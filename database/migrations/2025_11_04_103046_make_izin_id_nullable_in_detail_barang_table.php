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
            // Ubah kolom izin_id menjadi nullable (asumsi tipenya unsignedBigInteger)
            $table->unsignedBigInteger('izin_id')->nullable()->change();
        });
    }

    /**
     * Kembalikan (rollback) migrasi (Kembalikan ke NOT NULL).
     */
    public function down(): void
    {
        Schema::table('detail_barang', function (Blueprint $table) {
            // Mengubah kembali kolom izin_id menjadi NOT NULL
            $table->unsignedBigInteger('izin_id')->change();
        });
    }
};
