<?php

// database/migrations/*_buat_tabel_detail_barang.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel untuk detail item (Daftar Barang)
        Schema::create('detail_barang', function (Blueprint $table) {
            $table->id();

            // Relasi ke Tabel Izin Masuk (Foreign Key)
            $table->foreignId('izin_id')->constrained('izin_masuk')->onDelete('cascade'); // Terhubung ke tabel 'izin_masuk'

            // Data Barang (dari Daftar Barang)
            $table->string('nama_barang');            // Nama Barang/Material
            $table->integer('volume');                // Volume/Jumlah
            $table->string('satuan');                 // Satuan (Pcs, Kg, dsb.)
            $table->text('keterangan_item')->nullable();    // Keterangan Khusus Item

            $table->timestamps();
        });
    }
    // ...
};
