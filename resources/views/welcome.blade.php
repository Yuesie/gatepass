<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Gatepass - {{ config('app.name', 'Laravel') }}</title>
    
    {{-- BARIS PENTING UNTUK PWA: Menghubungkan Web App Manifest --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}"> 
    
    {{-- PENTING UNTUK PWA: Meta tag untuk warna toolbar di mobile --}}
    <meta name="theme-color" content="#317EFB"/> 
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            /* Ganti 'images/background.jpg' dengan path gambar Anda */
            background-image: url("{{ asset('images/background.jpg') }}"); 
            background-size: cover; 
            background-position: center; 
            background-repeat: no-repeat;
            background-attachment: fixed; 
        }
        /* Tambahkan sedikit overlay gelap agar teks mudah dibaca */
        .overlay {
            background-color: rgba(0, 0, 0, 0.5); 
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body class="antialiased">
    
    <div class="overlay"></div> 
    
    <div class="relative flex items-top justify-center min-h-screen sm:items-center py-4 sm:pt-0 z-10 text-white">
        
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col items-center pt-8 sm:justify-start sm:pt-0">
                <h1 class="text-5xl font-extrabold mb-4">
                    Sistem Gatepass Digital
                </h1>
                <p class="text-xl mb-10">
                    Aplikasi Izin Masuk / Keluar Barang & Material.
                </p>

                <div class="flex space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg shadow-lg hover:bg-indigo-700 transition duration-300">
                            Masuk ke Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg shadow-lg hover:bg-indigo-700 transition duration-300">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="px-6 py-3 bg-gray-200 text-gray-800 font-bold rounded-lg shadow-lg hover:bg-gray-300 transition duration-300">
                            Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
    
    {{-- Di sini Anda bisa menambahkan kode pendaftaran Service Worker jika belum ada --}}
    {{-- Lihat poin 3 di bawah. --}}
    
</body>
</html>