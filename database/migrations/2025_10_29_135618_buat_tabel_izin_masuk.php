<?php

// database/migrations/*_buat_tabel_izin_masuk.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel utama untuk Header Gatepass
        Schema::create('izin_masuk', function (Blueprint $table) {
            $table->id();

            // === DATA UTAMA (Dari No. Registrasi) ===
            $table->string('nomor_izin')->unique();              // Nomor Izin (contoh: 10843921/GP-ITBJM/2025)
            $table->date('tanggal');                            // Tanggal Izin
            $table->string('fungsi_pemohon');                   // Fungsi Pemohon (Contoh: Maintenance Planning)
            $table->string('dasar_pekerjaan');                  // Dasar Pekerjaan
            $table->string('perihal');                          // Perihal (Masuk/Keluar Material)
            $table->string('dokumen_pendukung')->nullable();     // Dokumen Pendukung (Opsional)
            $table->string('nama_perusahaan')->nullable();      // Nama Perusahaan/Pembawa
            $table->string('tujuan_pengiriman')->nullable();    // Tujuan Pengiriman
            $table->string('pembawa_barang')->nullable();       // Nama Pembawa Barang
            $table->string('nomor_kendaraan')->nullable();      // Nomor Polisi
            $table->string('id_safetyman')->nullable();         // ID Safetyman
            $table->text('keterangan_barang')->nullable();      // Keterangan umum barang

            // === RELASI: ID Pembuat Izin (Requester) ===
            $table->foreignId('id_pembuat')->constrained('users'); // Terhubung ke tabel 'users'

            // === STATUS PERSETUJUAN PARALEL ===
            $table->enum('status', ['Draf', 'Menunggu', 'Disetujui Final', 'Ditolak'])->default('Draf');
            
            // Level 1: Atasan Pemohon (Diajukan Oleh)
            $table->foreignId('l1_id_penyetuju')->nullable()->constrained('users'); // ID Penyetuju Level 1
            $table->dateTime('l1_waktu_setuju')->nullable();                      // Waktu persetujuan Level 1
            
            // Level 2: Security (Diperiksa Oleh)
            $table->foreignId('l2_id_penyetuju')->nullable()->constrained('users');
            $table->dateTime('l2_waktu_setuju')->nullable();                      // Waktu persetujuan Level 2

            // Level 3: Manajemen (Disetujui Oleh)
            $table->foreignId('l3_id_penyetuju')->nullable()->constrained('users');
            $table->dateTime('l3_waktu_setuju')->nullable();                      // Waktu persetujuan Level 3
            
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }
    // ...
};