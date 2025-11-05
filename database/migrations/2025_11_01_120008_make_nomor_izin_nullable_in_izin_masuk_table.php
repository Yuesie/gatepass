<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            // Mengubah kolom nomor_izin agar dapat menerima nilai NULL
            $table->string('nomor_izin')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            // Mengembalikan kolom nomor_izin menjadi NOT NULL (Jika diperlukan rollback)
            // Namun, ini bisa menyebabkan error jika ada baris dengan nilai null.
            $table->string('nomor_izin')->nullable(false)->change();
        });
    }
};
