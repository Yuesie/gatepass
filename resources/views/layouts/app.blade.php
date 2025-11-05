<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        
        <div class="flex min-h-screen bg-gray-100">
            
            {{-- 1. Sidebar Kustom (@include('layouts.sidebar-nav')) tetap --}}
            @include('layouts.sidebar-nav') 

            {{-- 2. Main Content Container --}}
            <div x-data="{ sidebarOpen: true }" 
                 :class="sidebarOpen ? 'ml-64' : 'ml-20'"
                 class="flex-1 min-h-screen transition-all duration-300">
                
                {{-- 3. Header Konten - DIBUAT FIXED --}}
                @if (isset($header))
                    {{-- KOREKSI: Tambahkan fixed, top-0, right-0, z-20. 
                       Left disesuaikan dinamis dengan sidebar --}}
                    <header :class="sidebarOpen ? 'left-64' : 'left-20'"
                            class="fixed top-0 right-0 z-20 w-auto bg-white shadow transition-all duration-300">
                        <div class="py-6 px-4 sm:px-6 lg:px-8"> 
                            {{ $header }}
                        </div>
                    </header>
                @endif

                {{-- 4. Main Content --}}
                {{-- KOREKSI: Tambahkan pt-20 untuk mengimbangi tinggi header fixed --}}
                <main class="pt-20 pb-12 px-4 sm:px-6 lg:px-8">
                    
                    {{-- AREA UNTUK FLASH MESSAGE --}}
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

                    {{ $slot }}
                </main>
            </div>
        </div>

        {{-- ✅ Tambahkan ini agar script dari @push('scripts') dijalankan --}}
        @stack('scripts')

    </body>
</html>
