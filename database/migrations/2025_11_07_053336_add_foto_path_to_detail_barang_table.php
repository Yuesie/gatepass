<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
        */
    public function up()
    {
        Schema::table('detail_barang', function (Blueprint $table) {
            $table->string('foto_path')->nullable()->after('keterangan_item');
        });
    }

    // ... di dalam method down()
    public function down()
    {
        Schema::table('detail_barang', function (Blueprint $table) {
            $table->dropColumn('foto_path');
        });
    }
};
