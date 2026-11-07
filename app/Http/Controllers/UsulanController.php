<?php

namespace App\Http\Controllers;

use App\Enums\StatusUsulan;
use App\Http\Requests\UsulanInfoRequest;
use App\Http\Requests\UsulanKakRequest;
use App\Models\AkunBelanja;
use App\Models\Iku;
use App\Models\Tahun;
use App\Models\Usulan;
use App\Models\UsulanDokumen;
use App\Services\UsulanWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Pengajuan usulan oleh Pengusul (US-07 s.d. US-14).
 */
class UsulanController extends Controller
{
    public const STEPS = [
        'info' => 'Informasi kegiatan',
        'kak' => 'KAK/TOR',
        'rab' => 'Rencana anggaran',
        'iku' => 'Keterkaitan IKU',
        'dokumen' => 'Data dukung & kirim',
    ];

    public function __construct(private UsulanWorkflow $workflow) {}

    /** US-07: daftar usulan milik unit kerja, default tahun anggaran aktif. */
    public function index(Request $request): View
    {
        $user = $request->user();
        $tahunAktif = Tahun::aktif();
        $tahunId = $request->has('tahun') ? $request->integer('tahun') ?: null : $tahunAktif?->id;

        $base = Usulan::milikUnit($user->unit_id)->when($tahunId, fn ($q) => $q->where('tahun_id', $tahunId));

        $usulans = (clone $base)
            ->with('tahun')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('judul_kegiatan', 'like', "%{$s}%")
                ->orWhere('nomor', 'like', "%{$s}%")))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest('updated_at')
            ->paginate(15)->withQueryString();

        $ringkasan = (clone $base)->selectRaw('status, count(*) as jumlah, sum(total_rab) as nilai')
            ->groupBy('status')->get()->keyBy(fn ($r) => $r->status instanceof StatusUsulan ? $r->status->value : $r->status);

        return view('usulan.index', [
            'usulans' => $usulans,
            'ringkasan' => $ringkasan,
            'tahunId' => $tahunId,
            'tahunOptions' => Tahun::orderByDesc('tahun')->pluck('tahun', 'id')->all(),
            'unit' => $user->unit,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        Gate::authorize('create', Usulan::class);

        if (! Tahun::aktif()) {
            return redirect()->route('usulan.index')->with('error', 'Belum ada tahun anggaran aktif. Hubungi Administrator.');
        }

        return $this->wizard(new Usulan, 'info');
    }

    public function store(UsulanInfoRequest $request): RedirectResponse
    {
        Gate::authorize('create', Usulan::class);

        $usulan = $this->workflow->buat($request->user(), $request->validated());

        return redirect()->route('usulan.edit', [$usulan, 'kak'])
            ->with('success', "Draft {$usulan->nomor} tersimpan. Lanjutkan mengisi KAK/TOR.");
    }

    public function show(Usulan $usulan): View
    {
        Gate::authorize('view', $usulan);

        $usulan->load(['unit', 'tahun', 'pembuat', 'rabItems.akun', 'ikus', 'dokumens', 'logs.user']);

        return view('usulan.show', compact('usulan'));
    }

    public function edit(Usulan $usulan, string $step = 'info'): View
    {
        Gate::authorize('update', $usulan);
        abort_unless(array_key_exists($step, self::STEPS), 404);

        return $this->wizard($usulan, $step);
    }

    public function updateInfo(UsulanInfoRequest $request, Usulan $usulan): RedirectResponse
    {
        Gate::authorize('update', $usulan);
        $usulan->update($request->validated());

        return $this->next($request, $usulan, 'info');
    }

    public function updateKak(UsulanKakRequest $request, Usulan $usulan): RedirectResponse
    {
        Gate::authorize('update', $usulan);
        $usulan->update($request->validated());

        return $this->next($request, $usulan, 'kak');
    }

    /** US-13: kirim ke Perencanaan. */
    public function kirim(Request $request, Usulan $usulan): RedirectResponse
    {
        Gate::authorize('submit', $usulan);

        try {
            $usulan = $this->workflow->kirim($usulan, $request->user());
        } catch (ValidationException $e) {
            return redirect()->route('usulan.edit', [$usulan, 'dokumen'])->withErrors($e->errors());
        }

        return redirect()->route('usulan.show', $usulan)
            ->with('success', "Usulan {$usulan->nomor} berhasil diajukan dan menunggu verifikasi Perencanaan.");
    }

    public function destroy(Usulan $usulan): RedirectResponse
    {
        Gate::authorize('delete', $usulan);
        $usulan->delete();

        return redirect()->route('usulan.index')->with('success', "Draft {$usulan->nomor} dihapus.");
    }

    // ---------- Helper ----------

    private function wizard(Usulan $usulan, string $step): View
    {
        $data = ['usulan' => $usulan, 'step' => $step, 'steps' => self::STEPS];

        if ($usulan->exists) {
            $data['kelengkapan'] = collect($usulan->kelengkapan())->keyBy('step');
        }

        return view("usulan.steps.{$step}", $data + match ($step) {
            'rab' => [
                'items' => $usulan->rabItems()->get(),
                'akunOptions' => AkunBelanja::active()->orderBy('kode_akun')->get(['kode_akun', 'nama_akun'])
                    ->mapWithKeys(fn ($a) => [$a->kode_akun => "{$a->kode_akun} — {$a->nama_akun}"])->all(),
            ],
            'iku' => [
                'ikus' => Iku::forTahun($usulan->tahun->tahun)->orderBy('sifat')->orderBy('kode_iku')->get(),
                'terpilih' => $usulan->ikus()->pluck('ikus.id')->all(),
            ],
            'dokumen' => [
                'dokumens' => $usulan->dokumens()->get(),
                'kategori' => UsulanDokumen::KATEGORI,
            ],
            default => [],
        });
    }

    /** Tombol "Simpan & lanjut" pindah ke langkah berikutnya; "Simpan draft" tetap di langkah ini. */
    public static function next(Request $request, Usulan $usulan, string $step): RedirectResponse
    {
        $keys = array_keys(self::STEPS);
        $nextStep = $keys[array_search($step, $keys, true) + 1] ?? $step;
        $target = $request->input('aksi') === 'lanjut' ? $nextStep : $step;

        return redirect()->route('usulan.edit', [$usulan, $target])->with('success', 'Perubahan tersimpan.');
    }
}
