<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\{
    UserController,
    unitController,
    TahunController,
    AkunBelanjaController,
    DashboardController,
    IkuController
};

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    if (Auth::user()->role == 'admin') {
        return view('admin_dashboard');
    } else {
        return view('user_dashboard');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

Route::view('/dashboard-admin', 'admin_dashboard')->middleware(['auth', 'admin'])->name('admin.dashboard');
Route::view('/dashboard-user', 'user_dashboard')->middleware(['auth'])->name('user.dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('users', UserController::class);
    Route::resource('unit', unitController::class);
    Route::resource('tahun', TahunController::class);
    Route::resource('akun-belanja', AkunBelanjaController::class);
    Route::view('/iku', 'coming-soon')->name('iku');
    Route::resource('tahuns', TahunController::class);
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth'])
        ->name('dashboard');
    Route::resource('iku', IkuController::class);
});

require __DIR__ . '/auth.php';
