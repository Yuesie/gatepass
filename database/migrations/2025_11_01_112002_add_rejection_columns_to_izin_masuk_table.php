<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            // Kolom untuk melacak status penolakan
            $table->boolean('l1_rejected')->default(false)->after('tgl_persetujuan_l3');
            $table->boolean('l2_rejected')->default(false)->after('l1_rejected');
            $table->boolean('l3_rejected')->default(false)->after('l2_rejected');
            // Kolom untuk menyimpan semua alasan penolakan
            $table->json('rejection_notes')->nullable()->after('l3_rejected');
        });
    }

    public function down(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            $table->dropColumn(['l1_rejected', 'l2_rejected', 'l3_rejected', 'rejection_notes']);
        });
    }
};
