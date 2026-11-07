<?php

namespace App\Http\Controllers;

use App\Enums\StatusUsulan as S;
use App\Models\AkunBelanja;
use App\Models\Iku;
use App\Models\Tahun;
use App\Models\Unit;
use App\Models\User;
use App\Models\Usulan;
use App\Services\StatistikUsulan;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard per peran (US-26 s.d. US-32).
 * Kartu KPI: [label, jumlah, nilai (Rp, opsional), tautan (opsional), warna aksen].
 */
class DashboardController extends Controller
{
    public function __construct(private StatistikUsulan $stat) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return $this->admin($user);
        }

        $tahunAktif = Tahun::aktif();
        $tahun = $request->filled('tahun') ? Tahun::find($request->integer('tahun')) : $tahunAktif;
        $lingkup = $this->stat->lingkup($user, $tahun?->id);

        $data = [
            'user' => $user,
            'tahun' => $tahun,
            'tahunAktif' => $tahunAktif,
            'tahunOptions' => Tahun::orderByDesc('tahun')->pluck('tahun', 'id')->all(),
            'kpi' => [],
            'perStatus' => $this->stat->perStatus($lingkup),
            'perPrioritas' => null,
            'perUnit' => null,
            'tren' => null,
            'menunggu' => collect(),
            'ikus' => collect(),
            'total' => $this->stat->hitung($lingkup),
        ];

        $antrean = fn (S $s) => (clone $lingkup)->where('usulans.status', $s->value);
        $kartu = fn (string $label, $q, ?string $link = null, string $warna = 'slate') => ['label' => $label, 'link' => $link, 'warna' => $warna] + $this->stat->hitung($q);

        switch ($user->role) {
            case User::ROLE_PENGUSUL: // US-27
                $data['kpi'] = [
                    $kartu('Total usulan', $lingkup, route('usulan.index'), 'blue'),
                    $kartu('Draft', $antrean(S::Draft), route('usulan.index', ['status' => 'draft'])),
                    $kartu('Diproses', (clone $lingkup)->whereIn('usulans.status', StatistikUsulan::DIPROSES), null, 'blue'),
                    $kartu('Perlu perbaikan', $antrean(S::PerluPerbaikan), route('usulan.index', ['status' => 'perlu_perbaikan']), 'amber'),
                    $kartu('Disahkan', $antrean(S::Disahkan), route('usulan.index', ['status' => 'disahkan']), 'green'),
                    $kartu('Ditolak', $antrean(S::Ditolak), route('usulan.index', ['status' => 'ditolak']), 'red'),
                ];
                $data['tren'] = $this->stat->trenTahunan($user->unit_id);
                $data['ikus'] = $tahun ? Iku::forTahun($tahun->tahun)->orderBy('kode_iku')->get() : collect();
                $data['menunggu'] = (clone $lingkup)->whereIn('usulans.status', ['draft', 'perlu_perbaikan'])->latest('updated_at')->limit(5)->get();
                break;

            case User::ROLE_PERENCANAAN: // US-29
                $data['kpi'] = [
                    $kartu('Usulan masuk', $lingkup, null, 'blue'),
                    $kartu('Belum diverifikasi', $antrean(S::Diajukan), route('verifikasi.index'), 'amber'),
                    $kartu('Dalam proses Wadir–Direktur', (clone $lingkup)->whereIn('usulans.status', ['di_wadir', 'di_spi', 'di_direktur'])),
                    $kartu('Dikembalikan', $antrean(S::PerluPerbaikan)),
                    $kartu('Diteruskan', $this->stat->diputuskan(clone $lingkup, 'perencanaan', ['teruskan']), route('verifikasi.index', ['tab' => 'riwayat']), 'green'),
                ];
                $data['perPrioritas'] = $this->stat->perPrioritas($lingkup);
                $data['perUnit'] = $this->stat->perUnit($lingkup);
                $data['tren'] = $this->stat->trenTahunan();
                $data['menunggu'] = $antrean(S::Diajukan)->with('unit')->oldest('submitted_at')->limit(5)->get();
                break;

            case User::ROLE_WADIR: // US-30
                $data['kpi'] = [
                    $kartu('Masuk ke Wadir', $lingkup, null, 'blue'),
                    $kartu('Belum diverifikasi', $antrean(S::DiWadir), route('verifikasi.index'), 'amber'),
                    $kartu('Disetujui', $this->stat->diputuskan(clone $lingkup, 'wadir', ['teruskan']), null, 'green'),
                    $kartu('Dikembalikan', $this->stat->diputuskan(clone $lingkup, 'wadir', ['kembalikan'])),
                    $kartu('Ditolak', $this->stat->diputuskan(clone $lingkup, 'wadir', ['tolak']), null, 'red'),
                ];
                $data['perPrioritas'] = $this->stat->perPrioritas($lingkup);
                $data['perUnit'] = $this->stat->perUnit($lingkup);
                $data['menunggu'] = $antrean(S::DiWadir)->with('unit')->oldest('submitted_at')->limit(5)->get();
                break;

            case User::ROLE_SPI: // US-31
                $data['kpi'] = [
                    $kartu('Masuk ke SPI', $lingkup, null, 'blue'),
                    $kartu('Belum diverifikasi', $antrean(S::DiSpi), route('verifikasi.index'), 'amber'),
                    $kartu('Sudah diverifikasi', $this->stat->diputuskan(clone $lingkup, 'spi', ['teruskan', 'kembalikan', 'tolak']), route('verifikasi.index', ['tab' => 'riwayat']), 'green'),
                ];
                $data['perPrioritas'] = $this->stat->perPrioritas($lingkup);
                $data['menunggu'] = $antrean(S::DiSpi)->with('unit')->oldest('submitted_at')->limit(5)->get();
                break;

            case User::ROLE_DIREKTUR: // US-32
                $data['kpi'] = [
                    $kartu('Seluruh usulan', $lingkup, null, 'blue'),
                    $kartu('Telah melalui verifikasi', $this->stat->pernahSampai(clone $lingkup, S::DiDirektur)),
                    $kartu('Menunggu pengesahan', $antrean(S::DiDirektur), route('verifikasi.index'), 'amber'),
                    $kartu('Disahkan', $antrean(S::Disahkan), null, 'green'),
                    $kartu('Ditolak', $antrean(S::Ditolak), null, 'red'),
                ];
                $data['perPrioritas'] = $this->stat->perPrioritas($lingkup);
                $data['perUnit'] = $this->stat->perUnit($lingkup);
                $data['tren'] = $this->stat->trenTahunan();
                $data['menunggu'] = $antrean(S::DiDirektur)->with('unit')->oldest('submitted_at')->limit(5)->get();
                break;
        }

        return view('dashboard', $data);
    }

    /** US-26: ringkasan jumlah data master. */
    private function admin(User $user): View
    {
        return view('dashboard-admin', [
            'user' => $user,
            'tahunAktif' => Tahun::aktif(),
            'stats' => [
                ['label' => 'Pengguna', 'value' => User::count(), 'sub' => User::where('is_active', false)->count().' nonaktif', 'icon' => 'users', 'route' => 'users.index'],
                ['label' => 'Unit kerja', 'value' => Unit::count(), 'sub' => Unit::where('is_active', true)->count().' aktif', 'icon' => 'building', 'route' => 'unit.index'],
                ['label' => 'IKU', 'value' => Iku::count(), 'sub' => Iku::where('sifat', 'Wajib')->count().' wajib', 'icon' => 'target', 'route' => 'iku.index'],
                ['label' => 'Akun belanja', 'value' => AkunBelanja::count(), 'sub' => AkunBelanja::where('is_active', true)->count().' aktif', 'icon' => 'wallet', 'route' => 'akun-belanja.index'],
            ],
            'perPeran' => User::selectRaw('role, count(*) as jumlah')->groupBy('role')->pluck('jumlah', 'role'),
            'jumlahUsulan' => Usulan::count(),
        ]);
    }
}
