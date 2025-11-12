<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- PWA MANIFEST LINK (WAJIB) --}}
        <link rel="manifest" href="{{ asset('manifest.json') }}"> 
        
        {{-- PWA THEME COLOR (Disarankan, gunakan warna ungu/indigo sesuai tema Anda) --}}
        <meta name="theme-color" content="#4f46e5"/> 

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-transparent">
            
            {{-- Wrapper Form: Tambahkan class glass-effect dan hapus bg-white --}}
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 shadow-xl overflow-hidden sm:rounded-2xl glass-effect">
                
                {{-- Tempat Logo Pertamina di dalam Glass Effect --}}
                <div class="mb-6 flex justify-center">
                    {{-- Ganti path logo sesuai lokasi logo Pertamina Anda --}}
                    <img src="{{ asset('images/logo-login.png') }}" alt="Pertamina Patra Niaga" class="h-16 w-auto">
                </div>

                {{ $slot }}
            </div>
        </div>
        
        {{-- Pendaftaran Service Worker: Walaupun bisa di app.blade, lebih aman di kedua layout utama --}}
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    // Ganti '/sw.js' jika Service Worker utama Anda punya nama lain
                    navigator.serviceWorker.register('/sw.js')
                        .then(reg => console.log('Service Worker: Registered!', reg))
                        .catch(err => console.log('Service Worker: Failed to register!', err));
                });
            }
        </script>
    </body>
</html>