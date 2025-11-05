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

    Route::get('/izin/buat', [IzinMasukController::class, 'buat'])->name('izin.buat');
    Route::post('/izin/simpan', [IzinMasukController::class, 'simpan'])->name('izin.simpan');

    Route::get('/persetujuan', [IzinMasukController::class, 'daftarPersetujuan'])->name('izin.persetujuan');
    Route::post('/izin/{izin}/proses-persetujuan', [IzinMasukController::class, 'prosesPersetujuan'])->name('izin.persetujuan.proses');

    Route::get('/izin/riwayat', [IzinMasukController::class, 'riwayat'])->name('izin.riwayat');
    Route::get('/izin/detail/{izin}', [IzinMasukController::class, 'detail'])->name('izin.detail');
    Route::get('/izin/cetak/{izin}', [IzinMasukController::class, 'cetak'])->name('izin.cetak');
    Route::post('/izin/batal/{izin}', [IzinMasukController::class, 'batal'])->name('izin.batal');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ===================================================================
// RUTE ADMIN (KHUSUS ADMIN) - SOLUSI FINAL BINDING
// ===================================================================
Route::middleware(['auth', RoleAccessCheck::class . ':admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

    // DASHBOARD ADMIN (Opsional, jika ada)
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    // =====================================================
    // KELOLA PENGGUNA (CRUD)
    // =====================================================
    // Index Pengguna
    Route::get('/pengguna', [AdminController::class, 'pengguna'])->name('pengguna');
    
    // Create (Form dan Store)
    Route::get('/pengguna/buat', [AdminController::class, 'buatPengguna'])->name('pengguna.buat');
    Route::post('/pengguna', [AdminController::class, 'simpanPengguna'])->name('pengguna.store'); // <-- POST URL SIMPAN
    
    // Update (Form Edit dan Proses Update)
    Route::get('/pengguna/{user}/edit', [AdminController::class, 'editPengguna'])->name('pengguna.edit');
    Route::patch('/pengguna/{user}', [AdminController::class, 'updatePengguna'])->name('pengguna.update');
    
    // Delete
    Route::delete('/pengguna/{user}', [AdminController::class, 'hapusPengguna'])->name('pengguna.delete');

    // =====================================================
    // LAPORAN
    // =====================================================
    Route::get('/laporan', [AdminController::class, 'laporan'])->name('laporan');
});

// ===================================================================
// RUTE AUTENTIKASI (LOGIN, REGISTER, DLL)
// ===================================================================
require __DIR__ . '/auth.php';