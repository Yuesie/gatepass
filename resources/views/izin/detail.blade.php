<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Gatepass: ') }}<span class="text-indigo-600">{{ $izin->nomor_izin ?? 'Menunggu Nomor' }}</span>
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
        {{-- Tombol Kembali & Action Buttons (Cetak/Batal) --}}
        <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-2 sm:space-y-0">
            <a href="{{ route('izin.riwayat') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 transition duration-150 ease-in-out font-medium">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Riwayat
            </a>
            
            <div class="space-x-2 flex">
                @php
                    // Logika Otorisasi Tombol Atas
                    $canPrint = !in_array($izin->status, ['Ditolak', 'Dibatalkan', 'Dibatalkan Oleh Pemohon']);
                    $isRequester = (Auth::check() && $izin->id_pemohon === Auth::id());
                    $showPrintButtons = $canPrint && ($isRequester || $izin->status === 'Disetujui Final');
                    $showCancelButton = str_contains($izin->status, 'Menunggu') && $isRequester;
                @endphp

                {{-- Tombol Batalkan --}}
                @if($showCancelButton)
                    <form action="{{ route('izin.batal', $izin->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Anda yakin ingin membatalkan Gatepass ini? Aksi ini tidak dapat diulang.');">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Batalkan Pengajuan
                        </button>
                    </form>
                @endif
                
                {{-- Tombol Cetak/Print --}}
                @if($showPrintButtons)
                    <a href="{{ route('izin.cetak', $izin->id) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Cetak PDF
                    </a>
                    
                    <button type="button" onclick="window.open('{{ route('izin.cetak', $izin->id) }}', '_blank').print();" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m0 0v2a2 2 0 002 2h4a2 2 0 002-2v-2m-6-4H8"></path></svg>
                        Print Gatepass
                    </button>
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
            
            {{-- Bagian 1: Ringkasan Gatepass --}}
            <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Ringkasan Gatepass
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
                {{-- Card Nomor Gatepass --}}
                <div class="p-4 bg-indigo-50 border-l-4 border-indigo-500 rounded-lg shadow-md">
                    <p class="text-sm font-medium text-gray-500">Nomor Gatepass</p>
                    <p class="text-2xl font-extrabold text-indigo-700 mt-1 truncate">
                        {{ $izin->nomor_izin ?? 'Menunggu Nomor' }}
                    </p>
                </div>
                
                {{-- Card Status Gatepass (Lebih Menonjol) --}}
                @php
                    $statusClass = 'bg-yellow-100 border-yellow-500 text-yellow-800';
                    if($izin->status == 'Disetujui Final') {
                        $statusClass = 'bg-green-100 border-green-500 text-green-800';
                    } elseif(in_array($izin->status, ['Ditolak', 'Dibatalkan', 'Dibatalkan Oleh Pemohon'])) {
                        $statusClass = 'bg-red-100 border-red-500 text-red-800';
                    }
                @endphp
                <div class="p-4 border-l-4 rounded-lg shadow-md {{ $statusClass }}">
                    <p class="text-sm font-medium text-gray-500">Status Gatepass</p>
                    <p class="text-2xl font-bold mt-1 uppercase">
                        {{ $izin->status }}
                    </p>
                </div>
                
                {{-- Card Tanggal Pengajuan --}}
                <div class="p-4 bg-gray-50 border-l-4 border-gray-300 rounded-lg shadow-md">
                    <p class="text-sm font-medium text-gray-500">Tanggal Pengajuan</p>
                    <p class="text-lg font-semibold text-gray-900 mt-1">
                        {{ \Carbon\Carbon::parse($izin->tanggal)->format('d F Y') }}
                    </p>
                </div>

                {{-- Card Jenis Izin --}}
                <div class="p-4 bg-gray-50 border-l-4 border-gray-300 rounded-lg shadow-md">
                    <p class="text-sm font-medium text-gray-500">Jenis Izin</p>
                    <p class="text-lg font-semibold text-gray-900 mt-1">
                        {{ $izin->jenis_izin }}
                    </p>
                </div>
            </div>

            <hr class="border-gray-200">

            {{-- Bagian 2: Detail Utama dan Pengiriman --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6 text-sm mt-8">
                
                {{-- Kolom Kiri: Detail Permintaan --}}
                <div>
                    <h4 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2h2m-4 0a2 2 0 002-2h2a2 2 0 002 2M7 7h10"></path></svg>
                        Detail Permintaan
                    </h4>

                    {{-- Menggunakan Definition List (<dl>) untuk kerapian Label/Value --}}
                    <dl class="space-y-3">
                        <div class="grid grid-cols-3 gap-1">
                            <dt class="font-semibold text-gray-500">Perihal</dt>
                            <dd class="col-span-2 text-gray-900 font-medium">{{ $izin->perihal }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-1">
                            <dt class="font-semibold text-gray-500">Dasar Pekerjaan</dt>
                            <dd class="col-span-2 text-gray-900">{{ $izin->dasar_pekerjaan }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-1">
                            <dt class="font-semibold text-gray-500">Fungsi Pemohon</dt>
                            <dd class="col-span-2 text-gray-900">{{ $izin->fungsi_pemohon }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-1">
                            <dt class="font-semibold text-gray-500">Jabatan Pemohon</dt>
                            <dd class="col-span-2 text-gray-900">{{ $izin->jabatan_fungsi_pemohon }}</dd>
                        </div>
                        @if($izin->dokumen_pendukung)
                        <div class="grid grid-cols-3 gap-1">
                            <dt class="font-semibold text-gray-500">Dokumen Pendukung</dt>
                            <dd class="col-span-2 text-gray-900">
                                <a href="{{ Storage::url($izin->dokumen_pendukung) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                    [Lihat Dokumen]
                                </a>
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>

                {{-- Kolom Kanan: Detail Pengiriman --}}
                <div>
                    <h4 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        Informasi Pengiriman
                    </h4>
                    
                    <dl class="space-y-3">
                        <div class="grid grid-cols-3 gap-1">
                            <dt class="font-semibold text-gray-500">Nama Perusahaan</dt>
                            <dd class="col-span-2 text-gray-900 font-medium">{{ $izin->nama_perusahaan ?? '-' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-1">
                            <dt class="font-semibold text-gray-500">Pembawa Barang</dt>
                            <dd class="col-span-2 text-gray-900">{{ $izin->pembawa_barang }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-1">
                            <dt class="font-semibold text-gray-500">Nomor Kendaraan</dt>
                            <dd class="col-span-2 text-gray-900">{{ $izin->nomor_kendaraan ?? '-' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-1">
                            <dt class="font-semibold text-gray-500">Tujuan Pengiriman</dt>
                            <dd class="col-span-2 text-gray-900">{{ $izin->tujuan_pengiriman }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <hr class="border-gray-200 mt-8">

            {{-- Bagian 3: Detail Barang --}}
            <div class="mt-8 pt-2">
                <h3 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m-8-4v10l8 4m-8-10l8 4m8-4v10l-8 4m8-10h-2M4 7h2"></path></svg>
                    Detail Barang
                </h3>
                @if($izin->detailBarang->isNotEmpty())
                    <div class="overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-indigo-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">No.</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Nama Barang</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-indigo-700 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Satuan</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Keterangan Item</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-indigo-700 uppercase tracking-wider">Foto</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($izin->detailBarang as $index => $barang)
                                <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-indigo-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $barang->nama_item }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-center">{{ $barang->qty }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $barang->satuan }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">{{ $barang->keterangan_item ?? '-' }}</td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        @if($barang->foto_path)
                                            <a href="{{ Storage::url($barang->foto_path) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold hover:underline">
                                                [Lihat Foto]
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs italic">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <p class="text-gray-500 italic">Tidak ada detail barang terlampir.</p>
                    </div>
                @endif
            </div>

            <hr class="border-gray-200 mt-8">

            {{-- Bagian 4: Alur Persetujuan (Status Persetujuan) --}}
            <div class="mt-8 pt-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    Alur Persetujuan
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    @php
                        $approvers = [
                            ['level' => 'L1', 'title' => 'Atasan Pemohon', 'approver' => $izin->approverL1, 'nama' => $izin->nama_approver_l1, 'ttd_path' => $izin->ttd_approver_l1, 'tgl_persetujuan' => $izin->tgl_persetujuan_l1, 'rejected' => $izin->l1_rejected],
                            ['level' => 'L2', 'title' => 'Security', 'approver' => $izin->approverL2, 'nama' => $izin->nama_approver_l2, 'ttd_path' => $izin->ttd_approver_l2, 'tgl_persetujuan' => $izin->tgl_persetujuan_l2, 'rejected' => $izin->l2_rejected],
                            ['level' => 'L3', 'title' => 'Manajemen / Final', 'approver' => $izin->approverL3, 'nama' => $izin->nama_approver_l3, 'ttd_path' => $izin->ttd_approver_l3, 'tgl_persetujuan' => $izin->tgl_persetujuan_l3, 'rejected' => $izin->l3_rejected],
                        ];
                    @endphp

                    @foreach($approvers as $app)
                        @php
                            $cardClass = 'bg-yellow-50 border-yellow-300';
                            $statusText = 'Menunggu Persetujuan';
                            $statusColor = 'text-yellow-600';

                            if($app['rejected']) {
                                $cardClass = 'bg-red-50 border-red-300';
                                $statusText = 'Ditolak';
                                $statusColor = 'text-red-600';
                            } elseif ($app['tgl_persetujuan']) {
                                $cardClass = 'bg-green-50 border-green-300';
                                $statusText = 'Disetujui';
                                $statusColor = 'text-green-600';
                            }
                        @endphp
                        <div class="p-4 border-t-4 rounded-lg shadow-sm {{ $cardClass }}">
                            <p class="font-bold text-gray-700 mb-2">Level {{ $app['level'] }} - {{ $app['title'] }}</p>
                            <hr class="my-1 border-gray-300">
                            
                            <dl class="text-sm space-y-1">
                                <div class="grid grid-cols-3">
                                    <dt class="text-gray-500">Nama</dt>
                                    <dd class="col-span-2 text-gray-900 font-semibold">{{ $app['nama'] ?? 'N/A' }}</dd>
                                </div>
                                <div class="grid grid-cols-3">
                                    <dt class="text-gray-500">Digital User</dt>
                                    <dd class="col-span-2 text-gray-700 truncate">{{ $app['approver']->name ?? 'Belum Ditunjuk' }}</dd>
                                </div>
                                <div class="grid grid-cols-3">
                                    <dt class="text-gray-500">Status</dt>
                                    <dd class="col-span-2 font-bold {{ $statusColor }}">{{ $statusText }}</dd>
                                </div>
                                @if($app['tgl_persetujuan'])
                                <div class="grid grid-cols-3">
                                    <dt class="text-gray-500">Tanggal</dt>
                                    <dd class="col-span-2 text-gray-700">{{ \Carbon\Carbon::parse($app['tgl_persetujuan'])->format('d M Y H:i') }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <hr class="border-gray-200 mt-8">
            
            {{-- Bagian 5: Verifikasi Tanda Tangan Digital --}}
            <div class="mt-8 pt-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Verifikasi Tanda Tangan Digital
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                    {{-- TANDA TANGAN PEMOHON --}}
                    <div class="p-4 border rounded-lg bg-indigo-50 border-indigo-300">
                        <p class="text-sm font-bold text-indigo-800 mb-2">Pemohon</p>
                        @if($izin->ttd_pemohon_path)
                            <img src="{{ Storage::url($izin->ttd_pemohon_path) }}" alt="TTD Pemohon" class="w-full h-auto max-h-32 object-contain border border-indigo-200 bg-white">
                            <p class="text-xs text-indigo-600 mt-1">Disimpan saat pengajuan.</p>
                        @else
                            <p class="text-sm text-red-500 font-medium">Tanda tangan Pemohon tidak ditemukan.</p>
                        @endif
                    </div>
                    
                    {{-- TANDA TANGAN APPROVER L1 --}}
                    <div class="p-4 border rounded-lg {{ $izin->tgl_persetujuan_l1 ? 'bg-green-50 border-green-300' : ($izin->l1_rejected ? 'bg-red-50 border-red-300' : 'bg-gray-50 border-gray-300') }}">
                        <p class="text-sm font-bold text-gray-700 mb-2">L1 ({{ $izin->approverL1->name ?? 'N/A' }})</p>
                        @if($izin->ttd_approver_l1)
                            <img src="{{ asset($izin->ttd_approver_l1) }}" alt="TTD Approver L1" class="w-full h-auto max-h-32 object-contain border border-green-200 bg-white">
                            <p class="text-xs text-green-600 mt-1">Disetujui.</p>
                        @elseif($izin->l1_rejected)
                            <p class="text-sm text-red-700 font-bold">DITOLAK</p>
                        @else
                            <p class="text-sm text-gray-600">Menunggu TTD.</p>
                        @endif
                    </div>

                    {{-- TANDA TANGAN APPROVER L2 --}}
                    <div class="p-4 border rounded-lg {{ $izin->tgl_persetujuan_l2 ? 'bg-green-50 border-green-300' : ($izin->l2_rejected ? 'bg-red-50 border-red-300' : 'bg-gray-50 border-gray-300') }}">
                        <p class="text-sm font-bold text-gray-700 mb-2">L2 ({{ $izin->approverL2->name ?? 'N/A' }})</p>
                        @if($izin->ttd_approver_l2)
                            <img src="{{ asset($izin->ttd_approver_l2) }}" alt="TTD Approver L2" class="w-full h-auto max-h-32 object-contain border border-green-200 bg-white">
                            <p class="text-xs text-green-600 mt-1">Disetujui.</p>
                        @elseif($izin->l2_rejected)
                            <p class="text-sm text-red-700 font-bold">DITOLAK</p>
                        @else
                            <p class="text-sm text-gray-600">Menunggu TTD.</p>
                        @endif
                    </div>

                    {{-- TANDA TANGAN APPROVER L3 --}}
                    <div class="p-4 border rounded-lg {{ $izin->tgl_persetujuan_l3 ? 'bg-green-50 border-green-300' : ($izin->l3_rejected ? 'bg-red-50 border-red-300' : 'bg-gray-50 border-gray-300') }}">
                        <p class="text-sm font-bold text-gray-700 mb-2">L3 ({{ $izin->approverL3->name ?? 'N/A' }})</p>
                        @if($izin->ttd_approver_l3)
                            <img src="{{ asset($izin->ttd_approver_l3) }}" alt="TTD Approver L3" class="w-full h-auto max-h-32 object-contain border border-green-200 bg-white">
                            <p class="text-xs text-green-600 mt-1">Disetujui Final.</p>
                        @elseif($izin->l3_rejected)
                            <p class="text-sm text-red-700 font-bold">DITOLAK</p>
                        @else
                            <p class="text-sm text-gray-600">Menunggu TTD.</p>
                        @endif
                    </div>
                </div>
            </div>
            
            {{-- START: BAGIAN TOMBOL PERSETUJUAN/PENOLAKAN (LOGIKA DIPERBAIKI) --}}
            @php
                $currentUserId = Auth::id();
                $currentLevel = null;
                $isApprover = false;
                $namaApprover = null;
                $jabatanApprover = null;

                // --- LOGIKA OTORISASI YANG MENCARI ID APPROVER ---
                
                // L1: Jika ID approver L1 adalah user saat ini, DAN persetujuan L1 belum ada/ditolak
                if (isset($izin->id_approver_l1) && $izin->id_approver_l1 == $currentUserId && is_null($izin->tgl_persetujuan_l1) && !$izin->l1_rejected) {
                    $currentLevel = 'L1';
                    $isApprover = true;
                    $namaApprover = $izin->nama_approver_l1;
                    $jabatanApprover = $izin->jabatan_approver_l1;
                } 
                // L2: Jika ID approver L2 adalah user saat ini, DAN persetujuan L1 SUDAH ada, DAN L2 belum disetujui/ditolak
                elseif (isset($izin->id_approver_l2) && $izin->id_approver_l2 == $currentUserId && !is_null($izin->tgl_persetujuan_l1) && is_null($izin->tgl_persetujuan_l2) && !$izin->l2_rejected) {
                    $currentLevel = 'L2';
                    $isApprover = true;
                    $namaApprover = $izin->nama_approver_l2;
                    $jabatanApprover = $izin->jabatan_approver_l2;
                } 
                // L3: Jika ID approver L3 adalah user saat ini, DAN persetujuan L2 SUDAH ada, DAN L3 belum disetujui/ditolak
                elseif (isset($izin->id_approver_l3) && $izin->id_approver_l3 == $currentUserId && !is_null($izin->tgl_persetujuan_l2) && is_null($izin->tgl_persetujuan_l3) && !$izin->l3_rejected) {
                    $currentLevel = 'L3';
                    $isApprover = true;
                    $namaApprover = $izin->nama_approver_l3;
                    $jabatanApprover = $izin->jabatan_approver_l3;
                }
                
                // Tambahkan pengecekan status keseluruhan untuk memastikan Gatepass masih aktif
                if ($isApprover && !str_contains($izin->status, 'Menunggu')) {
                    // Jika isApprover true tapi status sudah final, override isApprover menjadi false.
                    $isApprover = false;
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
                    
                    {{-- Form Aksi Persetujuan --}}
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
            {{-- END: TOMBOL PERSETUJUAN/PENOLAKAN --}}
            
        </div>
    </div>
</x-app-layout>