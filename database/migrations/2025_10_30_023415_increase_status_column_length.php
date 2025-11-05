<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            // Ubah tipe kolom 'status' menjadi VARCHAR(50)
            $table->string('status', 50)->change();
        });
    }

    public function down(): void
{
    Schema::table('izin_masuk', function (Blueprint $table) {
        // UBAH DARI VARCHAR(10) menjadi VARCHAR(50) atau lebih besar
        $table->string('status', 50)->change(); 
    });
}
};
