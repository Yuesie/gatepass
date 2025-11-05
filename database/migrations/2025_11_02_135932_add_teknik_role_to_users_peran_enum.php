<?php
// resources/views/2025_11_02_135932_add_teknik_role_to_users_peran_enum.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; 

return new class extends Migration
{
    public function up(): void
    {
        // KOREKSI: Gunakan 'manager' dan daftar 7 peran lengkap (termasuk hsse dan teknik)
        DB::statement("ALTER TABLE users MODIFY peran ENUM('pemohon', 'atasan_pemohon', 'security', 'manager', 'admin', 'hsse', 'teknik') DEFAULT 'pemohon'");
    }

    public function down(): void
    {
        // KOREKSI: Gunakan 'manager' dan hapus hsse/teknik
        DB::statement("ALTER TABLE users MODIFY peran ENUM('pemohon', 'atasan_pemohon', 'security', 'manager', 'admin') DEFAULT 'pemohon'");
    }
};