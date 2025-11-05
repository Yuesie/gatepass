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
            // Ubah kolom volume menjadi nullable
            // Asumsi tipe datanya adalah integer atau decimal.
            $table->decimal('volume', 8, 2)->nullable()->change(); 
        });
    }

    /**
     * Kembalikan (rollback) migrasi.
     */
    public function down(): void
    {
        Schema::table('detail_barang', function (Blueprint $table) {
            // Mengubah kembali kolom volume menjadi NOT NULL
            $table->decimal('volume', 8, 2)->change();
        });
    }
};
