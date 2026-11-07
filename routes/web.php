<?php

use App\Http\Controllers\AkunBelanjaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IkuController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TahunController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    // Semua peran
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Administrator: master data & pengguna
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except('show');
        Route::resource('unit', UnitController::class)->except('show');
        Route::resource('tahun', TahunController::class)->except(['show', 'destroy']);
        Route::resource('akun-belanja', AkunBelanjaController::class)->except('show');
        Route::resource('iku', IkuController::class)->except('show');
    });
});

require __DIR__.'/auth.php';
