<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $units = Unit::query()
            ->withCount('users')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('unit_id', 'like', "%{$s}%")
                ->orWhere('nama_unit', 'like', "%{$s}%")))
            ->when($request->jenis, fn ($q, $j) => $q->where('jenis', $j))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'aktif'))
            ->orderBy('jenis')->orderBy('nama_unit')
            ->paginate(15)->withQueryString();

        return view('unit.index', compact('units'));
    }

    public function create(): View
    {
        return view('unit.form', ['unit' => new Unit(['is_active' => true, 'jenis' => 'Jurusan'])]);
    }

    public function store(UnitRequest $request): RedirectResponse
    {
        $unit = Unit::create($request->validated());

        return redirect()->route('unit.index')->with('success', "Unit {$unit->nama_unit} berhasil ditambahkan.");
    }

    public function edit(Unit $unit): View
    {
        return view('unit.form', compact('unit'));
    }

    public function update(UnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()->route('unit.index')->with('success', "Unit {$unit->nama_unit} berhasil diperbarui.");
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        if ($jumlah = $unit->users()->count()) {
            return back()->with('error', "Unit {$unit->nama_unit} masih dipakai {$jumlah} pengguna. Nonaktifkan unit ini, jangan dihapus.");
        }

        $unit->delete();

        return redirect()->route('unit.index')->with('success', "Unit {$unit->nama_unit} berhasil dihapus.");
    }
}
