<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Iku;

class IkuController extends Controller
{

    // menampilkan data IKU
    public function index()
    {
        $ikus = Iku::orderBy('kode_iku', 'asc')->get();
        return view('iku.index', compact('ikus'));
    }


    // form tambah IKU
    public function create()
    {
        return view('iku.create');
    }


    // simpan IKU
    public function store(Request $request)
    {
        $request->validate([
            'kode_iku' => 'required|unique:ikus',
            'nama_iku' => 'required'
        ]);

        Iku::create([
            'kode_iku' => $request->kode_iku,
            'nama_iku' => $request->nama_iku,
            'deskripsi' => $request->deskripsi,
            'kriteria' => $request->kriteria,
            'sifat' => $request->sifat,
            'tahun' => $request->tahun,
            'target' => $request->target
        ]);

        return redirect()->route('iku.index')
            ->with('success', 'Data IKU berhasil ditambahkan');
    }


    // form edit IKU
    public function edit($id)
    {
        $iku = Iku::findOrFail($id);
        return view('iku.edit', compact('iku'));
    }


    // update IKU
    public function update(Request $request, $id)
    {
        $iku = Iku::findOrFail($id);

        $request->validate([
            'kode_iku' => 'required|unique:ikus,kode_iku,' . $iku->id,
            'nama_iku' => 'required',
            'sifat' => 'required',
            'tahun' => 'required',
        ]);

        $iku->update([
            'kode_iku' => $request->kode_iku,
            'nama_iku' => $request->nama_iku,
            'deskripsi' => $request->deskripsi,
            'kriteria' => $request->kriteria
        ]);

        return redirect()->route('iku.index')
            ->with('success', 'Data IKU berhasil diperbarui');
    }


    // hapus IKU
    public function destroy($id)
    {
        $iku = Iku::findOrFail($id);
        $iku->delete();

        return redirect()->route('iku.index')
            ->with('success', 'Data IKU berhasil dihapus');
    }
}
