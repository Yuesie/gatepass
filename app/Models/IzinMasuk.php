<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// --- BARIS YANG HILANG/DIPERLUKAN ---
use App\Models\User; 
use App\Models\DetailBarang; 

class IzinMasuk extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'izin_masuk'; 

    // Field yang diizinkan untuk Mass Assignment
    protected $fillable = [
        // Data Umum & Pengiriman
        'tanggal',
        'jenis_izin',
        'fungsi_pemohon',
        'jabatan_fungsi_pemohon',
        'dasar_pekerjaan',
        'perihal',
        'nama_perusahaan',
        'tujuan_pengiriman',
        'pembawa_barang',
        'nomor_kendaraan',
        'keterangan_umum',
        'ttd_pemohon',

        // Data Otomatis & Status
        'id_pemohon',
        'status', 
        'nomor_izin', 
        
        // --- ID Approver (Otorisasi) ---
        'id_approver_l1',
        'id_approver_l2',
        'id_approver_l3',

        // --- Field Manual Approver (Dokumentasi Cetak) ---
        'jabatan_approver_l1',
        'nama_approver_l1',
        'jabatan_approver_l2',
        'nama_approver_l2',
        'jabatan_approver_l3',
        'nama_approver_l3',
        
        // Data Persetujuan
        'tgl_approve_l1', 'tgl_approve_l2', 'tgl_approve_l3',
    ];

    // Relasi ke User (Pembuat Gatepass)
    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pemohon');
    }

    // Relasi ke User (Approver L1, L2, L3)
    public function approverL1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_approver_l1');
    }

    public function approverL2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_approver_l2');
    }

    public function approverL3(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_approver_l3');
    }
    
    // Relasi ke Detail Barang (Di sinilah IzinMasukDetail dipanggil)
    public function details()
    {
        return $this->hasMany(DetailBarang::class, 'izin_masuk_id');
    }
}