@php
    $userId = Auth::id();
    $currentLevel = null;
    $isApprover = false;

    // --- Data Approver yang Diinput Manual ---
    $namaApprover = null;
    $jabatanApprover = null;

    // Logika Persetujuan Sekuensial (Didasarkan pada status dan ID approver)
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
@endphp

@if ($isApprover)
<div id="approval-action" class="mt-10 pt-6 border-t-4 border-indigo-500">
    
    <h3 class="text-3xl font-extrabold text-gray-800 mb-6 flex items-center">
        <svg class="w-7 h-7 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        Aksi Persetujuan Anda
    </h3>

    <div class="bg-indigo-50 p-6 rounded-xl shadow-lg border border-indigo-200">
        
        <div class="mb-6 p-4 border-l-4 border-indigo-700 bg-white rounded-md shadow-sm">
            <p class="text-sm font-semibold text-indigo-600">Anda ditugaskan sebagai Approver Level **{{ substr($currentLevel, 1) }}**:</p>
            <p class="text-xl font-bold text-indigo-900 mt-1">Nama yang Dituju: {{ $namaApprover ?? 'N/A' }}</p>
            <p class="text-sm text-indigo-700">Jabatan Formal: {{ $jabatanApprover ?? 'N/A' }}</p>
        </div>
        
        <form action="{{ route('izin.persetujuan.proses', $izin->id) }}" method="POST">
            @csrf
            <input type="hidden" name="level" value="{{ $currentLevel }}">
            
            <div class="mb-6">
                <label for="catatan_penolakan" class="block text-sm font-medium text-gray-700">Catatan/Komentar (Wajib diisi jika **Menolak**)</label>
                <textarea name="catatan_penolakan" id="catatan_penolakan" rows="3" 
                          placeholder="Masukkan catatan Anda (Misalnya alasan penolakan atau instruksi persetujuan)."
                          class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 
                          @error('catatan_penolakan') border-red-500 ring-red-500 @enderror"></textarea>
                @error('catatan_penolakan')
                    <p class="text-sm text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                
                {{-- Tombol Tolak --}}
                <button type="submit" name="action" value="reject"
                        class="flex-1 px-6 py-3 bg-red-600 text-white font-bold uppercase tracking-wider rounded-lg shadow-lg hover:bg-red-700 transition transform hover:scale-105"
                        onclick="return confirm('❗ PERHATIAN: Apakah Anda yakin ingin MENOLAK Gatepass ini? Aksi ini akan menghentikan alur persetujuan.');">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        TOLAK GATEPASS
                    </span>
                </button>

                {{-- Tombol Setuju --}}
                <button type="submit" name="action" value="approve"
                        class="flex-1 px-6 py-3 bg-green-600 text-white font-bold uppercase tracking-wider rounded-lg shadow-lg hover:bg-green-700 transition transform hover:scale-105">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        SETUJUI GATEPASS
                    </span>
                </button>
            </div>
            
        </form>
        
    </div>
</div>
@endif