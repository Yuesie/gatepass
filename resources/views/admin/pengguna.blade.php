<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Flash Message akan otomatis muncul di sini (dari app.blade.php) --}}
            
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6 border-b pb-4">
                        <h3 class="text-gray-900 text-2xl font-bold">Daftar Akun Pengguna</h3>
                        <a href="{{ route('admin.pengguna.buat') }}"
                           class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition shadow">
                            + Tambah Pengguna Baru
                        </a>
                    </div>

                    @if($users->isEmpty())
                        <div class="p-4 text-center bg-gray-50 border rounded-lg">
                            <p class="text-gray-600">Belum ada data pengguna yang terdaftar.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peran</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($users as $index => $user)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $users->firstItem() + $index }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $user->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $user->email }}
                                            </td>
                                            
                                            {{-- KODE PERBAIKAN PERAN/ROLE BADGE (Defensif) --}}
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    // Dapatkan peran dengan default 'pemohon' jika null/kosong
                                                    $peran = strtolower($user->peran ?? 'pemohon');
                                                    $cssClass = 'bg-indigo-100 text-indigo-800'; 
                                                    
                                                    if ($peran == 'admin') {
                                                        $cssClass = 'bg-red-100 text-red-800';
                                                    } elseif ($peran == 'security') {
                                                        $cssClass = 'bg-yellow-100 text-yellow-800';
                                                    } elseif ($peran == 'manager') {
                                                        $cssClass = 'bg-green-100 text-green-800';
                                                    } elseif ($peran == 'atasan_pemohon') {
                                                        $cssClass = 'bg-purple-100 text-purple-800';
                                                    } elseif ($peran == 'teknik' || $peran == 'hsse') {
                                                        $cssClass = 'bg-blue-100 text-blue-800';
                                                    }
                                                @endphp

                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cssClass }}">
                                                    {{ ucfirst(str_replace('_', ' ', $user->peran ?? 'N/A')) }}
                                                </span>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                <a href="{{ route('admin.pengguna.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 mx-2">Edit</a>
                                                
                                                {{-- Tombol Hapus dengan form DELETE --}}
                                                <form action="{{ route('admin.pengguna.delete', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ $user->name }}? Tindakan ini tidak dapat dibatalkan!');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 mx-2">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Link Pagination --}}
                        <div class="mt-4 p-4 border-t">
                            {{ $users->links() }} {{-- BARIS INI KRITIS --}}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>