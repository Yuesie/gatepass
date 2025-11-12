<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\IzinMasukController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\RoleAccessCheck;
use Illuminate\Support\Facades\Route;

// ===================================================================
// RUTE HALAMAN UTAMA (TANPA LOGIN)
// ===================================================================
Route::get('/', function () {
    return view('welcome');
});

// ===================================================================
// RUTE UMUM (WAJIB LOGIN DAN VERIFIED EMAIL)
// ===================================================================
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Rute Aplikasi Utama (Dashboard, Izin, Persetujuan, Riwayat, Profil)
    Route::get('/dashboard', [IzinMasukController::class, 'dashboard'])->name('dashboard');

    // ... (Rute Izin lainnya) ...
    Route::get('/izin/buat', [IzinMasukController::class, 'buat'])->name('izin.buat');
    Route::post('/izin/simpan', [IzinMasukController::class, 'simpan'])->name('izin.simpan');
    Route::get('/persetujuan', [IzinMasukController::class, 'daftarPersetujuan'])->name('izin.persetujuan');
    Route::post('/izin/{izin}/proses-persetujuan', [IzinMasukController::class, 'prosesPersetujuan'])->name('izin.persetujuan.proses');
    Route::get('/izin/riwayat', [IzinMasukController::class, 'riwayat'])->name('izin.riwayat');
    Route::get('/izin/detail/{izin}', [IzinMasukController::class, 'detail'])->name('izin.detail');
    Route::get('/izin/cetak/{izin}', [IzinMasukController::class, 'cetak'])->name('izin.cetak');
    Route::post('/izin/batal/{izin}', [IzinMasukController::class, 'batal'])->name('izin.batal');

    // Rute Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // ==============================================================
    // 🔥 RUTE BARU: SIMPAN FCM TOKEN (Memperbaiki error RouteNotFound)
    // ==============================================================
    Route::post('/fcm-token', [ProfileController::class, 'saveToken'])->name('save.fcm.token');

});

// ===================================================================
// RUTE ADMIN (KHUSUS ADMIN)
// ===================================================================
Route::middleware(['auth', RoleAccessCheck::class . ':admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

    // ... (Rute Admin lainnya) ...
    Route::get('/', [AdminController::class, 'index'])->name('dashboard'); 
    Route::get('/pengguna', [AdminController::class, 'index'])->name('pengguna');
    Route::get('/pengguna/buat', [AdminController::class, 'create'])->name('pengguna.buat');
    Route::post('/pengguna', [AdminController::class, 'store'])->name('pengguna.store'); 
    Route::get('/pengguna/{user}/edit', [AdminController::class, 'edit'])->name('pengguna.edit');
    Route::patch('/pengguna/{user}', [AdminController::class, 'update'])->name('pengguna.update');
    Route::delete('/pengguna/{user}', [AdminController::class, 'destroy'])->name('pengguna.delete');
    Route::get('/laporan', [AdminController::class, 'laporan'])->name('laporan');
    Route::delete('/laporan/{izin}', [AdminController::class, 'deleteIzin'])->name('laporan.delete');
});

// ===================================================================
// RUTE AUTENTIKASI (LOGIN, REGISTER, DLL)
// ===================================================================
require __DIR__ . '/auth.php';