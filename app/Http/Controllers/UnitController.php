<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;

class UnitController extends Controller
{

    // Menampilkan semua data unit
    public function index()
    {
        $units = unit::orderBy('created_at', 'DESC')->get();

        return view('unit.index', compact('units'));
    }

    // Form tambah unit
    public function create()
    {
        return view('unit.create');
    }

    // Simpan data unit
    public function store(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|unique:units',
            'nama_unit' => 'required|string|max:255',
            'keterangan' => 'nullable|string'
        ]);

        Unit::create([
            'unit_id' => $request->unit_id,
            'nama_unit' => $request->nama_unit,
            'keterangan' => $request->keterangan
        ]);

        return redirect()->route('unit.index')
            ->with('success', 'Data unit berhasil ditambahkan');
    }

    // Form edit unit
    public function edit($id)
    {
        $unit = unit::findOrFail($id);

        return view('unit.edit', compact('unit'));
    }

    // Update data unit
    public function update(Request $request, $id)
    {
        $unit = unit::findOrFail($id);

        $request->validate([
            'unit_id' => 'required|unique:units,unit_id,' . $unit->id,
            'nama_unit' => 'required|string|max:255',
            'keterangan' => 'nullable|string'
        ]);

        $unit->update([
            'unit_id' => $request->unit_id,
            'nama_unit' => $request->nama_unit,
            'keterangan' => $request->keterangan
        ]);

        return redirect()->route('unit.index')
            ->with('success', 'Data unit berhasil diperbarui');
    }

    // Hapus unit
    public function destroy($id)
    {
        $unit = unit::findOrFail($id);

        $unit->delete();

        return redirect()->route('unit.index')
            ->with('success', 'Data unit berhasil dihapus');
    }
}
