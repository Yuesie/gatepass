<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('izin_masuk', function (Blueprint $table) {
        // Hanya lakukan RENAME, jangan melakukan operasi ADD/DROP di sini
        $table->renameColumn('l1_waktu_setuju', 'tgl_persetujuan_l1');
        $table->renameColumn('l1_id_penyetuju', 'id_approver_l1');
        $table->renameColumn('l2_waktu_setuju', 'tgl_persetujuan_l2');
        $table->renameColumn('l2_id_penyetuju', 'id_approver_l2');
        // PASTIKAN SEMUA NAMA KOLOM INI ADA DI DATABASE DARI MIGRASI SEBELUMNYA!
    });
}

    public function down(): void
    {
        // ... (down function yang aman)
    }
};