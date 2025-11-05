<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('detail_barang', function (Blueprint $table) {
            // Ubah kolom nama_barang menjadi nullable
            $table->string('nama_barang')->nullable()->change();
        });
    }

    /**
     * Kembalikan (rollback) migrasi.
     */
    public function down(): void
    {
        Schema::table('detail_barang', function (Blueprint $table) {
            // Mengubah kembali kolom nama_barang menjadi NOT NULL (sesuaikan dengan definisi awal Anda jika perlu)
            $table->string('nama_barang')->change();
        });
    }
};
