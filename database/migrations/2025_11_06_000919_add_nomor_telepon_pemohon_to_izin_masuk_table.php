<?php

// database/migrations/XXXX_XX_XX_add_nomor_telepon_pemohon_to_izin_masuk_table.php

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
        Schema::table('izin_masuk', function (Blueprint $table) {
            // Menambahkan kolom nomor_telepon_pemohon
            // Kolom ini disetel nullable karena di Controller kita set 'nullable'
            $table->string('nomor_telepon_pemohon', 20)->nullable()->after('fungsi_pemohon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            // Untuk rollback/hapus kolom
            $table->dropColumn('nomor_telepon_pemohon');
        });
    }
};
