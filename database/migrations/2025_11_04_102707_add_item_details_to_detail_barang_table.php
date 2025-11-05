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
            // Cek semua kolom sebelum menambahkan untuk menghindari error 1060

            if (!Schema::hasColumn('detail_barang', 'nama_item')) {
                $table->string('nama_item', 255)->nullable()->after('izin_masuk_id');
            }

            if (!Schema::hasColumn('detail_barang', 'qty')) {
                $table->integer('qty')->nullable();
            }

            if (!Schema::hasColumn('detail_barang', 'satuan')) {
                $table->string('satuan', 50)->nullable();
            }
            
            if (!Schema::hasColumn('detail_barang', 'keterangan_item')) {
                $table->string('keterangan_item', 255)->nullable(); 
            }
        });
    }

    /**
     * Kembalikan (rollback) migrasi.
     */
    public function down(): void
    {
        // Tetap biarkan dropColumn di sini, agar rollback berjalan normal
        Schema::table('detail_barang', function (Blueprint $table) {
            $table->dropColumn(['nama_item', 'qty', 'satuan', 'keterangan_item']);
        });
    }
};
