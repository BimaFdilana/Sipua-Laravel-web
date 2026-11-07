<?php

namespace App\Http\Controllers;

use App\Http\Requests\AkunBelanjaRequest;
use App\Models\AkunBelanja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AkunBelanjaController extends Controller
{
    public function index(Request $request): View
    {
        $akun = AkunBelanja::query()
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('kode_akun', 'like', "%{$s}%")
                ->orWhere('nama_akun', 'like', "%{$s}%")))
            ->when($request->jenis, fn ($q, $j) => $q->where('jenis_belanja', $j))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'aktif'))
            ->orderBy('kode_akun')
            ->paginate(15)->withQueryString();

        return view('akun-belanja.index', compact('akun'));
    }

    public function create(): View
    {
        return view('akun-belanja.form', ['akun' => new AkunBelanja(['is_active' => true, 'jenis_belanja' => 'Barang'])]);
    }

    public function store(AkunBelanjaRequest $request): RedirectResponse
    {
        $akun = AkunBelanja::create($request->validated());

        return redirect()->route('akun-belanja.index')->with('success', "Akun {$akun->kode_akun} berhasil ditambahkan.");
    }

    public function edit(AkunBelanja $akunBelanja): View
    {
        return view('akun-belanja.form', ['akun' => $akunBelanja]);
    }

    public function update(AkunBelanjaRequest $request, AkunBelanja $akunBelanja): RedirectResponse
    {
        $akunBelanja->update($request->validated());

        return redirect()->route('akun-belanja.index')->with('success', "Akun {$akunBelanja->kode_akun} berhasil diperbarui.");
    }

    public function destroy(AkunBelanja $akunBelanja): RedirectResponse
    {
        if (\App\Models\RabItem::where('kode_akun', $akunBelanja->kode_akun)->exists()) {
            return back()->with('error', "Akun {$akunBelanja->kode_akun} sudah dipakai di RAB usulan. Nonaktifkan akun ini, jangan dihapus.");
        }

        $akunBelanja->delete();

        return redirect()->route('akun-belanja.index')->with('success', "Akun {$akunBelanja->kode_akun} berhasil dihapus.");
    }
}
