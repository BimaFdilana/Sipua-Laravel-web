<?php

namespace App\Http\Controllers;

use App\Http\Requests\IkuRequest;
use App\Models\Iku;
use App\Models\Tahun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IkuController extends Controller
{
    public function index(Request $request): View
    {
        // Default: tampilkan IKU tahun anggaran aktif.
        $tahun = $request->has('tahun') ? $request->tahun : Tahun::aktif()?->tahun;

        $ikus = Iku::query()
            ->when($tahun, fn ($q, $t) => $q->where('tahun', $t))
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('kode_iku', 'like', "%{$s}%")
                ->orWhere('nama_iku', 'like', "%{$s}%")))
            ->when($request->sifat, fn ($q, $s) => $q->where('sifat', $s))
            ->orderBy('tahun', 'desc')->orderBy('kode_iku')
            ->paginate(15)->withQueryString();

        return view('iku.index', ['ikus' => $ikus, 'tahun' => $tahun, 'tahunOptions' => Tahun::options()]);
    }

    public function create(): View
    {
        return view('iku.form', [
            'iku' => new Iku(['sifat' => 'Wajib', 'tahun' => Tahun::aktif()?->tahun]),
            'tahunOptions' => Tahun::options(),
        ]);
    }

    public function store(IkuRequest $request): RedirectResponse
    {
        $iku = Iku::create($request->validated());

        return redirect()->route('iku.index', ['tahun' => $iku->tahun])->with('success', "{$iku->kode_iku} berhasil ditambahkan.");
    }

    public function edit(Iku $iku): View
    {
        return view('iku.form', ['iku' => $iku, 'tahunOptions' => Tahun::options()]);
    }

    public function update(IkuRequest $request, Iku $iku): RedirectResponse
    {
        $iku->update($request->validated());

        return redirect()->route('iku.index', ['tahun' => $iku->tahun])->with('success', "{$iku->kode_iku} berhasil diperbarui.");
    }

    public function destroy(Iku $iku): RedirectResponse
    {
        // Sprint 2: tolak hapus jika IKU sudah dipilih di usulan.
        $iku->delete();

        return back()->with('success', "{$iku->kode_iku} berhasil dihapus.");
    }
}
