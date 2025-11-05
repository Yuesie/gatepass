<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Riwayat Pengajuan Gatepass') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Message akan otomatis muncul di sini --}}
            
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-gray-900 text-2xl font-bold mb-6 border-b pb-4">Daftar Gatepass</h3>

                    @if($riwayatIzin->isEmpty())
                        <div class="p-4 text-center bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 rounded-lg">
                            <p class="font-bold">Belum Ada Pengajuan</p>
                            <p>Anda belum pernah mengajukan Gatepass. Silakan buat yang pertama!</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Izin</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perihal</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($riwayatIzin as $izin)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $izin->nomor_izin }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($izin->tanggal)->format('d M Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $izin->perihal }}</td>
                                            
                                            {{-- Kolom Status --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ 
                                                        $izin->status == 'Menunggu' ? 'bg-yellow-100 text-yellow-800' : 
                                                        ($izin->status == 'Disetujui' ? 'bg-green-100 text-green-800' : 
                                                        ($izin->status == 'Ditolak' ? 'bg-red-100 text-red-800' : 
                                                        'bg-gray-100 text-gray-800')) 
                                                    }}">
                                                    {{ $izin->status }}
                                                </span>
                                            </td>

                                            {{-- Kolom Aksi --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                <a href="{{ route('izin.detail', $izin->id) }}" class="text-indigo-600 hover:text-indigo-900 mx-1">Detail</a>
                                                
                                                {{-- Tampilkan tombol Batalkan hanya jika status 'Menunggu' --}}
                                                @if($izin->status == 'Menunggu')
                                                    <form action="{{ route('izin.batal', $izin->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?');">
                                                        @csrf
                                                        <button type="submit" class="text-red-600 hover:text-red-900 mx-1">Batalkan</button>
                                                    </form>
                                                @endif

                                                <a href="{{ route('izin.cetak', $izin->id) }}" class="text-blue-600 hover:text-blue-900 mx-1 ml-2">Cetak</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Link Pagination --}}
                        <div class="mt-4 p-4 border-t">
                            {{ $riwayatIzin->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>