<?php

namespace App\Services;

use App\Enums\StatusUsulan as S;
use App\Models\Tahun;
use App\Models\User;
use App\Models\Usulan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Satu-satunya tempat yang boleh mengubah status usulan.
 * Setiap perubahan dicek terhadap tabel transisi dan dicatat di usulan_logs.
 */
class UsulanWorkflow
{
    /** status asal => [aksi => status tujuan] */
    public const TRANSISI = [
        'draft' => ['kirim' => S::Diajukan],
        'perlu_perbaikan' => ['kirim' => S::Diajukan],
        'diajukan' => ['teruskan' => S::DiWadir, 'kembalikan' => S::PerluPerbaikan, 'tolak' => S::Ditolak],
        'di_wadir' => ['teruskan' => S::DiSpi, 'kembalikan' => S::PerluPerbaikan, 'tolak' => S::Ditolak],
        'di_spi' => ['teruskan' => S::DiDirektur, 'kembalikan' => S::PerluPerbaikan, 'tolak' => S::Ditolak],
        'di_direktur' => ['sahkan' => S::Disahkan, 'tolak' => S::Ditolak],
    ];

    /** Membuat draft baru untuk unit kerja pengguna pada tahun anggaran aktif (US-08). */
    public function buat(User $user, array $data): Usulan
    {
        $tahun = Tahun::aktif();

        if (! $tahun) {
            throw ValidationException::withMessages(['judul_kegiatan' => 'Belum ada tahun anggaran aktif. Hubungi Administrator.']);
        }
        if (! $user->unit) {
            throw ValidationException::withMessages(['judul_kegiatan' => 'Akun Anda belum terhubung ke unit kerja. Hubungi Administrator.']);
        }

        return DB::transaction(function () use ($user, $tahun, $data) {
            $usulan = new Usulan($data);
            $usulan->nomor = Usulan::nomorBaru($user->unit, $tahun);
            $usulan->unit()->associate($user->unit);
            $usulan->tahun()->associate($tahun);
            $usulan->pembuat()->associate($user);
            $usulan->status = S::Draft;
            $usulan->save();

            $this->log($usulan, $user, 'pengusul', 'buat', null, S::Draft);

            return $usulan;
        });
    }

    /** Mengirim usulan untuk diverifikasi Perencanaan (US-13, US-18 kirim ulang). */
    public function kirim(Usulan $usulan, User $user): Usulan
    {
        return DB::transaction(function () use ($usulan, $user) {
            $usulan = Usulan::lockForUpdate()->findOrFail($usulan->id);

            if (! $usulan->isLengkap()) {
                throw ValidationException::withMessages(['kirim' => 'Usulan belum lengkap. Lengkapi semua syarat di daftar periksa.']);
            }

            $kirimUlang = $usulan->status === S::PerluPerbaikan;
            $this->pindah($usulan, $user, 'pengusul', 'kirim', $kirimUlang ? 'Dikirim ulang setelah perbaikan.' : null);
            $usulan->submitted_at = now();
            $usulan->save();

            return $usulan;
        });
    }

    /**
     * Keputusan verifikator (US-17, 18, 22, 24, 25).
     *
     * $statusDiharapkan = status saat halaman dibuka; jika berbeda, usulan sudah diproses orang lain.
     */
    public function verifikasi(Usulan $usulan, User $user, string $aksi, ?string $catatan, string $statusDiharapkan): Usulan
    {
        return DB::transaction(function () use ($usulan, $user, $aksi, $catatan, $statusDiharapkan) {
            $usulan = Usulan::lockForUpdate()->findOrFail($usulan->id);
            $tahap = $usulan->status->tahapVerifikasi();

            if ($usulan->status->value !== $statusDiharapkan || $tahap !== $user->role) {
                throw ValidationException::withMessages([
                    'keputusan' => 'Usulan ini sudah diproses atau bukan di tahap Anda. Muat ulang halaman.',
                ]);
            }

            if (in_array($aksi, ['kembalikan', 'tolak'], true) && blank($catatan)) {
                throw ValidationException::withMessages([
                    'catatan' => $aksi === 'kembalikan'
                        ? 'Tuliskan apa yang harus diperbaiki Pengusul.'
                        : 'Tuliskan alasan penolakan.',
                ]);
            }

            // US-20: prioritas ditetapkan Perencanaan sebelum usulan diteruskan ke Wadir.
            if ($tahap === User::ROLE_PERENCANAAN && $aksi === 'teruskan' && ! $usulan->prioritas) {
                throw ValidationException::withMessages(['prioritas' => 'Tetapkan prioritas usulan sebelum meneruskan ke Wadir.']);
            }

            return $this->pindah($usulan, $user, $tahap, $aksi, $catatan ?: null);
        });
    }

    /** US-20: Perencanaan menetapkan tingkat prioritas; tercatat siapa dan kapan. */
    public function tetapkanPrioritas(Usulan $usulan, User $user, string $prioritas, ?string $catatan = null): Usulan
    {
        return DB::transaction(function () use ($usulan, $user, $prioritas, $catatan) {
            $usulan = Usulan::lockForUpdate()->findOrFail($usulan->id);

            if ($usulan->status !== S::Diajukan) {
                throw ValidationException::withMessages(['prioritas' => 'Prioritas hanya bisa ditetapkan saat usulan di tahap Perencanaan.']);
            }

            $usulan->prioritas = $prioritas;
            $usulan->save();

            $this->log($usulan, $user, User::ROLE_PERENCANAAN, 'prioritas', $usulan->status, $usulan->status,
                trim("Prioritas {$prioritas}. ".($catatan ?? '')));

            return $usulan;
        });
    }

    /** Ubah status sesuai tabel transisi, lalu catat log. */
    public function pindah(Usulan $usulan, User $user, string $tahap, string $aksi, ?string $catatan = null): Usulan
    {
        $dari = $usulan->status;
        $ke = self::TRANSISI[$dari->value][$aksi] ?? null;

        if (! $ke) {
            throw ValidationException::withMessages([
                'status' => "Aksi \"{$aksi}\" tidak berlaku untuk usulan berstatus {$dari->label()}.",
            ]);
        }

        $usulan->status = $ke;
        $usulan->save();

        $this->log($usulan, $user, $tahap, $aksi, $dari, $ke, $catatan);

        return $usulan;
    }

    private function log(Usulan $usulan, User $user, string $tahap, string $aksi, ?S $dari, S $ke, ?string $catatan = null): void
    {
        $usulan->logs()->create([
            'user_id' => $user->id,
            'tahap' => $tahap,
            'aksi' => $aksi,
            'status_dari' => $dari?->value,
            'status_ke' => $ke->value,
            'catatan' => $catatan,
        ]);
    }
}
