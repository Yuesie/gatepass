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
            // Tambahkan kolom untuk menyimpan path TTD Pemohon
            $table->string('ttd_pemohon_path', 255)->nullable()->after('keterangan_umum'); 
        });
    }

    /**
     * Kembalikan (rollback) migrasi.
     */
    public function down(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            $table->dropColumn('ttd_pemohon_path');
        });
    }
};
