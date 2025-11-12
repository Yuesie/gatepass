// public/firebase-messaging-sw.js

// Wajib: Import pustaka Firebase untuk FCM
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

// =========================================================
// KONFIGURASI PWA CACHING (DIKOSONGKAN UNTUK MENGHINDARI ERROR)
// =========================================================
const CACHE_NAME = 'gatepass-fcm-cache-v1-minimal'; 
// Kita sengaja mengosongkan array ini untuk mencegah error 'cache.addAll'
const urlsToCache = []; 

// =========================================================
// KONFIGURASI FIREBASE (HARDCODE - WAJIB)
// =========================================================
// Ganti nilai ini dengan konfigurasi Firebase Anda dari .env
firebase.initializeApp({
    apiKey: "AIzaSyC_VeudH4lhz0SBef9ari_K9NGmfcm2UqI",
    authDomain: "magang-f6091.firebaseapp.com",
    projectId: "magang-f6091",
    storageBucket: "magang-f6091.appspot.com", // Menggunakan .appspot.com sesuai V8 compat
    messagingSenderId: "1032919548215",
    appId: "1:1032919548215:web:b006735f8d64c30ddedab2"
});

const messaging = firebase.messaging();

// =========================================================
// EVENT HANDLERS PWA (MINIMAL)
// =========================================================
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(urlsToCache); // Aman karena array kosong
        })
    );
    self.skipWaiting(); 
});

self.addEventListener('activate', event => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    event.waitUntil(self.clients.claim());
});

// =========================================================
// EVENT HANDLERS FCM (PALING PENTING)
// =========================================================

// 4. FCM Event: onBackgroundMessage (Saat aplikasi ditutup)
messaging.onBackgroundMessage((payload) => {
    console.log("📨 Pesan diterima di background:", payload);

    const notificationTitle = payload.notification?.title || "Notifikasi Baru";
    const notificationOptions = {
        body: payload.notification?.body || "Anda menerima pesan baru.",
        icon: "/images/logo.png", // Pastikan path ini benar (opsional)
        data: payload.data 
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// 5. FCM Event: notificationclick (Saat notifikasi diklik)
self.addEventListener('notificationclick', event => {
    event.notification.close(); 
    const targetUrl = event.notification.data?.fcmOptions?.link || '/'; 

    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(windowClients => {
            for (let client of windowClients) {
                if (client.url.endsWith(targetUrl) && 'focus' in client) {
                    return client.focus(); 
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl); 
            }
        })
    );
});