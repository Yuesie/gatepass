<?php

// database/migrations/*_tambah_kolom_peran_ke_tabel_users.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menambahkan kolom 'peran'
        Schema::table('users', function (Blueprint $table) {
            $table->enum('peran', ['pemohon', 'atasan_pemohon', 'security', 'manager', 'admin', 'teknik', 'hsse'])
                  ->default('pemohon') // Default peran saat mendaftar adalah Pemohon
                  ->after('email');     // Kolom diletakkan setelah kolom 'email'
        });
    }

    public function down(): void
    {
        // Jika migrasi dibatalkan (rollback), kolom 'peran' harus dihapus
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('peran');
        });
    }
};