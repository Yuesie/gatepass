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
    protected $guarded = [];
    
    // Kolom yang diizinkan untuk diisi
    protected $fillable = [
        'izin_id', 'nama_barang', 'volume', 'satuan', 'keterangan_item',
    ];

    // Relasi Many-to-One: Item ini milik satu Izin Masuk
    public function izinMasuk()
    {
        return $this->belongsTo(IzinMasuk::class, 'izin_id');
    }
}
