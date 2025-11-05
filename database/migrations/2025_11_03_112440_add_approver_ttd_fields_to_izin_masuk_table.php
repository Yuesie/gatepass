<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            // Kolom ini TIDAK akan dibuat jika sudah ada (karena ttd_pemohon sudah dibuat di migrasi Anda)
            // Namun, untuk Approver, kolom ini WAJIB:
            $table->string('ttd_approver_l1')->nullable()->after('tgl_persetujuan_l1');
            $table->string('ttd_approver_l2')->nullable()->after('tgl_persetujuan_l2');
            $table->string('ttd_approver_l3')->nullable()->after('tgl_persetujuan_l3');
        });
    }

    public function down(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            $table->dropColumn(['ttd_approver_l1', 'ttd_approver_l2', 'ttd_approver_l3']);
        });
    }
};