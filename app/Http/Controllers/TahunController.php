<?php

namespace App\Http\Controllers;

use App\Http\Requests\TahunRequest;
use App\Models\Tahun;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TahunController extends Controller
{
    public function index(): View
    {
        $tahuns = Tahun::orderByDesc('tahun')->get();

        return view('tahun.index', compact('tahuns'));
    }

    public function create(): View
    {
        return view('tahun.form', ['tahun' => new Tahun(['tahun' => (Tahun::max('tahun') ?? now()->year) + 1, 'status' => false])]);
    }

    public function store(TahunRequest $request): RedirectResponse
    {
        $tahun = (new Tahun)->saveExclusive($request->validated());

        return redirect()->route('tahun.index')->with('success', "Tahun anggaran {$tahun->tahun} berhasil ditambahkan.");
    }

    public function edit(Tahun $tahun): View
    {
        return view('tahun.form', compact('tahun'));
    }

    public function update(TahunRequest $request, Tahun $tahun): RedirectResponse
    {
        $tahun->saveExclusive($request->validated());

        return redirect()->route('tahun.index')->with('success', "Tahun anggaran {$tahun->tahun} berhasil diperbarui.");
    }

    public function activate(Tahun $tahun): RedirectResponse
    {
        $tahun->saveExclusive(['status' => true]);

        return back()->with('success', "Tahun anggaran {$tahun->tahun} sekarang aktif.");
    }

    public function destroy(Tahun $tahun): RedirectResponse
    {
        if ($tahun->status) {
            return back()->with('error', "Tahun {$tahun->tahun} sedang aktif. Aktifkan tahun lain dulu sebelum menghapus.");
        }

        if (\App\Models\Usulan::withTrashed()->where('tahun_id', $tahun->id)->exists()) {
            return back()->with('error', "Tahun {$tahun->tahun} sudah memiliki usulan dan tidak bisa dihapus.");
        }

        $tahun->delete();

        return redirect()->route('tahun.index')->with('success', "Tahun anggaran {$tahun->tahun} berhasil dihapus.");
    }
}
