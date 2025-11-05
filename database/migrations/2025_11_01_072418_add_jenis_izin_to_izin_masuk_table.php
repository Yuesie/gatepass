<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Safety Check: Pastikan kolom belum ada sebelum menambahkannya
        if (!Schema::hasColumn('izin_masuk', 'jenis_izin')) {
            Schema::table('izin_masuk', function (Blueprint $table) {
                // Menambahkan kolom 'jenis_izin' sebagai ENUM ('masuk' atau 'keluar')
                // Kolom ditempatkan setelah kolom 'tanggal'
                $table->enum('jenis_izin', ['masuk', 'keluar'])->after('tanggal');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            // Menghapus kolom jika migrasi di-rollback
            $table->dropColumn('jenis_izin');
        });
    }
};