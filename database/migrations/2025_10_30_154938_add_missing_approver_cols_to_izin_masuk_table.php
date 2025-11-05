<?php

// database/migrations/YYYY_MM_DD_HHMMSS_add_missing_approver_cols_to_izin_masuk_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    //Schema::table('izin_masuk', function (Blueprint $table) {
        // Lakukan Rename kolom lama ke nama yang baru
        //$table->renameColumn('l1_waktu_setuju', 'tgl_persetujuan_l1');
        //$table->renameColumn('l1_id_penyetuju', 'id_approver_l1');
        //$table->renameColumn('l2_waktu_setuju', 'tgl_persetujuan_l2');
        //$table->renameColumn('l2_id_penyetuju', 'id_approver_l2');
        //$table->renameColumn('l3_waktu_setuju', 'tgl_persetujuan_l3');
        //$table->renameColumn('l3_id_penyetuju', 'id_approver_l3'); // Ini yang dibutuhkan
    //});
}

    //public function down(): void
    //{
      //  Schema::table('izin_masuk', function (Blueprint $table) {
            // Hanya hapus jika kolom ada
        //    if (Schema::hasColumn('izin_masuk', 'id_approver_l3')) {
           //     $table->dropForeign(['id_approver_l3']);
          //    $table->dropColumn(['id_approver_l3', 'tgl_persetujuan_l3']);
            //}
           // if (Schema::hasColumn('izin_masuk', 'id_approver_l2')) {
             //   $table->dropForeign(['id_approver_l2']);
               // $table->dropColumn(['id_approver_l2', 'tgl_persetujuan_l2']);
           // }
           // if (Schema::hasColumn('izin_masuk', 'id_approver_l1')) {
             //   $table->dropForeign(['id_approver_l1']);
              //  $table->dropColumn(['id_approver_l1', 'tgl_persetujuan_l1']);
           // }
       // });
   // }
};