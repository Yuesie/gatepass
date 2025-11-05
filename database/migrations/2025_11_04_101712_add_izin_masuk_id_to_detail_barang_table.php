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
        // HANYA TAMBAHKAN JIKA KOLOM BELUM ADA (MEMPERBAIKI ERROR 1060)
        if (!Schema::hasColumn('detail_barang', 'izin_masuk_id')) {
            
            // Tambahkan kolom izin_masuk_id
            $table->unsignedBigInteger('izin_masuk_id')->after('id');
            
            // Definisikan sebagai Foreign Key
            $table->foreign('izin_masuk_id')
                  ->references('id')
                  ->on('izin_masuk')
                  ->onDelete('cascade');
        }
    });
}

    /**
     * Kembalikan (rollback) migrasi.
     */
    public function down(): void
    {
        Schema::table('detail_barang', function (Blueprint $table) {
            // Hapus foreign key terlebih dahulu
            $table->dropForeign(['izin_masuk_id']);
            // Hapus kolom
            $table->dropColumn('izin_masuk_id');
        });
    }
};
