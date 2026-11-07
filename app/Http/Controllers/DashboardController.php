<?php

namespace App\Http\Controllers;

use App\Models\Iku;
use App\Models\Tahun;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tahunAktif = Tahun::where('status', true)->first();

        // Dashboard per peran (Pengusul, Perencanaan, Wadir, SPI, Direktur) dibangun di Sprint 4.
        $stats = $user->isAdmin() ? [
            ['label' => 'Pengguna', 'value' => User::count(), 'icon' => 'users', 'route' => 'users.index'],
            ['label' => 'Unit kerja', 'value' => Unit::count(), 'icon' => 'building', 'route' => 'unit.index'],
            ['label' => 'IKU', 'value' => Iku::count(), 'icon' => 'target', 'route' => 'iku.index'],
        ] : [];

        return view('dashboard', compact('user', 'tahunAktif', 'stats'));
    }
}
