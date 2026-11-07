<?php

namespace App\Services;

use App\Enums\StatusUsulan as S;
use App\Models\Tahun;
use App\Models\Unit;
use App\Models\User;
use App\Models\Usulan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Angka untuk dashboard dan laporan. Dashboard dan laporan memakai query yang sama
 * agar angka yang tampil di layar selalu cocok dengan yang dicetak (US-36).
 */
class StatistikUsulan
{
    public const DIPROSES = ['diajukan', 'di_wadir', 'di_spi', 'di_direktur'];

    /**
     * Usulan yang terlihat oleh peran pada satu tahun anggaran.
     * - Pengusul: semua usulan unitnya (termasuk draft).
     * - Perencanaan, Direktur: semua yang pernah diajukan.
     * - Wadir, SPI: yang pernah sampai ke tahapnya.
     */
    public function lingkup(User $user, ?int $tahunId): Builder
    {
        $q = Usulan::query()->when($tahunId, fn ($q) => $q->where('usulans.tahun_id', $tahunId));

        return match ($user->role) {
            User::ROLE_PENGUSUL => $q->where('usulans.unit_id', $user->unit_id),
            User::ROLE_WADIR => $this->pernahSampai($q, S::DiWadir),
            User::ROLE_SPI => $this->pernahSampai($q, S::DiSpi),
            default => $q->whereNotNull('usulans.submitted_at'),
        };
    }

    public function pernahSampai(Builder $q, S $status): Builder
    {
        return $q->where(fn ($w) => $w
            ->where('usulans.status', $status->value)
            ->orWhereHas('logs', fn ($l) => $l->where('status_ke', $status->value)));
    }

    /** Usulan yang pernah diputuskan oleh tahap tertentu dengan aksi tertentu. */
    public function diputuskan(Builder $q, string $tahap, array $aksi): Builder
    {
        return $q->whereHas('logs', fn ($l) => $l->where('tahap', $tahap)->whereIn('aksi', $aksi));
    }

    /** @return array{jumlah: int, nilai: float} */
    public function hitung(Builder $q): array
    {
        $r = (clone $q)->toBase()->selectRaw('count(*) as jumlah, coalesce(sum(usulans.total_rab), 0) as nilai')->first();

        return ['jumlah' => (int) $r->jumlah, 'nilai' => (float) $r->nilai];
    }

    /** status => [jumlah, nilai], urut sesuai alur. */
    public function perStatus(Builder $q): Collection
    {
        $rows = (clone $q)->toBase()->selectRaw('usulans.status, count(*) as jumlah, coalesce(sum(usulans.total_rab), 0) as nilai')
            ->groupBy('usulans.status')->get()->keyBy('status');

        return collect(S::cases())->mapWithKeys(fn (S $s) => [$s->value => [
            'status' => $s,
            'jumlah' => (int) ($rows[$s->value]->jumlah ?? 0),
            'nilai' => (float) ($rows[$s->value]->nilai ?? 0),
        ]]);
    }

    /** Tinggi/Sedang/Rendah/Belum => [jumlah, nilai]. */
    public function perPrioritas(Builder $q): Collection
    {
        $rows = (clone $q)->toBase()->selectRaw('usulans.prioritas, count(*) as jumlah, coalesce(sum(usulans.total_rab), 0) as nilai')
            ->groupBy('usulans.prioritas')->get()->keyBy(fn ($r) => $r->prioritas ?? 'Belum');

        return collect([...array_keys(Usulan::PRIORITAS), 'Belum'])->mapWithKeys(fn ($p) => [$p => [
            'jumlah' => (int) ($rows[$p]->jumlah ?? 0),
            'nilai' => (float) ($rows[$p]->nilai ?? 0),
        ]]);
    }

    /** Rekap per unit: jumlah, nilai, diproses, disahkan, ditolak. */
    public function perUnit(Builder $q): Collection
    {
        $proses = "'".implode("','", self::DIPROSES)."'";

        return (clone $q)->toBase()
            ->join('units', 'units.id', '=', 'usulans.unit_id')
            ->selectRaw("units.id, units.unit_id as kode, units.nama_unit,
                count(*) as jumlah,
                coalesce(sum(usulans.total_rab), 0) as nilai,
                sum(case when usulans.status in ({$proses}) then 1 else 0 end) as diproses,
                sum(case when usulans.status = 'disahkan' then 1 else 0 end) as disahkan,
                sum(case when usulans.status = 'disahkan' then usulans.total_rab else 0 end) as nilai_disahkan,
                sum(case when usulans.status = 'ditolak' then 1 else 0 end) as ditolak")
            ->groupBy('units.id', 'units.unit_id', 'units.nama_unit')
            ->orderByDesc('nilai')
            ->get();
    }

    /**
     * Grafik pagu tahunan (US-27): pagu, nilai diusulkan, dan nilai disahkan per tahun.
     * $unitId diisi untuk Pengusul agar batang usulan hanya milik unitnya.
     */
    public function trenTahunan(?int $unitId = null): Collection
    {
        $agregat = Usulan::query()->whereNotNull('submitted_at')
            ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
            ->toBase()
            ->selectRaw("tahun_id,
                coalesce(sum(case when status <> 'ditolak' then total_rab else 0 end), 0) as diusulkan,
                coalesce(sum(case when status = 'disahkan' then total_rab else 0 end), 0) as disahkan")
            ->groupBy('tahun_id')->get()->keyBy('tahun_id');

        return Tahun::orderBy('tahun')->get()->map(fn (Tahun $t) => [
            'tahun' => $t->tahun,
            'pagu' => $t->pagu !== null ? (float) $t->pagu : null,
            'diusulkan' => (float) ($agregat[$t->id]->diusulkan ?? 0),
            'disahkan' => (float) ($agregat[$t->id]->disahkan ?? 0),
        ])->filter(fn ($r) => $r['pagu'] || $r['diusulkan'])->values();
    }

    /** Untuk laporan IKU (US-35): setiap IKU tahun itu beserta usulan terkait. */
    public function perIku(Builder $lingkupUsulan, int $tahun): Collection
    {
        $ids = (clone $lingkupUsulan)->pluck('usulans.id');

        $relasi = DB::table('iku_usulan')->whereIn('usulan_id', $ids)->get()->groupBy('iku_id');
        $usulans = Usulan::with('unit')->whereIn('id', $ids)->get()->keyBy('id');

        return \App\Models\Iku::forTahun($tahun)->orderBy('kode_iku')->get()->map(function ($iku) use ($relasi, $usulans) {
            $terkait = collect($relasi[$iku->id] ?? [])->map(fn ($r) => $usulans[$r->usulan_id])->sortByDesc('total_rab')->values();

            return [
                'iku' => $iku,
                'usulans' => $terkait,
                'jumlah' => $terkait->count(),
                'nilai' => (float) $terkait->sum('total_rab'),
            ];
        });
    }

    public static function unitOptions(): array
    {
        return Unit::orderBy('nama_unit')->pluck('nama_unit', 'id')->all();
    }
}
