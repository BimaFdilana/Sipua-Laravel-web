<?php

namespace App\Http\Controllers;

use App\Enums\StatusUsulan;
use App\Models\Tahun;
use App\Models\Unit;
use App\Models\Usulan;
use App\Services\UsulanWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Verifikasi berjenjang Perencanaan → Wadir → SPI → Direktur (US-15 s.d. US-25).
 */
class VerifikasiController extends Controller
{
    public function __construct(private UsulanWorkflow $workflow) {}

    /** Antrean usulan di tahap peran ini, dan riwayat usulan yang pernah diprosesnya. */
    public function index(Request $request): View
    {
        $role = $request->user()->role;
        $statusAntrean = StatusUsulan::antreanUntuk($role);
        $tab = $request->query('tab') === 'riwayat' ? 'riwayat' : 'antrean';
        $tahunId = $request->has('tahun') ? $request->integer('tahun') ?: null : Tahun::aktif()?->id;

        $base = Usulan::query()
            ->when($tahunId, fn ($q) => $q->where('tahun_id', $tahunId))
            ->when($request->unit, fn ($q, $u) => $q->where('unit_id', $u))
            ->when($request->prioritas, fn ($q, $p) => $q->where('prioritas', $p));

        $antreanCount = (clone $base)->where('status', $statusAntrean)->count();

        $usulans = (clone $base)
            ->with(['unit', 'tahun'])
            ->withCount(['ikus as iku_wajib_count' => fn ($q) => $q->where('sifat', 'Wajib')])
            ->when($tab === 'antrean',
                fn ($q) => $q->where('status', $statusAntrean)->oldest('submitted_at'),
                fn ($q) => $q->whereHas('logs', fn ($l) => $l->where('tahap', $role)->whereIn('aksi', ['teruskan', 'kembalikan', 'tolak', 'sahkan']))
                    ->latest('updated_at'))
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('judul_kegiatan', 'like', "%{$s}%")
                ->orWhere('nomor', 'like', "%{$s}%")))
            ->paginate(15)->withQueryString();

        return view('verifikasi.index', [
            'usulans' => $usulans,
            'tab' => $tab,
            'antreanCount' => $antreanCount,
            'statusAntrean' => $statusAntrean,
            'tahunId' => $tahunId,
            'tahunOptions' => Tahun::orderByDesc('tahun')->pluck('tahun', 'id')->all(),
            'unitOptions' => Unit::options(false),
        ]);
    }

    /** Keputusan verifikasi: teruskan / kembalikan / tolak (Direktur: sahkan / tolak). */
    public function proses(Request $request, Usulan $usulan): RedirectResponse
    {
        Gate::authorize('verify', $usulan);

        $aksiValid = array_keys(UsulanWorkflow::TRANSISI[$usulan->status->value] ?? []);

        $data = $request->validate([
            'keputusan' => ['required', Rule::in($aksiValid)],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'string'],
        ], ['keputusan.required' => 'Pilih keputusan verifikasi.'], ['keputusan' => 'keputusan', 'catatan' => 'catatan']);

        $usulan = $this->workflow->verifikasi($usulan, $request->user(), $data['keputusan'], $data['catatan'] ?? null, $data['status']);

        $pesan = match ($data['keputusan']) {
            'teruskan' => "Usulan {$usulan->nomor} diteruskan ke tahap {$usulan->status->label()}.",
            'kembalikan' => "Usulan {$usulan->nomor} dikembalikan ke Pengusul untuk perbaikan.",
            'tolak' => "Usulan {$usulan->nomor} ditolak.",
            'sahkan' => "Usulan {$usulan->nomor} disahkan.",
        };

        return redirect()->route('verifikasi.index')->with('success', $pesan);
    }

    /** US-20: tetapkan prioritas (Perencanaan, sebelum diteruskan). */
    public function prioritas(Request $request, Usulan $usulan): RedirectResponse
    {
        Gate::authorize('setPrioritas', $usulan);

        $data = $request->validate([
            'prioritas' => ['required', Rule::in(array_keys(Usulan::PRIORITAS))],
            'alasan_prioritas' => ['nullable', 'string', 'max:1000'],
        ], ['prioritas.required' => 'Pilih tingkat prioritas.']);

        $this->workflow->tetapkanPrioritas($usulan, $request->user(), $data['prioritas'], $data['alasan_prioritas'] ?? null);

        return back()->with('success', "Prioritas {$data['prioritas']} tersimpan.");
    }
}
