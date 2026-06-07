<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index()
    {
        $users = User::with('units')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $units = unit::all();
        return view('users.create', compact('units'));
    }

    public function store(Request $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'unit_id' => $request->unit_id
        ]);

        return redirect('users')->with('success', 'User berhasil dibuat');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $units = unit::all();

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
            'Status' => $request->Status
        ]);

        return redirect('users')->with('success', 'User berhasil diupdate');
    }


    public function destroy($id)
    {
        User::destroy($id);

        return redirect('users')->with('success', 'User berhasil dihapus');
    }
}
