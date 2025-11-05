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
            // Tambahkan kolom id_pemohon (Foreign Key ke tabel users)
            $table->unsignedBigInteger('id_pemohon')->nullable()->after('ttd_pemohon_path');
            
            // Definisikan sebagai Foreign Key
            $table->foreign('id_pemohon')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Kembalikan (rollback) migrasi.
     */
    public function down(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            // Hapus foreign key terlebih dahulu
            $table->dropForeign(['id_pemohon']);
            // Hapus kolom
            $table->dropColumn('id_pemohon');
        });
    }
};
