<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unit;
use App\Models\Tahun;

class DashboardController extends Controller
{

    public function index()
    {

        $tahunAktif = Tahun::where('status', 1)->first();

        $jumlahUnit = Unit::count();

        $jumlahUser = User::count();

        return view('dashboard', compact(
            'tahunAktif',
            'jumlahUnit',
            'jumlahUser'
        ));
    }
}
