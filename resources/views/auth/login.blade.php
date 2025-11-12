<style>
    /* 1. Pengaturan BODY (Background Gambar) */
    body {
        /* Ganti 'images/background.jpg' dengan path gambar Anda */
        background-image: url("{{ asset('images/background.jpg') }}"); 
        background-size: cover; 
        background-position: center; 
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-color: transparent !important; /* Timpa warna default */
    }
    
    /* Hapus background color di min-h-screen */
    .min-h-screen {
        background-color: transparent !important; 
    }
    
    /* 2. Gaya untuk Efek Glassmorphism (Kaca Buram) */
    .glass-effect {
        /* Warna semi-transparan (putih) */
        background-color: rgba(255, 255, 255, 0.15); 
        
        /* Menerapkan efek blur */
        backdrop-filter: blur(10px); 
        -webkit-backdrop-filter: blur(10px); 
        
        /* Border, shadow, dan rounded corners untuk estetika */
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        
        /* Warna teks di dalam form menjadi putih agar terlihat kontras */
        color: white; 
    }

    /* 3. Penyesuaian Input Field */
    /* Menargetkan x-text-input component */
    .block.mt-1.w-full {
        background-color: rgba(255, 255, 255, 0.2) !important; /* Semi-transparan */
        border-color: rgba(255, 255, 255, 0.5) !important; /* Border putih */
        color: white !important; /* Warna teks yang diketik di dalam input */
    }
    /* Placeholder color (Opsional, mungkin perlu diatur di app.css) */
    .block.mt-1.w-full::placeholder {
        color: rgba(255, 255, 255, 0.8) !important;
    }


    /* 4. Penyesuaian Label & Teks Kecil */
    label, .text-sm.text-gray-600, .text-sm.text-gray-600 a {
        color: #fff !important; /* Warna putih untuk semua label dan teks */
        text-shadow: 0 0 2px rgba(0, 0, 0, 0.8); /* Tambahkan shadow agar teks lebih jelas */
    }

    /* 5. Penyesuaian Tombol Login (x-primary-button) */
    .ms-3 {
        background-color: #1e40af !important; /* Warna Indigo Tua */
    }
</style>

<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            {{-- Label akan otomatis menjadi putih berkat CSS di atas --}}
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                {{-- Teks Forgot Password otomatis menjadi putih/terang --}}
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>