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
        Schema::table('izin_masuk', function (Blueprint $table) {
            // Periksa apakah kolom 'nomor_izin' belum ada sebelum menambahkannya
            if (!Schema::hasColumn('izin_masuk', 'nomor_izin')) {
                $table->string('nomor_izin', 50)->nullable()->after('id');
            }
            
            // Periksa apakah kolom 'nomor_kontrak' belum ada sebelum menambahkannya
            if (!Schema::hasColumn('izin_masuk', 'nomor_kontrak')) {
                $table->string('nomor_kontrak', 100)->nullable()->after('nomor_izin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            // Periksa apakah kolom 'nomor_kontrak' ada sebelum menghapusnya
            if (Schema::hasColumn('izin_masuk', 'nomor_kontrak')) {
                $table->dropColumn('nomor_kontrak');
            }

            // Periksa apakah kolom 'nomor_izin' ada sebelum menghapusnya
            if (Schema::hasColumn('izin_masuk', 'nomor_izin')) {
                $table->dropColumn('nomor_izin');
            }
        });
    }
};
