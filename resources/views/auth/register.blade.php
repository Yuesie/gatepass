<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- ===================== EMAIL ===================== --}}
        <div>
            <x-input-label for="email" :value="__('Email (contoh: mps@perusahaan.com)')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- ===================== JABATAN / PERAN ===================== --}}
        <div class="mt-4">
            <x-input-label for="jabatan_terpilih" :value="__('Jabatan / Peran')" />

            <select id="jabatan_terpilih" name="jabatan_terpilih" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="">-- Pilih Jabatan --</option>

                {{-- Daftar Jabatan Internal --}}
                <option value="Spv II MPS" @selected(old('jabatan_terpilih') == 'Spv II MPS')>Spv II MPS</option>
                <option value="SPV II HSSE & FS" @selected(old('jabatan_terpilih') == 'SPV II HSSE & FS')>SPV II HSSE & FS</option>
                <option value="Sr Spv RSD" @selected(old('jabatan_terpilih') == 'Sr Spv RSD')>Sr Spv RSD</option>
                <option value="Spv I QQ" @selected(old('jabatan_terpilih') == 'Spv I QQ')>Spv I QQ</option>
                <option value="Spv I SSGA" @selected(old('jabatan_terpilih') == 'Spv I SSGA')>Spv I SSGA</option>
                <option value="Admin" @selected(old('jabatan_terpilih') == 'Admin')>Admin</option>
                <option value="Jr Assistant Security TNI/POLRI" @selected(old('jabatan_terpilih') == 'Jr Assistant Security TNI/POLRI')>Jr Assistant Security TNI/POLRI</option>
                <option value="IT Manager Banjarmasin" @selected(old('jabatan_terpilih') == 'IT Manager Banjarmasin')>IT Manager Banjarmasin</option>
                <option value="Pjs IT Manager Banjarmasin" @selected(old('jabatan_terpilih') == 'Pjs IT Manager Banjarmasin')>Pjs IT Manager Banjarmasin</option>

                {{-- 🔹 Tambahan untuk akun KONTRAKTOR --}}
                <option value="Kontraktor" @selected(old('jabatan_terpilih') == 'Kontraktor')>Kontraktor</option>
            </select>

            <x-input-error :messages="$errors->get('jabatan_terpilih')" class="mt-2" />
        </div>

        {{-- ===================== PASSWORD ===================== --}}
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- ===================== KONFIRMASI PASSWORD ===================== --}}
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- ===================== BUTTON REGISTER ===================== --}}
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
