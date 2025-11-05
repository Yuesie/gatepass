// resources/views/izin/partials/approval-form.blade.php

@php
    $userId = Auth::id();
    $userPeran = Auth::user()->peran;
    $currentLevel = null;
    $isApprover = false;

    // 1. Tentukan Level Approver yang sedang bertugas
    if ($izin->status === 'Menunggu L1' && $izin->id_approver_l1 == $userId) {
        $currentLevel = 'L1';
        $isApprover = true;
    } elseif ($izin->status === 'Menunggu L2' && $izin->id_approver_l2 == $userId) {
        $currentLevel = 'L2';
        $isApprover = true;
    } elseif ($izin->status === 'Menunggu L3' && $izin->id_approver_l3 == $userId) {
        $currentLevel = 'L3';
        $isApprover = true;
    }
    
    // Khusus Manager/Admin/Lainnya yang memiliki hak override, bisa ditambahkan logic di sini
    // if (in_array($userPeran, ['admin', 'manager']) && $currentLevel === null) { ... }

@endphp

@if ($isApprover)
<div class="mt-10 border-t pt-6">
    <h3 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Aksi Persetujuan Anda (Level {{ substr($currentLevel, 1) }})</h3>
    <div class="bg-gray-50 p-6 rounded-lg shadow-inner">
        
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