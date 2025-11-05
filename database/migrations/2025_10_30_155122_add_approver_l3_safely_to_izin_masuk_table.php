<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            // Karena error Anda fokus pada L3, kita fokus pada L3 dan L2 sebagai referensi
            if (!Schema::hasColumn('izin_masuk', 'id_approver_l3')) {
                // Tambahkan kolom L3 yang hilang
                // Penempatan kolom 'after' mungkin perlu disesuaikan jika id_approver_l2 juga hilang.
                // Jika id_approver_l2 ada, gunakan tgl_persetujuan_l2 sebagai referensi.
                
                // Tambahkan kolom id_approver_l3
                $table->unsignedBigInteger('id_approver_l3')->nullable()->after('status')->nullable(); 
                
                // Tambahkan kolom tgl_persetujuan_l3
                $table->timestamp('tgl_persetujuan_l3')->nullable()->after('id_approver_l3');
                
                // Tambahkan foreign key
                $table->foreign('id_approver_l3')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            if (Schema::hasColumn('izin_masuk', 'id_approver_l3')) {
                $table->dropForeign(['id_approver_l3']);
                $table->dropColumn(['id_approver_l3', 'tgl_persetujuan_l3']);
            }
        });
    }
};
