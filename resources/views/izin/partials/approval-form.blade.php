@php
    $userId = Auth::id();
    $userPeran = Auth::user()->peran;
    $currentLevel = null;
    $isApprover = false;

    // --- Data Approver yang Diinput Manual ---
    $namaApprover = null;
    $jabatanApprover = null;

    // 1. Tentukan Level Approver yang sedang bertugas
    // Catatan: Logika ini masih SEKUENSIAL (Menunggu L1, L2, L3).
    if ($izin->status === 'Menunggu L1' && $izin->id_approver_l1 == $userId) {
        $currentLevel = 'L1';
        $isApprover = true;
        $namaApprover = $izin->nama_approver_l1;
        $jabatanApprover = $izin->jabatan_approver_l1;
    } elseif ($izin->status === 'Menunggu L2' && $izin->id_approver_l2 == $userId) {
        $currentLevel = 'L2';
        $isApprover = true;
        $namaApprover = $izin->nama_approver_l2;
        $jabatanApprover = $izin->jabatan_approver_l2;
    } elseif ($izin->status === 'Menunggu L3' && $izin->id_approver_l3 == $userId) {
        $currentLevel = 'L3';
        $isApprover = true;
        $namaApprover = $izin->nama_approver_l3;
        $jabatanApprover = $izin->jabatan_approver_l3;
    }
    
    // Perhatikan: Jika Anda menggunakan alur PARALEL, logika status di atas harus diubah
    // menjadi: $izin->status === 'Menunggu Persetujuan' AND kolom_persetujuan_L_X_NULL

@endphp

@if ($isApprover)
<div class="mt-10 border-t pt-6">
    <h3 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Aksi Persetujuan Anda (Level {{ substr($currentLevel, 1) }})</h3>
    <div class="bg-gray-50 p-6 rounded-lg shadow-inner">
        
        <div class="mb-5 p-3 border-l-4 border-indigo-500 bg-indigo-100">
            <p class="text-sm font-semibold text-indigo-800">Detail Penugasan Anda (Level {{ $currentLevel }}):</p>
            <p class="text-xl font-bold text-indigo-900 mt-1">Nama yang Dituju: {{ $namaApprover ?? 'N/A' }}</p>
            <p class="text-sm text-indigo-700">Jabatan Formal: {{ $jabatanApprover ?? 'N/A' }}</p>
        </div>
        <form action="{{ route('izin.persetujuan.proses', $izin->id) }}" method="POST">
            @csrf
            <input type="hidden" name="level" value="{{ $currentLevel }}">
            
            <div class="mb-4">
                <label for="catatan_penolakan" class="block text-sm font-medium text-gray-700">Catatan Penolakan (Wajib diisi jika Tolak)</label>
                <textarea name="catatan_penolakan" id="catatan_penolakan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('catatan_penolakan') border-red-500 @enderror"></textarea>
                @error('catatan_penolakan')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex space-x-4">
                {{-- Tombol Setuju --}}
                <button type="submit" name="action" value="approve"
                        class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 transition">
                    ✅ Setuju Gatepass
                </button>
                
                {{-- Tombol Tolak --}}
                <button type="submit" name="action" value="reject"
                        class="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg shadow-md hover:bg-red-700 transition"
                        onclick="return confirm('Apakah Anda yakin ingin MENOLAK Gatepass ini? Penolakan akan menghentikan proses persetujuan.');">
                    ❌ Tolak Gatepass
                </button>
            </div>
            
        </form>
        
    </div>
</div>
@endif