<?php

// app/Models/DetailBarang.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailBarang extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan di database
    protected $table = 'detail_barang';
    
    // Matikan $guarded agar $fillable yang digunakan.
    // protected $guarded = []; 
    
    // Kolom yang diizinkan untuk diisi (Mass Assignment)
    protected $fillable = [
        // ⚠️ Nama kolom yang digunakan di Controller Anda adalah 'izin_masuk_id'
        'izin_masuk_id', 
        
        // ⚠️ Sesuaikan nama kolom agar konsisten dengan input form dan Controller (nama_item)
        'nama_item', 
        
        // ⚠️ Sesuaikan nama kolom QTY/Volume agar konsisten
        'qty', 
        
        'satuan', 
        'keterangan_item',
        
        // ⭐ Kolom baru untuk menyimpan path foto
        'foto_path',
    ];

    // Relasi Many-to-One: Item ini milik satu Izin Masuk
    public function izinMasuk()
    {
        // ⚠️ Foreign key yang digunakan di Controller adalah 'izin_masuk_id'
        return $this->belongsTo(IzinMasuk::class, 'izin_masuk_id');
    }
}