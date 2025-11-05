<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($user) ? 'Edit Pengguna: ' . $user->name : 'Tambah Pengguna Baru' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    
                    {{-- Tentukan URL Aksi Form (CREATE atau UPDATE) --}}
                    @if(isset($user))
                        {{-- Jika $user ada (mode EDIT) --}}
                        <form method="POST" action="{{ route('admin.pengguna.update', $user->id) }}">
                        @method('PUT') {{-- Metode HTTP untuk Update --}}
                    @else
                        {{-- Jika $user TIDAK ada (mode CREATE) --}}
                        <form method="POST" action="{{ route('admin.pengguna.store') }}">
                    @endif
                    
                    @csrf {{-- Token Keamanan Laravel --}}

                    <h3 class="text-gray-900 text-xl font-bold mb-6">
                        Data Detail Pengguna
                    </h3>
                    
                    {{-- 1. Nama --}}
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="name" id="name" required
                                value="{{ old('name', $user->name ?? '') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 2. Email --}}
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" required
                                value="{{ old('email', $user->email ?? '') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 3. Peran (Role) --}}
                    <div class="mb-4">
                        <label for="peran" class="block text-sm font-medium text-gray-700">Peran Pengguna (Role)</label>
                        <select name="peran" id="peran" required
                                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('peran') border-red-500 @enderror">
                            @php
                                // KOREKSI FINAL: 7 role yang sesuai dengan migrasi dan AdminController.php
                                $roles = ['admin', 'pemohon', 'atasan_pemohon', 'security', 'manager', 'teknik', 'hsse'];
                                $currentRole = old('peran', $user->peran ?? '');
                            @endphp

                            <option value="">-- Pilih Peran --</option>
                            @foreach ($roles as $role)
                                @php
                                    $displayRole = ucfirst(str_replace('_', ' ', $role));
                                    if ($role === 'manager') $displayRole = 'Manager (L3 Final)';
                                    if ($role === 'atasan_pemohon') $displayRole = 'Atasan Pemohon (L1 Wajib)';
                                @endphp
                                
                                <option value="{{ $role }}" {{ $currentRole == $role ? 'selected' : '' }}>
                                    {{ $displayRole }}
                                </option>
                            @endforeach
                        </select>
                        @error('peran')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    {{-- 4. Password (Opsional/Ganti Password) --}}
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Kata Sandi ({{ isset($user) ? 'Kosongkan jika tidak ingin ganti' : 'Wajib diisi' }})
                        </label>
                        <input type="password" name="password" id="password" 
                                {{ isset($user) ? '' : 'required' }} 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    {{-- 5. Konfirmasi Password --}}
                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                            Konfirmasi Kata Sandi
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation" 
                                {{ isset($user) ? '' : 'required' }} 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('password') border-red-500 @enderror">
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex justify-end space-x-3 border-t pt-4">
                        <a href="{{ route('admin.pengguna') }}" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition shadow">
                            {{ isset($user) ? 'Simpan Perubahan' : 'Simpan Pengguna' }}
                        </button>
                    </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>