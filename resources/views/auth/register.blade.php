<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email (contoh: mps@perusahaan.com)')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="jabatan_terpilih" :value="__('Jabatan / Peran')" />

            <select id="jabatan_terpilih" name="jabatan_terpilih" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="">-- Pilih Jabatan --</option>
                
                {{-- Daftar Jabatan Sesuai Permintaan Anda --}}
                <option value="Spv II MPS" @if(old('jabatan_terpilih') == 'Spv II MPS') selected @endif>Spv II MPS</option>
                <option value="SPV II HSSE & FS" @if(old('jabatan_terpilih') == 'SPV II HSSE & FS') selected @endif>SPV II HSSE & FS</option>
                <option value="Sr Spv RSD" @if(old('jabatan_terpilih') == 'Sr Spv RSD') selected @endif>Sr Spv RSD</option>
                <option value="Spv I QQ" @if(old('jabatan_terpilih') == 'Spv I QQ') selected @endif>Spv I QQ</option>
                <option value="Spv I SSGA" @if(old('jabatan_terpilih') == 'Spv I SSGA') selected @endif>pv I SSGA</option>
                <option value="Admin" @if(old('jabatan_terpilih') == 'Admin') selected @endif>Admin</option>
                <option value="Jr Assistant Security TNI/POLRI" @if(old('jabatan_terpilih') == 'Jr Assistant Security TNI/POLRI') selected @endif>Jr Assistant Security TNI/POLRI</option>
                <option value="IT Manager Banjarmasin" @if(old('jabatan_terpilih') == 'IT Manager Banjarmasin') selected @endif>IT Manager Banjarmasin</option>
                <option value="Pjs IT Manager Banjarmasin" @if(old('jabatan_terpilih') == 'Pjs IT Manager Banjarmasin') selected @endif>Pjs IT Manager Banjarmasin</option>
                
            </select>
            <x-input-error :messages="$errors->get('jabatan_terpilih')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Sudah terdaftar?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>