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
        Schema::table('izin_masuk', function (Blueprint $table) {
            // Ubah kolom id_pembuat menjadi nullable
            $table->unsignedBigInteger('id_pembuat')->nullable()->change();
        });
    }

    /**
     * Kembalikan (rollback) migrasi (Kembalikan ke NOT NULL).
     */
    public function down(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            // Mengubah kembali kolom id_pembuat menjadi NOT NULL
            $table->unsignedBigInteger('id_pembuat')->change();
        });
    }
};
