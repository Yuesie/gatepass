<div x-data="{ sidebarOpen: true }" 
    :class="sidebarOpen ? 'w-64' : 'w-20'" 
    {{-- Latar Belakang Gelap dan Teks Terang --}}
    class="fixed top-0 left-0 h-full bg-slate-900 text-gray-200 p-4 z-40 transition-all duration-300 shadow-xl flex flex-col justify-between"
>
    
    @php
        $userPeran = Auth::user()->peran;
        // KELOMPOK APPROVER LENGKAP: Wajib ada untuk menampilkan menu Persetujuan
        $approverRoles = ['atasan_pemohon', 'security', 'manager', 'teknik', 'hsse']; 
    @endphp

    <div class="flex flex-col mb-8">
    {{-- Header & Tombol Toggle --}}
    <div class="flex items-center mb-6" :class="sidebarOpen ? 'justify-between' : 'justify-center'">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2" 
            :class="{ 'opacity-100' : sidebarOpen, 'opacity-0 w-0 h-0 overflow-hidden' : !sidebarOpen }"
        >
            {{-- BLOK LOGO BARU --}}
            
                 
            {{-- TEKS GERBANG DIGITAL --}}
            <span class="text-xl font-bold tracking-tight text-white transition-opacity duration-300">
                GERBANG DIGITAL
            </span>
        </a>
        
        <button @click="sidebarOpen = ! sidebarOpen" class="text-gray-400 focus:outline-none p-1 rounded-full hover:bg-gray-700 transition-colors"
            :class="{ 'opacity-100' : sidebarOpen, 'opacity-0' : !sidebarOpen }"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
        </button>
    </div>

        {{-- BLOK NAVIGASI UTAMA --}}
        <div class="flex flex-col space-y-2">

            {{-- Dashboard (Semua Peran) --}}
            <a href="{{ route('dashboard') }}" 
                class="flex items-center py-2 px-3 rounded-lg transition duration-200 text-gray-300 hover:bg-gray-700 hover:text-white"
                {{-- PERBAIKAN SINTAKS ALPINE: Gunakan string interpolation --}}
                :class="`{ 'justify-start' : sidebarOpen, 'justify-center' : !sidebarOpen, 'bg-gray-700 text-white font-semibold' : {{ request()->routeIs('dashboard') ? 'true' : 'false' }} }`"
            >
                <span class="w-6 h-6 flex items-center justify-center">🏠</span>
                <span x-show="sidebarOpen" class="ml-3 transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">Dashboard</span>
            </a>

            
            {{-- MENU PERSETUJUAN (APPROVER) --}}
            @if (in_array($userPeran, $approverRoles))
                <p x-show="sidebarOpen" class="text-xs font-semibold uppercase text-gray-500 mt-4 mb-2 tracking-wider transition-opacity duration-300">PERSETUJUAN</p>
                <a href="{{ route('izin.persetujuan') }}" 
                    class="flex items-center py-2 px-3 rounded-lg transition duration-200 text-yellow-300 hover:bg-gray-700 hover:text-yellow-400"
                    {{-- PERBAIKAN SINTAKS ALPINE --}}
                    :class="`{ 'justify-start' : sidebarOpen, 'justify-center' : !sidebarOpen, 'bg-gray-700 text-yellow-400 font-semibold' : {{ request()->routeIs('izin.persetujuan') ? 'true' : 'false' }} }`"
                >
                    <span class="w-6 h-6 flex items-center justify-center">✅</span>
                    <span x-show="sidebarOpen" class="ml-3 transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">Daftar Persetujuan</span>
                </a>
            @endif

            {{-- MENU GATEPASS (PEMOHON) --}}
            @if ($userPeran == 'pemohon') 
                <p x-show="sidebarOpen" class="text-xs font-semibold uppercase text-gray-500 mt-4 mb-2 tracking-wider transition-opacity duration-300">GATEPASS</p>

                {{-- Menu: Buat Gatepass Baru --}}
                <a href="{{ route('izin.buat') }}" 
                    class="flex items-center py-2 px-3 text-sm rounded-lg transition duration-200 text-indigo-300 hover:bg-gray-700 hover:text-indigo-400"
                    {{-- PERBAIKAN SINTAKS ALPINE --}}
                    :class="`{ 'justify-start' : sidebarOpen, 'justify-center' : !sidebarOpen, 'bg-gray-700 text-indigo-400 font-semibold' : {{ request()->routeIs('izin.buat') ? 'true' : 'false' }} }`"
                >
                    <span class="w-6 h-6 flex items-center justify-center">➕</span>
                    <span x-show="sidebarOpen" class="ml-3 transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">Buat Gatepass Baru</span>
                </a>
                
                {{-- Menu: Riwayat Gatepass --}}
                <a href="{{ route('izin.riwayat') }}" 
                    class="flex items-center py-2 px-3 text-sm rounded-lg transition duration-200 text-indigo-300 hover:bg-gray-700 hover:text-indigo-400"
                    {{-- PERBAIKAN SINTAKS ALPINE --}}
                    :class="`{ 'justify-start' : sidebarOpen, 'justify-center' : !sidebarOpen, 'bg-gray-700 text-indigo-400 font-semibold' : {{ request()->routeIs('izin.riwayat') ? 'true' : 'false' }} }`"
                >
                    <span class="w-6 h-6 flex items-center justify-center">📜</span>
                    <span x-show="sidebarOpen" class="ml-3 transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">Riwayat Gatepass</span>
                </a>
            @endif


            {{-- MENU ADMINISTRASI (ADMIN) --}}
            @if ($userPeran == 'admin')
                <p x-show="sidebarOpen" class="text-xs font-semibold uppercase text-gray-500 mt-4 mb-2 tracking-wider transition-opacity duration-300">ADMINISTRASI</p>

                {{-- Menu: Kelola Pengguna --}}
                <a href="{{ route('admin.pengguna') }}" 
                    class="flex items-center py-2 px-3 text-sm rounded-lg transition duration-200 text-pink-300 hover:bg-gray-700 hover:text-pink-400"
                    {{-- PERBAIKAN SINTAKS ALPINE --}}
                    :class="`{ 'justify-start' : sidebarOpen, 'justify-center' : !sidebarOpen, 'bg-gray-700 text-pink-400 font-semibold' : {{ request()->routeIs('admin.pengguna') || request()->routeIs('admin.pengguna.*') ? 'true' : 'false' }} }`"
                >
                    <span class="w-6 h-6 flex items-center justify-center">👥</span>
                    <span x-show="sidebarOpen" class="ml-3 transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">Kelola Pengguna</span>
                </a>
                
                {{-- Menu: Laporan Sistem --}}
                <a href="{{ route('admin.laporan') }}" 
                    class="flex items-center py-2 px-3 text-sm rounded-lg transition duration-200 text-pink-300 hover:bg-gray-700 hover:text-pink-400"
                    {{-- PERBAIKAN SINTAKS ALPINE --}}
                    :class="`{ 'justify-start' : sidebarOpen, 'justify-center' : !sidebarOpen, 'bg-gray-700 text-pink-400 font-semibold' : {{ request()->routeIs('admin.laporan') ? 'true' : 'false' }} }`"
                >
                    <span class="w-6 h-6 flex items-center justify-center">📈</span>
                    <span x-show="sidebarOpen" class="ml-3 transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">Laporan Sistem</span>
                </a>
            @endif

        </div>
    </div>

    {{-- Logout --}}
    <div class="border-t border-gray-700 pt-4 mt-auto">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" 
                class="flex items-center w-full py-2 px-3 text-left rounded-lg transition duration-200 text-red-400 hover:bg-red-900 hover:text-white"
                :class="{ 'justify-start' : sidebarOpen, 'justify-center' : !sidebarOpen }"
            >
                <span class="w-6 h-6 flex items-center justify-center">🚪</span>
                <span x-show="sidebarOpen" class="ml-3 transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0'">Keluar</span>
            </button>
        </form>
    </div>
</div>