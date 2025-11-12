<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- REKOMENDASI: Gunakan versi SDK v9 yang lebih baru, misal 9.23.0 --}}
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.8/axios.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    {{-- ❌ PERBAIKAN: Hapus @include sidebar ganda yang ada di sini --}}
    {{-- @include('layouts.sidebar-nav') --}}

    <div class="flex min-h-screen bg-gray-100">
        {{-- Sidebar --}}
        @include('layouts.sidebar-nav')

        {{-- Main Container --}}
        <div x-data="{ sidebarOpen: true }" 
             :class="sidebarOpen ? 'ml-64' : 'ml-20'"
             class="flex-1 min-h-screen transition-all duration-300">

            {{-- Header --}}
            @if (isset($header))
                <header :class="sidebarOpen ? 'left-64' : 'left-20'"
                        class="fixed top-0 right-0 z-20 w-auto bg-white shadow transition-all duration-300">
                    <div class="py-6 px-4 sm:px-6 lg:px-8"> 
                        {{ $header }}
                    </div>
                </header>
            @endif

            {{-- Main Content --}}
            <main class="pt-20 pb-12 px-4 sm:px-6 lg:px-8">
                {{-- Flash Message --}}
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

    @stack('scripts')

    <script>
        const SW_URL = '{{ asset('firebase-messaging-sw.js') }}';

        // === 1. Daftarkan Service Worker ===
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register(SW_URL, { scope: '/' })
                    .then(registration => {
                        console.log('✅ FCM: Service Worker terdaftar.');
                        @auth
                            // Panggil inisialisasi FCM SETELAH SW terdaftar
                            // Kita tidak perlu mengirim 'registration' lagi
                            initializeFCM(); 
                        @endauth
                    })
                    .catch(err => console.error('❌ FCM: Gagal daftar SW:', err));
            });
        }
    </script>

    @auth
    <script>
        // === 2. Konfigurasi Firebase ===
        const FIREBASE_CONFIG = {
            apiKey: "{{ env('FCM_API_KEY') }}",
            authDomain: "{{ env('FCM_PROJECT_ID') }}.firebaseapp.com",
            projectId: "{{ env('FCM_PROJECT_ID') }}",
            storageBucket: "{{ env('FCM_PROJECT_ID') }}.appspot.com",
            messagingSenderId: "{{ env('FCM_SENDER_ID') }}",
            appId: "{{ env('FCM_APP_ID') }}"
        };

        firebase.initializeApp(FIREBASE_CONFIG);
        const messaging = firebase.messaging();
        const vapidKey = "{{ env('FCM_VAPID_KEY') }}";

        // === 3. Ambil & Simpan Token ke Server ===
        function retrieveToken() {
            messaging.getToken({ vapidKey }).then(currentToken => {
                if (currentToken) {
                    axios.post('{{ route('save.fcm.token') }}', {
                        _token: '{{ csrf_token() }}',
                        fcm_token: currentToken
                    }).then(() => {
                        console.log('💾 Token FCM disimpan di server.');
                    }).catch(err => {
                        console.error('Gagal simpan token:', err);
                    });
                } else {
                    console.warn('Tidak ada token yang tersedia.');
                }
            }).catch(err => console.error('Error mengambil token:', err));
        }

        // === 4. Inisialisasi FCM (PERBAIKAN KODE) ===
        function initializeFCM() { // {{-- PERBAIKAN: Hapus parameter 'registration' --}}
            
            // ❌ PERBAIKAN: HAPUS BARIS INI. 
            // Ini adalah fungsi v8 dan penyebab error di v9.
            // messaging.useServiceWorker(registration);

            // Tunggu hingga SW benar-benar "aktif" sebelum meminta token
            navigator.serviceWorker.ready.then((activeRegistration) => {
                console.log('FCM: Service Worker sekarang aktif.');
                
                // Minta izin notifikasi
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        console.log('🔔 Izin notifikasi diberikan.');
                        // SEKARANG aman untuk mengambil token
                        retrieveToken();
                    } else {
                        console.warn('❌ Izin notifikasi ditolak.');
                    }
                });
            });

            // Handler untuk notifikasi foreground (saat tab terbuka)
            messaging.onMessage(payload => {
                console.log('📩 Pesan foreground diterima:', payload);
                new Notification(payload.notification.title, {
                    body: payload.notification.body,
                    icon: '{{ asset('images/logo.png') }}',
                });
            });
        }
    </script>
    @endauth
</body>
</html>