<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Gatepass') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Menggunakan shadow-xl dan rounded-xl untuk tampilan lebih modern --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl p-8"> 
                
                @php
                    // Pastikan variabel didefinisikan untuk menghindari error
                    $userPeran = $userPeran ?? Auth::user()->peran;
                    $userName = Auth::user()->name;
                    $approverRoles = ['atasan_pemohon', 'security', 'manager', 'teknik', 'hsse'];
                    
                    // Asumsi untuk Admin, jika variabel tidak dilempar dari controller:
                    $total_izin_masuk = $total_izin_masuk ?? 0;
                    $total_pengguna = $total_pengguna ?? 0;
                    $totalFinal = $totalFinal ?? 0;
                    $totalRejected = $totalRejected ?? 0;
                @endphp

                <h1 class="text-gray-900 text-2xl font-extrabold mb-8 border-b pb-3 text-indigo-700">
                    Selamat datang, {{ $userName }}! 🥳
                </h1>

                {{-- AREA UNTUK FLASH MESSAGE (Success/Error) --}}
                @if (session('success'))
                    <div class="p-4 mb-6 text-base font-medium text-green-700 bg-green-100 rounded-lg shadow" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="p-4 mb-6 text-base font-medium text-red-700 bg-red-100 rounded-lg shadow" role="alert">
                        {{ session('error') }}
                    </div>
                @endif


                {{-- ================================================= --}}
                {{-- BLOK 1 & 2: PEMBUAT GATEPASS / PEMOHON --}}
                {{-- ================================================= --}}
                @if($userPeran === 'pemohon' || $userPeran === 'pembuat_gatepass')
                    
                    {{-- BLOK 1: GATEPASS DIPROSES --}}
                    <div class="mb-8 p-6 bg-blue-50 border-l-4 border-blue-600 rounded-lg shadow-lg">
                        <h3 class="text-xl font-bold text-blue-700 mb-4">Gatepass Anda Sedang Diproses ⏳</h3>
                        
                        @if(($pending_izins ?? collect())->isEmpty())
                            <p class="text-gray-600 italic mb-4">Tidak ada pengajuan Gatepass yang sedang menunggu persetujuan.</p>
                            <a href="{{ route('izin.buat') }}" class="inline-block px-5 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition shadow">
                                + Buat Gatepass Baru
                            </a>
                        @else
                            <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-blue-100">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Nomor</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Perihal</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Tgl Pengajuan</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @foreach($pending_izins as $izin)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-blue-600">
                                                {{ $izin->nomor_izin ?? 'Menunggu Nomor' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ Str::limit($izin->perihal, 30) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($izin->tanggal)->format('d M Y') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    {{ $izin->status }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium space-x-2">
                                                <a href="{{ route('izin.detail', $izin->id) }}" class="text-blue-600 hover:text-blue-800">Lihat Detail</a>
                                                {{-- Tombol Cetak (untuk yang pending) dihilangkan agar tidak membingungkan --}}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    
                    {{-- BLOK 2: RIWAYAT GATEPASS ANDA (Disetujui Final) --}}
                    <div class="mb-8 p-6 bg-gray-50 border-l-4 border-green-600 rounded-lg shadow-lg">
                        <h3 class="text-xl font-bold text-gray-700 mb-4">Riwayat Gatepass Anda ({{ $riwayat_izin_count ?? 0 }} Total) ✅</h3>
                        
                        @if(($latest_user_submissions ?? collect())->isEmpty())
                            <p class="text-gray-600 italic">Anda belum memiliki riwayat Gatepass yang sudah disetujui final.</p>
                            <a href="{{ route('izin.buat') }}" class="mt-3 inline-block px-5 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition shadow">
                                Ajukan Gatepass Pertama Anda
                            </a>
                        @else
                            <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-200">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Nomor</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Perihal</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Tgl Final</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @foreach($latest_user_submissions as $izin)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-green-600">
                                                {{ $izin->nomor_izin }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ Str::limit($izin->perihal, 40) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                {{ $izin->tgl_persetujuan_l3 ? \Carbon\Carbon::parse($izin->tgl_persetujuan_l3)->format('d M Y') : 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium space-x-2">
                                                <a href="{{ route('izin.detail', $izin->id) }}" class="text-gray-600 hover:text-gray-800 border-r pr-2">Detail</a>
                                                <a href="{{ route('izin.cetak', $izin->id) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold pl-2">Cetak PDF</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Tombol Lihat Semua Riwayat --}}
                            <div class="mt-4 text-right">
                                <a href="{{ route('izin.riwayat') }}" class="text-sm text-gray-600 hover:text-gray-900 font-semibold">Lihat Semua Riwayat Gatepass ({{ $riwayat_izin_count ?? 0 }}) →</a>
                            </div>
                        @endif
                    </div>
                @endif


                {{-- ================================================= --}}
                {{-- BLOK 3: KONTEN KHUSUS ADMIN --}}
                {{-- ================================================= --}}
                @if($userPeran === 'admin')
                    <div class="mb-8 p-6 bg-gray-50 border-l-4 border-indigo-600 rounded-lg shadow-lg">
                        <h3 class="text-xl font-bold text-indigo-700 mb-4">Ringkasan Sistem Administrasi 📊</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            
                            {{-- Statistik 1: Total Gatepass --}}
                            <div class="p-5 bg-indigo-100 border border-indigo-300 rounded-lg shadow-sm">
                                <p class="text-sm text-indigo-600 font-medium">Total Gatepass Dibuat</p>
                                <p class="text-3xl font-extrabold text-indigo-800 mt-1">
                                    {{ $total_izin_masuk }} 
                                </p>
                            </div>
                            
                            {{-- Statistik 2: Total Disetujui Final --}}
                            <div class="p-5 bg-green-100 border border-green-300 rounded-lg shadow-sm">
                                <p class="text-sm text-green-600 font-medium">Gatepass Disetujui</p>
                                <p class="text-3xl font-extrabold text-green-800 mt-1">
                                    {{ $totalFinal }} 
                                </p>
                            </div>

                            {{-- Statistik 3: Total Ditolak --}}
                            <div class="p-5 bg-red-100 border border-red-300 rounded-lg shadow-sm">
                                <p class="text-sm text-red-600 font-medium">Gatepass Ditolak</p>
                                <p class="text-3xl font-extrabold text-red-800 mt-1">
                                    {{ $totalRejected }} 
                                </p>
                            </div>
                            
                            {{-- Statistik 4: Total Pengguna --}}
                            <div class="p-5 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">
                                <p class="text-sm text-gray-600 font-medium">Total Pengguna Aktif</p>
                                <p class="text-3xl font-extrabold text-gray-800 mt-1">
                                    {{ $total_pengguna }} 
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-8 text-right">
                            <a href="{{ route('admin.laporan') }}" class="inline-block px-5 py-2 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition shadow">
                                Lihat Laporan Detail →
                            </a>
                        </div>
                    </div>
                @endif
                
                {{-- ================================================= --}}
                {{-- BLOK 4: TUGAS PERSETUJUAN (Approver) --}}
                {{-- ================================================= --}}
                @if(in_array($userPeran, $approverRoles))
                    <div class="mt-8">
                        <h3 class="text-xl font-bold border-b pb-2 mb-4 text-gray-700">Tugas Persetujuan Anda ✍️</h3>
                        
                        <div class="bg-yellow-50 p-6 rounded-lg shadow-lg border-l-4 border-yellow-600 mb-8">
                            <p class="text-base text-gray-600">
                                Anda memiliki **{{ $approval_count ?? 0 }}** Gatepass yang membutuhkan tinjauan dan persetujuan.
                            </p>
                            <h4 class="text-3xl font-extrabold text-yellow-700 mt-2 mb-4">
                                {{ ($approval_count ?? 0 > 0) ? ($approval_count ?? 0) . ' Tugas Menunggu' : 'Semua Tugas Selesai!' }}
                            </h4>
                            <a href="{{ route('izin.persetujuan') }}" class="inline-block px-5 py-2 bg-yellow-600 text-white font-semibold rounded-xl hover:bg-yellow-700 transition shadow">
                                Proses Persetujuan Sekarang
                            </a>
                        </div>
                        
                        {{-- Tugas Persetujuan Terbaru (Hanya tampilkan 5 jika ada) --}}
                        @if(!($tugas_persetujuan_terbaru ?? collect())->isEmpty())
                            <h4 class="text-md font-bold mt-6 mb-3 text-gray-700">5 Tugas Persetujuan Terbaru</h4>
                            <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nomor</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Diajukan Oleh</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Perihal</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @foreach($tugas_persetujuan_terbaru as $izin)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-yellow-600">
                                                {{ $izin->nomor_izin ?? 'Menunggu Nomor' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $izin->pemohon->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ Str::limit($izin->perihal, 30) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                                 <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    {{ $izin->status }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('izin.detail', $izin->id) }}" class="text-yellow-600 hover:text-yellow-800">Tinjau Detail</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif
                
            </div>
        </div>
    </div>
</x-app-layout>