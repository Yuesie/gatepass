<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Formulir Pengajuan Izin Masuk / Keluar') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    
                    @php
                        // DEFINISI OPSI PERIHAl & KETERANGAN ITEM
                        $perihalOptions = [
                            'Material Masuk & Tidak Keluar kembali', 
                            'Material Masuk & Keluar kembali', 
                            'Material Keluar & Tidak Masuk Lagi', 
                            'Material Keluar & Masuk Kembali', 
                        ];
                        $keteranganItemOptions = $perihalOptions; 
                        
                        // FUNGSI PEMOHON DENGAN DAFTAR BARU (Digunakan untuk Filtering ID Approver DB)
                        $fungsiOptions = ['MPS', 'HSSE & FS', 'RSD', 'QQ', 'SSGA']; 

                        // JABATAN APPROVER L1 SPESIFIK (Untuk Tampilan Cetak/Form Input)
                        $jabatanL1Options = [
                            'Spv II MPS',
                            'SPV II HSSE & FS',
                            'Sr Spv RSD',
                            'Spv I QQ',
                            'Spv I SSGA',
                        ];

                        // JABATAN SPECIFIC UNTUK L2 & L3
                        $jabatanL2Options = [ 
                            'Jr Assistant Security',
                        ];
                        // 🛑 KOREKSI KRITIS: Gunakan string LENGKAP dari database users
                        $jabatanL3Options = [
                            'IT Manager Banjarmasin', 
                            'Pjs IT Manager Banjarmasin' 
                        ];
                        
                    @endphp

                    {{-- Pesan Sukses & Error --}}
                    @if (session('success'))
                        <div class="p-4 mb-6 text-green-800 bg-green-100 border border-green-300 rounded-lg shadow">
                            {!! session('success') !!}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="p-4 mb-6 text-red-800 bg-red-100 border border-red-300 rounded-lg shadow">
                            <strong>Gagal!</strong> {!! session('error') !!}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="p-4 mb-6 text-red-800 bg-red-100 border border-red-300 rounded-lg shadow">
                            <strong>Terjadi kesalahan pada input:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('izin.simpan') }}" method="POST" id="izinForm">
                        @csrf
                        
                        {{-- ================================================= --}}
                        {{-- BAGIAN 1: DATA UMUM IZIN --}}
                        {{-- ================================================= --}}
                        <h3 class="text-gray-900 text-2xl font-bold mb-6 border-b pb-2">Data Umum Izin</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Tanggal Izin --}}
                            <div class="col-span-1">
                                <label for="tanggal" class="block text-sm font-medium text-gray-700">Tanggal Izin <span class="text-red-500">*</span></label>
                                <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                                         class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('tanggal') border-red-500 @enderror">
                                @error('tanggal') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            {{-- Jenis Izin (Masuk/Keluar) --}}
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Jenis Izin <span class="text-red-500">*</span></label>
                                <div class="mt-1 space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="jenis_izin" value="masuk" {{ old('jenis_izin') == 'masuk' ? 'checked' : '' }} required
                                                 class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                        <span class="ml-2 text-sm text-gray-700">Izin Masuk</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="jenis_izin" value="keluar" {{ old('jenis_izin') == 'keluar' ? 'checked' : '' }} required
                                                 class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                        <span class="ml-2 text-sm text-gray-700">Izin Keluar</span>
                                    </label>
                                </div>
                                @error('jenis_izin') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Fungsi Pemohon (DROPDOWN) --}}
                            <div class="col-span-1">
                                <label for="fungsi_pemohon" class="block text-sm font-medium text-gray-700">Fungsi Pemohon <span class="text-red-500">*</span></label>
                                <select name="fungsi_pemohon" id="fungsi_pemohon" required
                                                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('fungsi_pemohon') border-red-500 @enderror">
                                    <option value="">-- Pilih Fungsi --</option>
                                    @foreach ($fungsiOptions as $fungsi)
                                        <option value="{{ $fungsi }}" {{ old('fungsi_pemohon') == $fungsi ? 'selected' : '' }}>
                                            {{ $fungsi }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('fungsi_pemohon') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Perihal (Dropdown) --}}
                            <div class="col-span-1">
                                <label for="perihal" class="block text-sm font-medium text-gray-700">Perihal <span class="text-red-500">*</span></label>
                                <select name="perihal" id="perihal" required
                                                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('perihal') border-red-500 @enderror">
                                    <option value="">-- Pilih Perihal --</option>
                                    @foreach ($perihalOptions as $perihal)
                                        <option value="{{ $perihal }}" {{ old('perihal') == $perihal ? 'selected' : '' }}>
                                            {{ $perihal }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('perihal') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- NOMOR TELEPON PEMOHON (name="nomor_telepon_pemohon") --}}
                            <div class="col-span-1">
                                <label for="nomor_telepon_pemohon" class="block text-sm font-medium text-gray-700">Nomor Telepon Pemohon</label>
                                <input type="text" name="nomor_telepon_pemohon" id="nomor_telepon_pemohon" 
                                         value="{{ old('nomor_telepon_pemohon') }}"
                                         class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('nomor_telepon_pemohon') border-red-500 @enderror">
                                @error('nomor_telepon_pemohon') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Dasar Pekerjaan --}}
                            <div class="col-span-1">
                                <label for="dasar_pekerjaan" class="block text-sm font-medium text-gray-700">Dasar Pekerjaan <span class="text-red-500">*</span></label>
                                <input type="text" name="dasar_pekerjaan" id="dasar_pekerjaan" value="{{ old('dasar_pekerjaan') }}" required
                                         class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('dasar_pekerjaan') border-red-500 @enderror">
                                @error('dasar_pekerjaan') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            {{-- Nomor Gatepass (Disabled) --}}
                            <div class="col-span-2">
                                <label for="nomor_izin" class="block text-sm font-medium text-gray-700">Nomor Gatepass</label>
                                <input type="text" value="Akan dibuat saat pengajuan" disabled
                                         class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm text-gray-500 text-sm">
                            </div>

                        </div>

                        {{-- ================================================= --}}
                        {{-- BAGIAN 2: DATA PENGIRIMAN / KENDARAAN --}}
                        {{-- ================================================= --}}
                        <h3 class="text-gray-900 text-2xl font-bold mb-6 border-b pb-2 mt-8">Data Pengiriman/Kendaraan</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Nama Perusahaan --}}
                            <div class="col-span-1">
                                <label for="nama_perusahaan" class="block text-sm font-medium text-gray-700">Nama Perusahaan <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_perusahaan" id="nama_perusahaan" value="{{ old('nama_perusahaan') }}" required
                                         class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('nama_perusahaan') border-red-500 @enderror">
                                @error('nama_perusahaan') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            {{-- Tujuan Pengiriman --}}
                            <div class="col-span-1">
                                <label for="tujuan_pengiriman" class="block text-sm font-medium text-gray-700">Tujuan Pengiriman <span class="text-red-500">*</span></label>
                                <input type="text" name="tujuan_pengiriman" id="tujuan_pengiriman" value="{{ old('tujuan_pengiriman') }}" required
                                         class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('tujuan_pengiriman') border-red-500 @enderror">
                                @error('tujuan_pengiriman') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Pembawa Barang --}}
                            <div class="col-span-1">
                                <label for="pembawa_barang" class="block text-sm font-medium text-gray-700">Nama Pembawa Barang / Driver <span class="text-red-500">*</span></label>
                                <input type="text" name="pembawa_barang" id="pembawa_barang" value="{{ old('pembawa_barang') }}" required
                                         class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('pembawa_barang') border-red-500 @enderror">
                                @error('pembawa_barang') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            {{-- Nomor Kendaraan (OPSIONAL) --}}
                            <div class="col-span-1">
                                <label for="nomor_kendaraan" class="block text-sm font-medium text-gray-700">Nomor Polisi</label>
                                <input type="text" name="nomor_kendaraan" id="nomor_kendaraan" value="{{ old('nomor_kendaraan') }}" 
                                         class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('nomor_kendaraan') border-red-500 @enderror">
                                @error('nomor_kendaraan') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                        </div>

                        {{-- ================================================= --}}
                        {{-- BAGIAN 3: PERSETUJUAN (INPUT NAMA TEKS) --}}
                        {{-- ================================================= --}}
                        <h3 class="text-gray-900 text-2xl font-bold mb-6 border-b pb-2 mt-8">Persetujuan Gatepass (Pilih Approver)</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                            {{-- Kolom L1 (Atasan) --}}
                            <div class="col-span-1">
                                {{-- 1. FUNGSI PEMOHON (Display) --}}
                                <label for="fungsi_pemohon_display" class="block text-sm font-medium text-gray-700">1. Fungsi Pemohon (dari Bagian 1) <span class="text-red-500">*</span></label>
                                <input type="text" id="fungsi_pemohon_display" disabled class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed text-sm">
                                
                                {{-- 2. PILIH JABATAN L1 (DROPDOWN, DIPERBAIKI MENGGUNAKAN JABATAN SPESIFIK) --}}
                                <label for="id_jabatan_l1" class="block text-sm font-medium text-gray-700 mt-2">Pilih Jabatan Atasan <span class="text-red-500">*</span></label>
                                <select name="jabatan_approver_l1" id="id_jabatan_l1" required
                                                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('jabatan_approver_l1') border-red-500 @enderror">
                                    <option value="">-- Pilih Jabatan --</option>
                                    @foreach ($jabatanL1Options as $jabatan)
                                        <option value="{{ $jabatan }}" {{ old('jabatan_approver_l1') == $jabatan ? 'selected' : '' }}>
                                            {{ $jabatan }}
                                        </option>
                                    @endforeach
                                </select>
                                
                                {{-- PESAN DINAMIS L1 INFO --}}
                                <p id="l1-jabatan-info" class="text-xs text-red-500 mt-1">Pilih Fungsi Pemohon di atas.</p>

                                {{-- 3. INPUT NAMA APPROVER L1 (TEKS MANUAL) --}}
                                <label for="nama_approver_l1" class="block text-sm font-medium text-gray-700 mt-2">Input Nama Approver Atasan <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_approver_l1" id="nama_approver_l1" value="{{ old('nama_approver_l1') }}" required
                                         class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('nama_approver_l1') border-red-500 @enderror">
                                
                                @error('nama_approver_l1')
                                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                                
                                {{-- Input tersembunyi untuk ID Database (biarkan kosong jika tidak ada ID yang dipilih) --}}
                                <input type="hidden" name="id_approver_l1" id="id_approver_l1" value="">
                            </div>
                            
                            {{-- Kolom L2 (Security) --}}
                            <div class="col-span-1">
                                {{-- 1. PILIH JABATAN SECURITY (DROPDOWN) --}}
                                <label for="id_jabatan_l2" class="block text-sm font-medium text-gray-700">2. Pilih Jabatan Security <span class="text-red-500">*</span></label>
                                <select name="jabatan_approver_l2" id="id_jabatan_l2" required
                                                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('jabatan_approver_l2') border-red-500 @enderror">
                                    <option value="">-- Pilih Jabatan --</option>
                                    @foreach ($jabatanL2Options as $jabatan)
                                        <option value="{{ $jabatan }}" {{ old('jabatan_approver_l2') == $jabatan ? 'selected' : '' }}>
                                            {{ $jabatan }}
                                        </option>
                                    @endforeach
                                </select>
                                
                                {{-- 2. INPUT NAMA APPROVER SECURITY (TEKS MANUAL) --}}
                                <label for="nama_approver_l2" class="block text-sm font-medium text-gray-700 mt-2">Input Nama Approver Security <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_approver_l2" id="nama_approver_l2" value="{{ old('nama_approver_l2') }}" required
                                         class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('nama_approver_l2') border-red-500 @enderror">

                                @error('nama_approver_l2')
                                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                                
                                {{-- Input tersembunyi untuk ID Database --}}
                                <input type="hidden" name="id_approver_l2" id="id_approver_l2" value="">
                            </div>
                            
                            {{-- Kolom L3 (Manager/PJS) --}}
                            <div class="col-span-1">
                                {{-- 1. PILIH JABATAN MANAGER/PJS (DROPDOWN) --}}
                                <label for="id_jabatan_l3" class="block text-sm font-medium text-gray-700">3. Pilih Jabatan Manager/PJS <span class="text-red-500">*</span></label>
                                <select name="jabatan_approver_l3" id="id_jabatan_l3" required
                                                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('jabatan_approver_l3') border-red-500 @enderror">
                                    <option value="">-- Pilih Jabatan --</option>
                                    @foreach ($jabatanL3Options as $jabatan)
                                        <option value="{{ $jabatan }}" {{ old('jabatan_approver_l3') == $jabatan ? 'selected' : '' }}>
                                            {{ $jabatan }}
                                        </option>
                                    @endforeach
                                </select>

                                {{-- 2. INPUT NAMA APPROVER MANAGER (TEKS MANUAL) --}}
                                <label for="nama_approver_l3" class="block text-sm font-medium text-gray-700 mt-2">Input Nama Approver Manager <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_approver_l3" id="nama_approver_l3" value="{{ old('nama_approver_l3') }}" required
                                         class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('nama_approver_l3') border-red-500 @enderror">

                                @error('nama_approver_l3')
                                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                                @enderror

                                {{-- Input tersembunyi untuk ID Database --}}
                                <input type="hidden" name="id_approver_l3" id="id_approver_l3" value="">
                            </div>

                        </div>

                        {{-- ================================================= --}}
                        {{-- BAGIAN 4 & 5 (TETAP SAMA) --}}
                        {{-- ================================================= --}}
                        <h3 class="text-gray-900 text-2xl font-bold mb-6 border-b pb-2 mt-8">Daftar Barang yang Dibawa</h3>

                        <div id="barang-container">
                            {{-- Baris barang akan ditambahkan di sini oleh JavaScript --}}
                        </div>

                        <button type="button" id="tambah-barang-btn"
                                 class="mt-4 px-4 py-2 bg-blue-500 text-white font-semibold rounded-lg shadow-md hover:bg-blue-600 transition">
                            + Tambah Item Barang
                        </button>
                        
                        {{-- TEMPLATE BARANG DINAMIS (HIDDEN) --}}
                        <template id="barang-template">
                            <div class="barang-row p-4 border border-gray-200 rounded-lg mb-4 bg-gray-50 flex flex-col md:flex-row gap-4 items-end">
                                
                                {{-- Nama Item --}}
                                <div class="flex-1 w-full">
                                    <label class="block text-sm font-medium text-gray-700">Nama Item / Material <span class="text-red-500">*</span></label>
                                    <input type="text" name="nama_item[]" required
                                         class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                {{-- Qty --}}
                                <div class="w-full md:w-20">
                                    <label class="block text-sm font-medium text-gray-700">Qty <span class="text-red-500">*</span></label>
                                    <input type="number" name="qty[]" min="1" required
                                         class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                {{-- Satuan --}}
                                <div class="w-full md:w-24">
                                    <label class="block text-sm font-medium text-gray-700">Satuan <span class="text-red-500">*</span></label>
                                    <input type="text" name="satuan[]" required
                                         class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                {{-- Keterangan Item --}}
                                <div class="flex-1 w-full">
                                    <label class="block text-sm font-medium text-gray-700">Keterangan Item <span class="text-red-500">*</span></label>
                                    <select name="keterangan_item[]" required
                                         class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">-- Pilih Keterangan --</option>
                                            @foreach ($keteranganItemOptions as $keterangan)
                                                <option value="{{ $keterangan }}">{{ $keterangan }}</option>
                                            @endforeach
                                    </select>
                                </div>
                                
                                {{-- Tombol Hapus --}}
                                <div class="w-full md:w-auto">
                                    <button type="button" class="hapus-barang-btn px-4 py-2 bg-red-400 text-white font-semibold rounded-md shadow-md hover:bg-red-500 transition w-full md:w-auto">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </template>

                        {{-- BAGIAN 5: TANDA TANGAN PEMOHON (DIGITAL BASAH) --}}
                        <h3 class="text-gray-900 text-2xl font-bold mb-6 border-b pb-2 mt-8">Tanda Tangan Pemohon</h3>

                        <div class="p-4 border border-indigo-300 rounded-lg bg-indigo-50">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanda Tangan Anda (Wajib)</label>
                            
                            {{-- Canvas Tempat Menggambar Tanda Tangan --}}
                            <canvas id="signatureCanvas" 
                                class="w-full border-2 border-dashed border-gray-400 bg-white rounded-md cursor-crosshair"
                                style="height: 150px; position: relative; z-index: 100;"></canvas>
                            
                            {{-- Input tersembunyi untuk data tanda tangan (Base64) --}}
                            <input type="hidden" name="ttd_pemohon" id="ttd_pemohon_data" 
                                value="{{ old('ttd_pemohon') }}">

                            {{-- Preview setelah tanda tangan (opsional, membantu user yakin tandanya tersimpan) --}}
                            <div id="signaturePreviewContainer" class="mt-3 hidden">
                                <p class="text-sm text-gray-700 mb-1">Preview Tanda Tangan:</p>
                                <img id="signaturePreview" class="border rounded-md max-h-32">
                            </div>

                            <div class="mt-2 flex justify-end space-x-2">
                                <button type="button" id="clearSignatureBtn"
                                    class="px-3 py-1 bg-red-500 text-white text-sm font-semibold rounded-lg hover:bg-red-600 transition">
                                    Hapus Tanda Tangan
                                </button>
                                <p class="text-sm text-gray-600 self-center">Gunakan mouse atau layar sentuh.</p>
                            </div>
                            <p class="text-sm text-red-500 mt-1">Anda wajib memberikan tanda tangan.</p>
                        </div>

                        <div class="mt-8">
                            <button type="submit"
                                class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition">
                                Simpan Pengajuan
                            </button>
                        </div>

                    </form>

                    {{-- ================================================= --}}
                    {{-- SCRIPT JAVASCRIPT (HANYA UNTUK TTD & BARANG) --}}
                    {{-- ================================================= --}}
                    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
                    <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        
                        // Selectors (Dipertahankan untuk Display)
                        const fungsiPemohonGlobalSelect = document.getElementById('fungsi_pemohon');
                        const fungsiPemohonDisplay = document.getElementById('fungsi_pemohon_display');
                        const l1JabatanInfo = document.getElementById('l1-jabatan-info'); // <-- Selektor pesan info Bagian 3
                        
                        // --- FUNGSI UTAMA UNTUK MENGISI DISPLAY L1 & MENGATUR INFO ---
                        function updateL1Display() {
                            const selectedFungsi = fungsiPemohonGlobalSelect.value;
                            fungsiPemohonDisplay.value = selectedFungsi;
                            
                            // LOGIKA PERBAIKAN PESAN DINAMIS L1 INFO
                            if (selectedFungsi && selectedFungsi !== '') {
                                l1JabatanInfo.textContent = `Anda memilih fungsi: ${selectedFungsi}. Pilih jabatan atasan yang sesuai.`;
                                l1JabatanInfo.classList.remove('text-red-500');
                                l1JabatanInfo.classList.add('text-green-600'); 
                            } else {
                                l1JabatanInfo.textContent = 'Pilih Fungsi Pemohon di atas.';
                                l1JabatanInfo.classList.remove('text-green-600');
                                l1JabatanInfo.classList.add('text-red-500');
                            }
                        }

                        // Panggil fungsi display saat DOMContentLoaded
                        updateL1Display();
                        
                        // Event Listeners (Hanya untuk Display Fungsi L1)
                        fungsiPemohonGlobalSelect.addEventListener('change', updateL1Display); 
                        
                        
                        // =================================================
                        // --- SCRIPT TANDA TANGAN DIGITAL (TTD) & BARANG DINAMIS ---
                        // =================================================
                        const canvas = document.getElementById('signatureCanvas');
                        const hiddenInput = document.getElementById('ttd_pemohon_data'); 
                        const clearButton = document.getElementById('clearSignatureBtn');
                        const form = document.getElementById('izinForm'); // Mengambil form berdasarkan ID
                        const previewContainer = document.getElementById('signaturePreviewContainer');
                        const previewImage = document.getElementById('signaturePreview');

                        // Restore old value for ttd input if validation fails
                        if (hiddenInput.value && hiddenInput.value.length > 100) {
                            previewImage.src = hiddenInput.value;
                            previewContainer.classList.remove('hidden');
                        }


                        const signaturePad = new SignaturePad(canvas, {
                            backgroundColor: 'rgb(255, 255, 255)',
                            onEnd: () => {
                                if (!signaturePad.isEmpty()) {
                                    const dataURL = signaturePad.toDataURL('image/png');
                                    hiddenInput.value = dataURL;
                                    previewImage.src = dataURL;
                                    previewContainer.classList.remove('hidden');
                                }
                            }
                        });

                        function resizeCanvas() {
                            const data = signaturePad.isEmpty() ? null : signaturePad.toData();
                            const ratio = Math.max(window.devicePixelRatio || 1, 1);
                            canvas.width = canvas.offsetWidth * ratio;
                            canvas.height = 150 * ratio;
                            canvas.getContext("2d").scale(ratio, ratio);
                            if (data) signaturePad.fromData(data);
                        }
                        window.addEventListener('resize', resizeCanvas);
                        resizeCanvas();

                        clearButton.addEventListener('click', () => {
                            signaturePad.clear();
                            hiddenInput.value = '';
                            previewContainer.classList.add('hidden');
                        });

                        form.addEventListener('submit', function (e) {
                            
                            // Ambil data TTD sebelum submit
                            if (!signaturePad.isEmpty()) {
                                hiddenInput.value = signaturePad.toDataURL('image/png');
                            }

                            // Cek jika Tanda Tangan Kosong (Validasi Klien)
                            if (hiddenInput.value === '' || hiddenInput.value.length < 100) { 
                                alert("❌ Mohon tanda tangani formulir sebelum melanjutkan pengajuan.");
                                e.preventDefault();
                                return;
                            }
                            
                            // Note: Validasi input lain (required fields) akan ditangani oleh HTML5 atau Laravel
                        });

                        // --- LOGIKA DAFTAR BARANG DINAMIS ---
                        const barangContainer = document.getElementById('barang-container');
                        const tambahBarangBtn = document.getElementById('tambah-barang-btn');
                        const barangTemplate = document.getElementById('barang-template');

                        function tambahBarangRow() {
                            if (!barangTemplate) return; 
                            
                            const clone = barangTemplate.content.cloneNode(true);
                            barangContainer.appendChild(clone);
                            
                            const newRow = barangContainer.lastElementChild;
                            newRow.querySelector('.hapus-barang-btn').addEventListener('click', function() {
                                newRow.remove();
                                checkMinRows();
                            });
                            checkMinRows();
                        }

                        function checkMinRows() {
                            const rows = barangContainer.querySelectorAll('.barang-row');
                            if (rows.length === 0) {
                                tambahBarangRow();
                            }
                        }

                        tambahBarangBtn.addEventListener('click', tambahBarangRow);
                        // Pertahankan satu baris awal saat dimuat
                        checkMinRows(); 

                    });
                    </script>
                </div> 
            </div> 
        </div> 
    </div> 
</x-app-layout>