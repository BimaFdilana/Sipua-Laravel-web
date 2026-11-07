<?php

namespace App\Http\Controllers;

use App\Models\Tahun;
use Illuminate\Http\Request;

class TahunController extends Controller
{
    public function index()
    {
        $tahuns = Tahun::all();

        return view('tahun.index', compact('tahuns'));
    }

    public function create()
    {
        return view('tahun.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun' => 'required|unique:tahuns',
        ]);
        // jika status yang dipilih aktif
        if ($request->status == 1) {
            Tahun::where('status', 1)->update(['status' => 0]);
        }

        Tahun::create([
            'tahun' => $request->tahun,
            'status' => $request->status ?? 0,
        ]);

        return redirect()->route('tahun.index')
            ->with('success', 'Tahun berhasil ditambahkan');
    }

    public function edit($id)
    {
        $tahun = Tahun::findOrFail($id);

        return view('tahun.edit', compact('tahun'));
    }

    public function update(Request $request, $id)
    {
        $tahun = Tahun::findOrFail($id);

        if ($request->status == 1) {
            Tahun::where('status', 1)->update(['status' => 0]);
        }

        $tahun->update([
            'tahun' => $request->tahun,
            'status' => $request->status,
        ]);

        return redirect()->route('tahun.index')
            ->with('success', 'Tahun berhasil diperbarui');
    }
}
