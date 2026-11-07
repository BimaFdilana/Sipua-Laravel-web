<?php

use App\Http\Controllers\AkunBelanjaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumenCetakController;
use App\Http\Controllers\IkuController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TahunController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsulanController;
use App\Http\Controllers\UsulanDokumenController;
use App\Http\Controllers\UsulanIkuController;
use App\Http\Controllers\UsulanRabController;
use App\Http\Controllers\VerifikasiController;
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
        Route::resource('tahun', TahunController::class)->except('show');
        Route::patch('tahun/{tahun}/aktifkan', [TahunController::class, 'activate'])->name('tahun.activate');
        Route::resource('akun-belanja', AkunBelanjaController::class)->except('show');
        Route::resource('iku', IkuController::class)->except('show');
    });

    // Pengusul: menyusun dan mengirim usulan (Sprint 2)
    Route::middleware('role:pengusul')->prefix('usulan')->name('usulan.')->group(function () {
        Route::get('/', [UsulanController::class, 'index'])->name('index');
        Route::get('/buat', [UsulanController::class, 'create'])->name('create');
        Route::get('/rkt', [DokumenCetakController::class, 'rkt'])->name('rkt');
        Route::post('/', [UsulanController::class, 'store'])->name('store');
        Route::get('/{usulan}/ubah/{step?}', [UsulanController::class, 'edit'])->name('edit');
        Route::put('/{usulan}/info', [UsulanController::class, 'updateInfo'])->name('info');
        Route::put('/{usulan}/kak', [UsulanController::class, 'updateKak'])->name('kak');
        Route::put('/{usulan}/rab', [UsulanRabController::class, 'update'])->name('rab');
        Route::put('/{usulan}/iku', [UsulanIkuController::class, 'update'])->name('iku');
        Route::post('/{usulan}/dokumen', [UsulanDokumenController::class, 'store'])->name('dokumen.store');
        Route::delete('/{usulan}/dokumen/{dokumen}', [UsulanDokumenController::class, 'destroy'])->name('dokumen.destroy')->scopeBindings();
        Route::post('/{usulan}/kirim', [UsulanController::class, 'kirim'])->name('kirim');
        Route::delete('/{usulan}', [UsulanController::class, 'destroy'])->name('destroy');
    });

    // Verifikator: antrean dan keputusan verifikasi (Sprint 3)
    Route::middleware('role:perencanaan,wadir,spi,direktur')->group(function () {
        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::post('/usulan/{usulan}/verifikasi', [VerifikasiController::class, 'proses'])->name('verifikasi.proses');
        Route::post('/usulan/{usulan}/prioritas', [VerifikasiController::class, 'prioritas'])->name('verifikasi.prioritas');
    });

    // Laporan (Sprint 4). Hak akses per jenis laporan dicek di LaporanController.
    Route::middleware('role:perencanaan,wadir,direktur')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/{jenis}', [LaporanController::class, 'show'])->name('laporan.show');
    });

    // Detail usulan & unduh dokumen: Pengusul unitnya dan verifikator (dicek UsulanPolicy)
    Route::get('/usulan/{usulan}', [UsulanController::class, 'show'])->name('usulan.show');
    Route::get('/usulan/{usulan}/rkakl', [DokumenCetakController::class, 'rkakl'])->name('usulan.rkakl');
    Route::get('/usulan/{usulan}/dokumen/{dokumen}', [UsulanDokumenController::class, 'download'])
        ->name('usulan.dokumen.download')->scopeBindings();
});

require __DIR__.'/auth.php';
