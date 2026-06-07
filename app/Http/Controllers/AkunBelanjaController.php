<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AkunBelanja;

class AkunBelanjaController extends Controller
{

    // Menampilkan semua data akun belanja
    public function index()
    {
        $akun = AkunBelanja::orderBy('kode_akun', 'ASC')->get();
        return view('akun-belanja.index', compact('akun'));
    }

    // Menampilkan form tambah akun
    public function create()
    {
        return view('akun-belanja.create');
    }

    // Menyimpan data akun baru
    public function store(Request $request)
    {

        $request->validate([
            'kode_akun' => 'required|unique:akun_belanjas',
            'nama_akun' => 'required',
            'jenis_belanja' => 'nullable',
            'keterangan' => 'required'

        ]);

        AkunBelanja::create([
            'kode_akun' => $request->kode_akun,
            'nama_akun' => $request->nama_akun,
            'jenis_belanja' => $request->jenis_belanja,
            'keterangan' => $request->keterangan
        ]);

        return redirect()->route('akun-belanja.index')
            ->with('success', 'Data akun belanja berhasil ditambahkan');
    }

    // Menampilkan form edit
    public function edit($kode_akun)
    {

        $akun = AkunBelanja::findOrFail($kode_akun);

        return view('akun-belanja.edit', compact('akun'));
    }

    // Update data akun
    public function update(Request $request, $kode_akun)
    {

        $akun = AkunBelanja::findOrFail($kode_akun);

        $request->validate([
            'nama_akun' => 'required',
            'jenis_belanja' => 'nullable',
            'keterangan' => 'required'
        ]);

        $akun->update([
            'nama_akun' => $request->nama_akun,
            'jenis_belanja' => $request->jenis_belanja,
            'keterangan' => $request->keterangan
        ]);

        return redirect()->route('akun-belanja.index')
            ->with('success', 'Data akun belanja berhasil diperbarui');
    }

    // Menghapus data akun
    public function destroy($kode_akun)
    {

        $akun = AkunBelanja::findOrFail($kode_akun);

        $akun->delete();

        return redirect()->route('akun-belanja.index')
            ->with('success', 'Data akun belanja berhasil dihapus');
    }
}
