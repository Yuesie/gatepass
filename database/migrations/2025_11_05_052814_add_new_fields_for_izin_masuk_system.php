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
        // Perubahan pada tabel 'users' (Untuk data dari form registrasi)
        Schema::table('users', function (Blueprint $table) {
            // Kolom 'jabatan_default' dihilangkan karena sudah ada.
            
            // Koreksi panjang kolom peran (disarankan)
            $table->string('peran', 50)->change(); 
        });

        // Perubahan pada tabel 'izin_masuk' (Untuk data dari form pengajuan)
        Schema::table('izin_masuk', function (Blueprint $table) {
            // 3. Tambahkan kolom jabatan_fungsi_pemohon
            $table->string('jabatan_fungsi_pemohon', 100)->nullable()->after('fungsi_pemohon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback untuk tabel 'users'
        Schema::table('users', function (Blueprint $table) {
            // Gunakan cek untuk menghindari error jika kolom sudah dihapus di tempat lain
            if (Schema::hasColumn('users', 'jabatan_default')) {
                $table->dropColumn('jabatan_default');
            }
        });

        // Rollback untuk tabel 'izin_masuk'
        Schema::table('izin_masuk', function (Blueprint $table) {
            $table->dropColumn('jabatan_fungsi_pemohon');
        });
    }
};
