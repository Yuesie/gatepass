<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\IzinMasukController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\PenggunaController;
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

    // --- Dashboard ---
    Route::get('/dashboard', [IzinMasukController::class, 'dashboard'])->name('dashboard');

    // --- Pengajuan Izin ---
    Route::get('/izin/buat', [IzinMasukController::class, 'buat'])->name('izin.buat');
    Route::post('/izin/simpan', [IzinMasukController::class, 'simpan'])->name('izin.simpan');

    // --- Persetujuan Izin ---
    Route::get('/persetujuan', [IzinMasukController::class, 'daftarPersetujuan'])->name('izin.persetujuan');
    Route::post('/izin/{izin}/proses-persetujuan', [IzinMasukController::class, 'prosesPersetujuan'])->name('izin.persetujuan.proses');

    // --- Riwayat dan Detail Izin ---
    Route::get('/izin/riwayat', [IzinMasukController::class, 'riwayat'])->name('izin.riwayat');
    Route::get('/izin/detail/{izin}', [IzinMasukController::class, 'detail'])->name('izin.detail');
    Route::get('/izin/cetak/{izin}', [IzinMasukController::class, 'cetak'])->name('izin.cetak');

    // --- Pembatalan Izin ---
    Route::post('/izin/batal/{izin}', [IzinMasukController::class, 'batal'])->name('izin.batal');

    // --- Profil User ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ===================================================================
// RUTE ADMIN (KHUSUS ADMIN)
// ===================================================================
Route::middleware(['auth', 'role.check:admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

    // =====================================================
    // DASHBOARD ADMIN
    // =====================================================
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    // =====================================================
    // KELOLA PENGGUNA (CRUD AKUN)
    // =====================================================
    Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
    Route::get('/pengguna/buat', [PenggunaController::class, 'create'])->name('pengguna.buat');
    Route::post('/pengguna/simpan', [PenggunaController::class, 'store'])->name('pengguna.store');
    Route::get('/pengguna/edit/{id}', [PenggunaController::class, 'edit'])->name('pengguna.edit');
    Route::put('/pengguna/update/{id}', [PenggunaController::class, 'update'])->name('pengguna.update');
    Route::delete('/pengguna/hapus/{id}', [PenggunaController::class, 'destroy'])->name('pengguna.delete');

    // =====================================================
    // LAPORAN
    // =====================================================
    Route::get('/laporan', [AdminController::class, 'laporan'])->name('laporan');
});

// ===================================================================
// RUTE AUTENTIKASI (LOGIN, REGISTER, DLL)
// ===================================================================
require __DIR__ . '/auth.php';
