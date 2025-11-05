<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            // Tambahkan kolom keterangan_umum sebagai TEXT atau string panjang
            $table->text('keterangan_umum')->nullable(); 
        });
    }

    public function down(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            $table->dropColumn('keterangan_umum');
        });
    }
};
