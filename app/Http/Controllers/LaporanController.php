<?php

namespace App\Http\Controllers;

use App\Enums\StatusUsulan;
use App\Models\Tahun;
use App\Models\Unit;
use App\Models\Usulan;
use App\Services\LaporanExport;
use App\Services\StatistikUsulan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Laporan usulan (US-33), per prioritas (US-34), keterkaitan IKU (US-35),
 * serta cetak/unduh PDF dan Excel (US-36). Layar, PDF, dan Excel memakai data yang sama.
 */
class LaporanController extends Controller
{
    public const JENIS = [
        'usulan' => ['Rekapitulasi Usulan Anggaran', ['perencanaan', 'direktur']],
        'prioritas' => ['Usulan Berdasarkan Tingkat Prioritas', ['perencanaan', 'wadir', 'direktur']],
        'iku' => ['Keterkaitan Usulan dengan IKU', ['perencanaan', 'direktur']],
    ];

    public function __construct(private StatistikUsulan $stat) {}

    public function index(Request $request): RedirectResponse
    {
        $pertama = collect(self::JENIS)->search(fn ($j) => $request->user()->hasRole(...$j[1]));

        return redirect()->route('laporan.show', $pertama);
    }

    public function show(Request $request, string $jenis): View|Response
    {
        abort_unless(isset(self::JENIS[$jenis]), 404);
        abort_unless($request->user()->hasRole(...self::JENIS[$jenis][1]), 403);

        $data = $this->{$jenis}($request) + [
            'jenis' => $jenis,
            'judul' => self::JENIS[$jenis][0],
            'dicetak' => now(),
            'dicetakOleh' => $request->user()->name,
        ];

        return match ($request->query('format')) {
            'pdf' => Pdf::loadView('laporan.pdf', $data)
                ->setPaper('a4', $jenis === 'usulan' ? 'landscape' : 'portrait')
                ->download($this->namaFile($data, 'pdf')),
            'xlsx' => app(LaporanExport::class)->xlsx($data, $this->namaFile($data, 'xlsx')),
            'cetak' => response()->view('laporan.cetak', $data),
            default => view('laporan.show', $data + [
                'tersedia' => collect(self::JENIS)->filter(fn ($j) => $request->user()->hasRole(...$j[1]))->map(fn ($j) => $j[0]),
                'tahunOptions' => Tahun::orderByDesc('tahun')->pluck('tahun', 'id')->all(),
                'unitOptions' => Unit::orderBy('nama_unit')->pluck('nama_unit', 'id')->all(),
            ]),
        };
    }

    // ---------- Penyusun data ----------

    /** Filter umum + label filter untuk kop laporan. */
    private function filter(Request $request, array $izinkan): array
    {
        $tahun = $request->has('tahun') ? Tahun::find($request->integer('tahun')) : Tahun::aktif();
        $q = $this->stat->lingkup($request->user(), $tahun?->id)->with(['unit', 'ikus']);
        $label = ['Tahun anggaran' => $tahun?->tahun ?? 'Semua'];

        if (in_array('unit', $izinkan) && $request->filled('unit') && ($unit = Unit::find($request->integer('unit')))) {
            $q->where('usulans.unit_id', $unit->id);
            $label['Unit kerja'] = $unit->nama_unit;
        }
        if (in_array('status', $izinkan) && ($status = StatusUsulan::tryFrom((string) $request->status))) {
            $q->where('usulans.status', $status->value);
            $label['Status'] = $status->label();
        }
        if (in_array('prioritas', $izinkan) && array_key_exists((string) $request->prioritas, Usulan::PRIORITAS)) {
            $q->where('usulans.prioritas', $request->prioritas);
            $label['Prioritas'] = $request->prioritas;
        }

        return [$q, $tahun, $label];
    }

    /** US-33 */
    private function usulan(Request $request): array
    {
        [$q, $tahun, $label] = $this->filter($request, ['unit', 'status', 'prioritas']);
        $rows = $q->orderBy('unit_id')->orderBy('nomor')->get();

        return [
            'tahun' => $tahun,
            'filterLabel' => $label,
            'rows' => $rows,
            'total' => (float) $rows->sum('total_rab'),
            'filters' => ['tahun', 'unit', 'status', 'prioritas'],
        ];
    }

    /** US-34: dikelompokkan per prioritas (Tinggi → Rendah) dengan subtotal. */
    private function prioritas(Request $request): array
    {
        [$q, $tahun, $label] = $this->filter($request, ['unit']);
        $rows = $q->whereNotNull('usulans.prioritas')->get();

        $urutan = array_keys(Usulan::PRIORITAS);
        $kelompok = collect($urutan)->mapWithKeys(fn ($p) => [$p => $rows->where('prioritas', $p)->sortByDesc('total_rab')->values()]);

        return [
            'tahun' => $tahun,
            'filterLabel' => $label,
            'kelompok' => $kelompok,
            'rows' => $rows,
            'total' => (float) $rows->sum('total_rab'),
            'filters' => ['tahun', 'unit'],
        ];
    }

    /** US-35: tiap IKU tahun itu beserta usulan terkait; IKU tanpa usulan ikut tampil. */
    private function iku(Request $request): array
    {
        [$q, $tahun, $label] = $this->filter($request, ['unit']);
        if (! $tahun && ($tahun = Tahun::aktif())) {
            $label['Tahun anggaran'] = $tahun->tahun; // IKU selalu per tahun
            $q->where('usulans.tahun_id', $tahun->id);
        }
        $perIku = $tahun ? $this->stat->perIku($q, $tahun->tahun) : collect();

        return [
            'tahun' => $tahun,
            'filterLabel' => $label,
            'perIku' => $perIku,
            'tanpaUsulan' => $perIku->where('jumlah', 0)->count(),
            'total' => (float) $perIku->flatMap(fn ($r) => $r['usulans'])->unique('id')->sum('total_rab'),
            'filters' => ['tahun', 'unit'],
        ];
    }

    private function namaFile(array $data, string $ext): string
    {
        return Str::slug('laporan '.$data['jenis'].' '.($data['tahun']?->tahun ?? 'semua').' '.now()->format('Ymd-His')).'.'.$ext;
    }
}
