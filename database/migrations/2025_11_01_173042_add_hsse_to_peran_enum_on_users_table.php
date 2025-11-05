<?php
// resources/views/2025_11_01_173042_add_hsse_to_peran_enum_on_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // PERBAIKAN: Ganti 'manajemen' menjadi 'manager' dan pastikan 7 peran lengkap
            $table->enum('peran', [
                'pemohon', 
                'atasan_pemohon', 
                'security', 
                'manager', // <<< KOREKSI
                'admin', 
                'hsse', 
                'teknik'
            ])
            ->default('pemohon')
            ->change(); 
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert ke 5 peran awal (tanpa hsse & teknik) dan gunakan 'manager'
            $table->enum('peran', ['pemohon', 'atasan_pemohon', 'security', 'manager', 'admin']) // <<< KOREKSI
                  ->default('pemohon')
                  ->change();
        });
    }
};