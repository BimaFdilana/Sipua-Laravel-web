<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('unit')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $units = Unit::all();

        return view('users.create', compact('units'));
    }

    public function store(Request $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'unit_id' => $request->unit_id,
            'nama_lengkap' => $request->nama_lengkap,
            'nip' => $request->nip,
            'jabatan' => $request->jabatan,
            'no_hp' => $request->no_hp,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect('users')->with('success', 'User berhasil dibuat');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $units = Unit::all();

        return view('users.edit', compact('user', 'units'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'unit_id' => $request->unit_id,
            'nama_lengkap' => $request->nama_lengkap,
            'nip' => $request->nip,
            'jabatan' => $request->jabatan,
            'no_hp' => $request->no_hp,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect('users')->with('success', 'User berhasil diupdate');
    }

    public function destroy($id)
    {
        User::destroy($id);

        return redirect('users')->with('success', 'User berhasil dihapus');
    }
}
