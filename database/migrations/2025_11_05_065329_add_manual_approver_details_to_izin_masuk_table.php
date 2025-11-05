<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            // Field Manual untuk L1 (Atasan Pemohon)
            $table->string('jabatan_approver_l1', 100)->nullable();
            $table->string('nama_approver_l1', 100)->nullable();
            
            // Field Manual untuk L2 (Security)
            $table->string('jabatan_approver_l2', 100)->nullable();
            $table->string('nama_approver_l2', 100)->nullable();
            
            // Field Manual untuk L3 (Manager)
            $table->string('jabatan_approver_l3', 100)->nullable();
            $table->string('nama_approver_l3', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('izin_masuk', function (Blueprint $table) {
            $table->dropColumn([
                'jabatan_approver_l1', 'nama_approver_l1',
                'jabatan_approver_l2', 'nama_approver_l2',
                'jabatan_approver_l3', 'nama_approver_l3'
            ]);
        });
    }
};
