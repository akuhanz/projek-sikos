<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\KosDetailController;
use App\Http\Controllers\KosListController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\Pemilik\DashboardController;
use App\Http\Controllers\Pemilik\KosController;
use App\Http\Controllers\Pemilik\PengajuanController as PemilikPengajuanController;
use Illuminate\Support\Facades\Route;

// Halaman publik
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/kos', [KosListController::class, 'index'])->name('kos.index');
Route::get('/kos/{kos}', [KosDetailController::class, 'show'])->name('kos.show');
Route::get('/kos/{kos}/ajukan', [KosDetailController::class, 'ajukan'])->name('kos.ajukan');

// Hanya bisa diakses kalau belum login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Hanya bisa diakses kalau sudah login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard per role
    Route::get('/admin/dashboard', fn () => 'Dashboard Admin')
        ->middleware('role:admin')->name('admin.dashboard');

    Route::get('/pencari/dashboard', fn () => redirect()->route('pengajuan.index'))
        ->middleware('role:pencari_kos')->name('pencari.dashboard');

    Route::get('/pemilik/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:pemilik_kos')->name('pemilik.dashboard');

    // Kelola kos dan pengajuan (pemilik)
    Route::prefix('pemilik')->name('pemilik.')->middleware('role:pemilik_kos')->group(function () {
        Route::get('/kos', [KosController::class, 'index'])->name('kos.index');
        Route::get('/kos/tambah', [KosController::class, 'create'])->name('kos.create');
        Route::post('/kos', [KosController::class, 'store'])->name('kos.store');
        Route::get('/kos/{kos}/edit', [KosController::class, 'edit'])->name('kos.edit');
        Route::put('/kos/{kos}', [KosController::class, 'update'])->name('kos.update');

        Route::patch('/kos/{kos}/kamar', [KosController::class, 'ubahKamar'])->name('kos.kamar');
        Route::patch('/telepon', [KosController::class, 'ubahTelepon'])->name('telepon.update');

        Route::get('/pengajuan', [PemilikPengajuanController::class, 'index'])->name('pengajuan.index');
        Route::patch('/pengajuan/{pengajuan}', [PemilikPengajuanController::class, 'respons'])->name('pengajuan.respons');
        Route::patch('/pengajuan/{pengajuan}/akhiri', [PemilikPengajuanController::class, 'akhiri'])->name('pengajuan.akhiri');
    });

    // Favorit dan pengajuan sewa (pencari)
    Route::middleware('role:pencari_kos')->group(function () {
        Route::get('/favorit', [FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/kos/{kos}/favorit', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
        Route::post('/kos/{kos}/pengajuan', [PengajuanController::class, 'store'])->name('pengajuan.store');
        Route::patch('/pengajuan/{pengajuan}/batal', [PengajuanController::class, 'batalkan'])->name('pengajuan.batal');
        Route::patch('/pengajuan/{pengajuan}/akhiri', [PengajuanController::class, 'akhiri'])->name('pengajuan.akhiri');
    });
});