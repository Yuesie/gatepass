<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Gatepass: ') }}<span class="text-indigo-600">{{ $izin->nomor_izin ?? 'Menunggu Nomor' }}</span>
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
        {{-- Tombol Kembali & Action Buttons (Cetak/Batal) --}}
        <div class="mb-4 flex justify-between items-center">
            <a href="{{ route('izin.riwayat') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 transition duration-150 ease-in-out">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Riwayat
            </a>
            
            <div class="space-x-2">
                {{-- Tombol Batalkan (Koreksi status dan kepemilikan) --}}
                @if(str_contains($izin->status, 'Menunggu') && ($izin->id_pemohon === Auth::id() || $izin->id_pembuat === Auth::id()))
                    <form action="{{ route('izin.batal', $izin->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Anda yakin ingin membatalkan Gatepass ini? Aksi ini tidak dapat diulang.');">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Batalkan Pengajuan
                        </button>
                    </form>
                @endif
                
                @if($izin->status == 'Disetujui Final')
                    <a href="{{ route('izin.cetak', $izin->id) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m0 0v2a2 2 0 002 2h4a2 2 0 002-2v-2m-6-4H8"></path></svg>
                        Cetak Gatepass
                    </a>
                @endif
            </div>
        </div>

        {{-- Flash Message --}}
        @if (session('error'))
            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg shadow" role="alert">
                {{ session('error') }}
            </div>
        @endif
        
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            
            <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Informasi Gatepass</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4 text-sm">
                
                {{-- Nomor Gatepass --}}
                <div>
                    <p class="font-semibold text-gray-500">Nomor Gatepass</p>
                    <p class="text-xl font-extrabold text-indigo-700 mt-1">
                        {{ $izin->nomor_izin ?? 'Menunggu Nomor / Disetujui Final' }}
                    </p>
                </div>
                
                <div>
                    <p class="font-semibold text-gray-500">Status Gatepass</p>
                    <span class="text-xl font-bold mt-1 
                        @if($izin->status == 'Disetujui Final') text-green-600 
                        @elseif(str_contains($izin->status, 'Menunggu')) text-yellow-600 
                        @else text-red-600 
                        @endif">
                        {{ $izin->status }}
                    </span>
                </div>
                
                {{-- Data Utama --}}
                <div>
                    <p class="font-semibold text-gray-500">Jenis Izin</p>
                    <p class="text-gray-900">{{ $izin->jenis_izin }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500">Tanggal Pengajuan</p>
                    <p class="text-gray-900">{{ \Carbon\Carbon::parse($izin->tanggal)->format('d F Y') }}</p>
                </div>
                
                <div>
                    <p class="font-semibold text-gray-500">Perihal</p>
                    <p class="text-gray-900">{{ $izin->perihal }}</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500">Dasar Pekerjaan</p>
                    <p class="text-gray-900">{{ $izin->dasar_pekerjaan }}</p>
                </div>

                <div class="col-span-1 md:col-span-2 mt-4 border-t pt-4">
                    <h4 class="font-bold text-gray-700 mb-2">Informasi Pihak & Pengiriman</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="font-semibold text-gray-500">Fungsi Pemohon</p>
                            <p class="text-gray-900">{{ $izin->fungsi_pemohon }}</p>
                        </div>
                        
                        {{-- ✅ NEW: Jabatan Fungsi Pemohon --}}
                        <div>
                            <p class="font-semibold text-gray-500">Jabatan Fungsi Pemohon</p>
                            <p class="text-gray-900">{{ $izin->jabatan_fungsi_pemohon }}</p>
                        </div>
                        
                        <div>
                            <p class="font-semibold text-gray-500">Nama Perusahaan (Vendor/Pihak Luar)</p>
                            <p class="text-gray-900">{{ $izin->nama_perusahaan ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-500">Pembawa Barang</p>
                            <p class="text-gray-900">{{ $izin->pembawa_barang }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-500">Nomor Kendaraan</p>
                            <p class="text-gray-900">{{ $izin->nomor_kendaraan ?? '-' }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="font-semibold text-gray-500">Tujuan Pengiriman</p>
                            <p class="text-gray-900">{{ $izin->tujuan_pengiriman }}</p>
                        </div>
                    </div>
                </div>
                
                @if($izin->dokumen_pendukung)
                <div class="col-span-1 md:col-span-2 mt-4 border-t pt-4">
                    <h4 class="font-bold text-gray-700 mb-2">Dokumen Pendukung</h4>
                    <a href="{{ Storage::url($izin->dokumen_pendukung) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold">
                        Lihat Dokumen
                    </a>
                </div>
                @endif

            </div>

            {{-- Detail Barang --}}
            <div class="mt-8 border-t pt-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Detail Barang</h3>
                @if($izin->detailBarang->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                                    {{-- KOREKSI: Header Volume menjadi Qty --}}
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan Item</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($izin->detailBarang as $index => $barang)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                    {{-- KOREKSI: nama_barang -> nama_item --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $barang->nama_item }}</td>
                                    {{-- KOREKSI: volume -> qty --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $barang->qty }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $barang->satuan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $barang->keterangan_item ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 italic">Tidak ada detail barang terlampir.</p>
                @endif
            </div>

            {{-- Alur Persetujuan --}}
            <div class="mt-8 border-t pt-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Alur Persetujuan</h3>

               <div class="grid grid-cols-3 gap-6 mt-8">
    
    <div class="p-4 border rounded-lg bg-yellow-100 border-yellow-500">
        <p class="font-semibold text-sm">Persetujuan Level 1 (Atasan Pemohon)</p>
        <hr class="my-1 border-gray-300">
        
        <p class="text-sm text-gray-700">Nama: <strong>{{ $izin->nama_approver_l1 ?? 'N/A' }}</strong></p> 
        
        <p class="mt-2 text-xs text-gray-600">
            Approver Digital: {{ $izin->approverL1->name ?? 'Belum Ditunjuk' }}
        </p>
    </div>

    <div class="p-4 border rounded-lg bg-yellow-100 border-yellow-500">
        <p class="font-semibold text-sm">Persetujuan Level 2 (Security)</p>
        <hr class="my-1 border-gray-300">
        
        <p class="text-sm text-gray-700">Nama: <strong>{{ $izin->nama_approver_l2 ?? 'N/A' }}</strong></p> 

        <p class="mt-2 text-xs text-gray-600">
            Approver Digital: {{ $izin->approverL2->name ?? 'Belum Ditunjuk' }}
        </p>
    </div>

    <div class="p-4 border rounded-lg bg-yellow-100 border-yellow-500">
        <p class="font-semibold text-sm">Persetujuan Level 3 (Manajemen / Final)</p>
        <hr class="my-1 border-gray-300">
        
        <p class="text-sm text-gray-700">Nama: <strong>{{ $izin->nama_approver_l3 ?? 'N/A' }}</strong></p> 
        
        
        <p class="mt-2 text-xs text-gray-600">
            Approver Digital: {{ $izin->approverL3->name ?? 'Belum Ditunjuk' }}
        </p>
    </div>
</div>
            
            {{-- Verifikasi Tanda Tangan --}}
            <div class="mt-8 border-t pt-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Verifikasi Tanda Tangan Digital</h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                    {{-- TANDA TANGAN PEMOHON (Digital Basah) --}}
                    <div class="p-4 border rounded-lg bg-indigo-50 border-indigo-300">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Tanda Tangan Pemohon</p>
                        {{-- KOREKSI TTD PATH: Menggunakan ttd_pemohon_path --}}
                        @if($izin->ttd_pemohon_path)
                            <img src="{{ asset('storage/' . $izin->ttd_pemohon_path) }}" alt="TTD Pemohon" class="w-full h-auto max-h-32 object-contain border border-indigo-200 bg-white">
                            <p class="text-xs text-indigo-600 mt-1">Disimpan saat pengajuan.</p>
                        @else
                            <p class="text-sm text-red-500">Tanda tangan Pemohon tidak ditemukan.</p>
                        @endif
                    </div>

                    {{-- TANDA TANGAN APPROVER L1 (Template PNG) --}}
                    <div class="p-4 border rounded-lg {{ $izin->tgl_persetujuan_l1 ? 'bg-green-50' : 'bg-red-50' }}">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Tanda Tangan L1 ({{ $izin->approverL1->name ?? 'N/A' }})</p>
                        @if($izin->ttd_approver_l1)
                            <img src="{{ asset($izin->ttd_approver_l1) }}" alt="TTD Approver L1" class="w-full h-auto max-h-32 object-contain border border-green-200 bg-white">
                            <p class="text-xs text-green-600 mt-1">Telah disetujui.</p>
                        @elseif($izin->l1_rejected)
                            <p class="text-sm text-red-700 font-bold">DITOLAK</p>
                        @else
                            <p class="text-sm text-yellow-600">Menunggu TTD.</p>
                        @endif
                    </div>

                    {{-- TANDA TANGAN APPROVER L2 (Template PNG) --}}
                    <div class="p-4 border rounded-lg {{ $izin->tgl_persetujuan_l2 ? 'bg-green-50' : 'bg-red-50' }}">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Tanda Tangan L2 ({{ $izin->approverL2->name ?? 'N/A' }})</p>
                        @if($izin->ttd_approver_l2)
                            <img src="{{ asset($izin->ttd_approver_l2) }}" alt="TTD Approver L2" class="w-full h-auto max-h-32 object-contain border border-green-200 bg-white">
                            <p class="text-xs text-green-600 mt-1">Telah disetujui.</p>
                        @elseif($izin->l2_rejected)
                            <p class="text-sm text-red-700 font-bold">DITOLAK</p>
                        @else
                            <p class="text-sm text-yellow-600">Menunggu TTD.</p>
                        @endif
                    </div>

                    {{-- TANDA TANGAN APPROVER L3 (Template PNG) --}}
                    <div class="p-4 border rounded-lg {{ $izin->tgl_persetujuan_l3 ? 'bg-green-50' : 'bg-red-50' }}">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Tanda Tangan L3 ({{ $izin->approverL3->name ?? 'N/A' }})</p>
                        @if($izin->ttd_approver_l3)
                            <img src="{{ asset($izin->ttd_approver_l3) }}" alt="TTD Approver L3" class="w-full h-auto max-h-32 object-contain border border-green-200 bg-white">
                            <p class="text-xs text-green-600 mt-1">Disetujui Final.</p>
                        @elseif($izin->l3_rejected)
                            <p class="text-sm text-red-700 font-bold">DITOLAK</p>
                        @else
                            <p class="text-sm text-yellow-600">Menunggu TTD.</p>
                        @endif
                    </div>
                </div>
            </div>
            
            {{-- Form Persetujuan/Penolakan (Hanya untuk Approver yang Bertugas) --}}
            @include('izin.partials.approval-form') 
            
        </div>
    </div>
</x-app-layout>