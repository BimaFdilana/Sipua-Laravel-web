<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('unit')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('nip', 'like', "%{$s}%")))
            ->when($request->role, fn ($q, $r) => $q->where('role', $r))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'aktif'))
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.form', [
            'user' => new User(['role' => User::ROLE_PENGUSUL, 'is_active' => true]),
            'units' => Unit::options(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create($request->payload() + ['email_verified_at' => now()]);

        return redirect()->route('users.index')->with('success', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        // Unit yang sedang dipakai tetap tampil walau sudah nonaktif.
        $units = Unit::options();
        if ($user->unit && ! isset($units[$user->unit_id])) {
            $units[$user->unit_id] = "{$user->unit->unit_id} — {$user->unit->nama_unit} (nonaktif)";
        }

        return view('users.form', compact('user', 'units'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->payload());

        return redirect()->route('users.index')->with('success', "Pengguna {$user->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        if (\App\Models\Usulan::withTrashed()->where('user_id', $user->id)->exists()
            || \App\Models\UsulanLog::where('user_id', $user->id)->exists()) {
            return back()->with('error', "{$user->name} sudah tercatat di usulan. Nonaktifkan akunnya, jangan dihapus.");
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', "Pengguna {$user->name} berhasil dihapus.");
    }
}
