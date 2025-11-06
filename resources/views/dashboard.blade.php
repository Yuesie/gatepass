<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Gatepass') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @php
                    // Ambil peran dari data yang dilempar controller
                    $userPeran = $userPeran ?? Auth::user()->peran;
                    $userName = Auth::user()->name;
                @endphp

                <p class="text-gray-900 text-xl font-bold mb-6">
                    Selamat datang, {{ $userName }}! 👋
                </p>

                {{-- AREA UNTUK FLASH MESSAGE (Success/Error) --}}
                @if (session('success'))
                    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg shadow" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg shadow" role="alert">
                        {{ session('error') }}
                    </div>
                @endif


                {{-- ================================================= --}}
                {{-- BLOK 1 & 2: PEMBUAT GATEPASS / PEMOHON (Peran: pembuat_gatepass atau pemohon) --}}
                {{-- ================================================= --}}
                @if($userPeran === 'pemohon' || $userPeran === 'pembuat_gatepass')
                    <div class="mb-8 p-6 bg-blue-50 border-l-4 border-blue-500 rounded-lg shadow-md">
                        <h3 class="text-xl font-bold text-blue-700 mb-3">Gatepass Anda Sedang Diproses ⏳</h3>
                        
                        @if($pending_izins->isEmpty())
                            <p class="text-gray-600 italic">Tidak ada pengajuan Gatepass yang sedang menunggu persetujuan.</p>
                            <a href="{{ route('izin.buat') }}" class="mt-3 inline-block px-4 py-2 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition text-sm">
                                Buat Gatepass Baru
                            </a>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-blue-100">
                                        <tr>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nomor</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Perihal</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tgl Pengajuan</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($pending_izins as $izin)
                                        <tr>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-blue-600">
                                                {{ $izin->nomor_izin ?? 'Menunggu Nomor' }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">{{ Str::limit($izin->perihal, 30) }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($izin->tanggal)->format('d M Y') }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    {{ $izin->status }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('izin.detail', $izin->id) }}" class="text-blue-600 hover:text-blue-900">Lihat Detail</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    
                    {{-- BLOK 2: RIWAYAT GATEPASS ANDA (Disetujui Final) --}}
                    <div class="mb-8 p-6 bg-gray-50 border-l-4 border-gray-500 rounded-lg shadow-md">
                        <h3 class="text-xl font-bold text-gray-700 mb-3">Riwayat Gatepass Anda ({{ $riwayat_izin_count ?? 0 }} Total) ✅</h3>
                        
                        @if($latest_user_submissions->isEmpty())
                            <p class="text-gray-600 italic">Anda belum memiliki riwayat Gatepass yang sudah disetujui final.</p>
                            <a href="{{ route('izin.buat') }}" class="mt-3 inline-block px-4 py-2 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition text-sm">
                                Ajukan Gatepass Pertama Anda
                            </a>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-200">
                                        <tr>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nomor</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Perihal</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tgl Final</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($latest_user_submissions as $izin)
                                        <tr>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-green-600">
                                                {{ $izin->nomor_izin }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">{{ Str::limit($izin->perihal, 40) }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($izin->tgl_persetujuan_l3)->format('d M Y') }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium space-x-2">
                                                <a href="{{ route('izin.detail', $izin->id) }}" class="text-gray-600 hover:text-gray-900">Detail</a>
                                                <a href="{{ route('izin.cetak', $izin->id) }}" class="text-indigo-600 hover:text-indigo-900 ml-2">Cetak</a>
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
                {{-- BLOK 3: KONTEN KHUSUS ADMIN (Peran: admin) --}}
                {{-- ================================================= --}}
                @if($userPeran === 'admin')
                    <div class="mb-8 p-6 bg-green-50 border-l-4 border-green-500 rounded-lg shadow-md">
                        <h3 class="text-xl font-bold text-green-700 mb-3">Ringkasan Sistem Administrasi</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            {{-- Statistik 1: Total Gatepass --}}
                            <div class="p-4 border border-green-300 rounded-lg shadow-sm">
                                <p class="text-sm text-gray-600">Total Gatepass Dibuat</p>
                                <p class="text-3xl font-bold text-green-800 mt-1">
                                    {{ $total_izin_masuk ?? 'N/A' }} 
                                </p>
                            </div>
                            
                            {{-- Statistik 2: Total Pengguna --}}
                            <div class="p-4 border border-green-300 rounded-lg shadow-sm">
                                <p class="text-sm text-gray-600">Total Pengguna Aktif</p>
                                <p class="text-3xl font-bold text-green-800 mt-1">
                                    {{ $total_pengguna ?? 'N/A' }} 
                                </p>
                            </div>
                            
                        </div>
                    </div>
                @endif
                
                {{-- ================================================= --}}
                {{-- BLOK 4: TUGAS PERSETUJUAN (Peran: Approver L1, L2, L3, Teknik, HSSE) --}}
                {{-- ================================================= --}}
                @if(in_array($userPeran, ['atasan_pemohon', 'security', 'manager', 'teknik', 'hsse']))
                    <div class="mt-8 border-t pt-6">
                        <h3 class="text-lg font-semibold border-b pb-2 mb-4 text-gray-700">Tugas Persetujuan Anda</h3>
                        
                        <div class="bg-yellow-50 p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
                            <p class="text-sm text-gray-600">
                                Anda memiliki **{{ $approval_count ?? 0 }}** Gatepass yang membutuhkan tinjauan dan persetujuan.
                            </p>
                            <h4 class="text-2xl font-bold text-yellow-700 mb-4 mt-2">
                                Proses Persetujuan Sekarang
                            </h4>
                            <a href="{{ route('izin.persetujuan') }}" class="inline-block px-4 py-2 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition">
                                {{ ($approval_count ?? 0 > 0) ? 'Lihat '.($approval_count ?? 0).' Tugas' : 'Lihat Daftar Persetujuan' }}
                            </a>
                        </div>
                        
                        {{-- Tugas Persetujuan Terbaru (Hanya tampilkan 5 jika ada) --}}
                        @if(!$tugas_persetujuan_terbaru->isEmpty())
                            <h4 class="text-md font-semibold mt-6 mb-3 text-gray-700">5 Tugas Persetujuan Terbaru</h4>
                            <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diajukan Oleh</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perihal</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($tugas_persetujuan_terbaru as $izin)
                                        <tr>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-yellow-600">
                                                {{ $izin->nomor_izin ?? 'Menunggu Nomor' }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">{{ $izin->pemohon->name ?? 'N/A' }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ Str::limit($izin->perihal, 30) }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('izin.detail', $izin->id) }}" class="text-yellow-600 hover:text-yellow-900">Tinjau</a>
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