<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\IzinMasukController;
use App\Http\Controllers\AdminController; 
use Illuminate\Support\Facades\Route;

// Rute Halaman Utama
Route::get('/', function () {
    return view('welcome');
});

// ===================================================================
// RUTE UMUM TER-AUTENTIKASI
// ===================================================================
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Rute Dashboard
    Route::get('/dashboard', [IzinMasukController::class, 'dashboard'])->name('dashboard');
    
    // --- Rute Pengajuan Izin ---
    Route::get('/izin/buat', [IzinMasukController::class, 'buat'])->name('izin.buat');
    Route::post('/izin/simpan', [IzinMasukController::class, 'simpan'])->name('izin.simpan');
    
    // --- Rute Persetujuan ---
    Route::get('/persetujuan', [IzinMasukController::class, 'daftarPersetujuan'])->name('izin.persetujuan');
    Route::post('/izin/{izin}/proses-persetujuan', [IzinMasukController::class, 'prosesPersetujuan'])->name('izin.persetujuan.proses');
    
    // --- Rute Riwayat dan Detail ---
    Route::get('/izin/riwayat', [IzinMasukController::class, 'riwayat'])->name('izin.riwayat');
    Route::get('/izin/detail/{izin}', [IzinMasukController::class, 'detail'])->name('izin.detail');
    Route::get('/izin/cetak/{izin}', [IzinMasukController::class, 'cetak'])->name('izin.cetak');
    
    // --- Rute Aksi Lainnya (Batal) ---
    Route::post('/izin/batal/{izin}', [IzinMasukController::class, 'batal'])->name('izin.batal'); 

    // --- Rute Profil User ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ===================================================================
// RUTE ADMINISTRASI (Khusus Admin)
// ===================================================================
Route::middleware(['auth', 'role.check:admin']) // ⬅️ Menggunakan alias baru
    ->prefix('admin')->name('admin.')->group(function () {
    
    // 1. Rute Kelola Pengguna (CRUD)
    Route::get('/pengguna', [AdminController::class, 'pengguna'])->name('pengguna');
    Route::get('/pengguna/buat', [AdminController::class, 'buatPengguna'])->name('pengguna.buat');
    Route::post('/pengguna', [AdminController::class, 'storePengguna'])->name('pengguna.store'); 
    Route::get('/pengguna/{id}/edit', [AdminController::class, 'editPengguna'])->name('pengguna.edit');
    Route::put('/pengguna/{id}', [AdminController::class, 'updatePengguna'])->name('pengguna.update');
    Route::delete('/pengguna/{id}', [AdminController::class, 'deletePengguna'])->name('pengguna.delete');

    // 2. Rute Laporan Global
    Route::get('/laporan', [AdminController::class, 'laporan'])->name('laporan');
});


// Rute Autentikasi Laravel Breeze (Login, Register, dll.)
require __DIR__.'/auth.php';