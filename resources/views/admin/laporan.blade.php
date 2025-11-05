<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Global Gatepass') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Pesan Sukses/Error (Opsional) --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold mb-6 text-indigo-700">Ringkasan Statistik Sistem</h3>
                    
                    {{-- Blok Kartu Statistik Ringkasan --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        
                        {{-- Kartu 1: Total Gatepass --}}
                        <div class="bg-indigo-50 p-6 rounded-lg shadow-lg border-b-4 border-indigo-500 transition duration-300 hover:shadow-xl">
                            <p class="text-sm font-medium text-indigo-700">Total Gatepass Dibuat</p>
                            <p class="text-4xl font-extrabold text-indigo-800 mt-1">{{ $totalIzin ?? 0 }}</p>
                        </div>

                        {{-- Kartu 2: Disetujui Final --}}
                        <div class="bg-green-50 p-6 rounded-lg shadow-lg border-b-4 border-green-500 transition duration-300 hover:shadow-xl">
                            <p class="text-sm font-medium text-green-700">Disetujui Final</p>
                            <p class="text-4xl font-extrabold text-green-800 mt-1">{{ $totalFinal ?? 0 }}</p>
                        </div>

                        {{-- Kartu 3: Ditolak --}}
                        <div class="bg-red-50 p-6 rounded-lg shadow-lg border-b-4 border-red-500 transition duration-300 hover:shadow-xl">
                            <p class="text-sm font-medium text-red-700">Total Ditolak</p>
                            <p class="text-4xl font-extrabold text-red-800 mt-1">{{ $totalRejected ?? 0 }}</p>
                        </div>
                        
                        {{-- Kartu 4: Total Pengguna --}}
                        <div class="bg-gray-50 p-6 rounded-lg shadow-lg border-b-4 border-gray-500 transition duration-300 hover:shadow-xl">
                            <p class="text-sm font-medium text-gray-700">Total Pengguna Aktif</p>
                            <p class="text-4xl font-extrabold text-gray-800 mt-1">{{ $totalUsers ?? 0 }}</p>
                        </div>
                    </div>
                    
                    
                    {{-- ================================================= --}}
                    {{-- BLOK FILTER LAPORAN --}}
                    {{-- ================================================= --}}
                    <h3 class="text-xl font-bold mt-10 mb-4 border-b pb-2">Filter Laporan Detail</h3>
                    
                    <form method="GET" action="{{ route('admin.laporan') }}" class="p-4 bg-gray-50 rounded-lg border mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                            
                            {{-- Filter 1: Tanggal Awal --}}
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Dari Tanggal</label>
                                <input type="date" name="start_date" id="start_date" 
                                    value="{{ request('start_date') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>

                            {{-- Filter 2: Tanggal Akhir --}}
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                                <input type="date" name="end_date" id="end_date" 
                                    value="{{ request('end_date') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>

                            {{-- Filter 3: Jenis Izin --}}
                            <div>
                                <label for="jenis_izin" class="block text-sm font-medium text-gray-700">Jenis Izin</label>
                                <select name="jenis_izin" id="jenis_izin" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">-- Semua --</option>
                                    <option value="masuk" {{ request('jenis_izin') == 'masuk' ? 'selected' : '' }}>Masuk</option>
                                    <option value="keluar" {{ request('jenis_izin') == 'keluar' ? 'selected' : '' }}>Keluar</option>
                                </select>
                            </div>
                            
                            {{-- Filter 4: Status --}}
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">-- Semua --</option>
                                    <option value="Menunggu" {{ request('status') == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                                    <option value="Disetujui Final" {{ request('status') == 'Disetujui Final' ? 'selected' : '' }}>Disetujui Final</option>
                                    <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                                    <option value="Dibatalkan" {{ request('status') == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                </select>
                            </div>

                            {{-- Tombol Aksi --}}
                            <div class="flex space-x-2">
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition shadow">
                                    Filter
                                </button>
                                <a href="{{ route('admin.laporan') }}" class="px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition shadow">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>


                    {{-- ================================================= --}}
                    {{-- BLOK TABEL LAPORAN DETAIL --}}
                    {{-- ================================================= --}}
                    <h3 class="text-xl font-bold mt-8 mb-4">Data Gatepass Hasil Filter ({{ $izins->count() ?? 0 }} Data)</h3>

                    @if($izins->isEmpty())
                        <div class="p-6 bg-red-50 border-l-4 border-red-400 text-red-700 rounded-lg">
                            Tidak ditemukan data Gatepass sesuai kriteria filter yang dipilih.
                        </div>
                    @else
                        <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Izin</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Pengajuan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemohon</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    {{-- Iterasi dilakukan di sini --}}
                                    @foreach ($izins as $izin)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">{{ $izin->nomor_izin }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ ucfirst($izin->jenis_izin) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($izin->tanggal)->isoFormat('D MMM Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($izin->pembuat)->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $izin->status === 'Disetujui Final' ? 'bg-green-100 text-green-800' : 
                                                    ($izin->status === 'Ditolak' ? 'bg-red-100 text-red-800' : 
                                                    'bg-yellow-100 text-yellow-800') }}">
                                                    {{ $izin->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('izin.detail', $izin->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    Lihat Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>