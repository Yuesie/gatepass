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
        Schema::table('users', function (Blueprint $table) {
            // 1. Tambahkan kolom jabatan_default (Wajib)
            $table->string('jabatan_default')->nullable()->after('email');
            
            // 2. Modifikasi kolom peran (Disarankan)
            // Mengubah string agar lebih panjang dan boleh null sementara jika perlu
            $table->string('peran', 50)->change(); // Ubah panjang string, misal 50
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom yang ditambahkan
            $table->dropColumn('jabatan_default');
            
            // Kembalikan perubahan peran jika diperlukan
            // $table->string('peran', 255)->change(); // Contoh mengembalikan ke default
        });
    }
};
