<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IzinMasuk;
use App\Models\User; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator; 
use Illuminate\Support\Collection;
use Illuminate\Support\Str; // <-- Tambahkan ini untuk fungsi string helper

class AdminController extends Controller
{
    // Daftar Peran Sistem yang Diizinkan (Final)
    private $allowedRoles = ['admin', 'pemohon', 'atasan_pemohon', 'security', 'manager', 'teknik', 'hsse'];
    
    // Fungsi Mapping untuk menyelaraskan form Admin dengan form Registrasi
    private function mapJabatanToRole(string $jabatan): string
    {
        $jabatan = Str::lower($jabatan); // Normalisasi input

        if ($jabatan === 'admin') {
            return 'admin';
        } elseif (Str::contains($jabatan, ['manager', 'pjs'])) {
            return 'manager'; // Approver L3
        } elseif (Str::contains($jabatan, ['hsse', 'security', 'tni', 'polri'])) {
            return 'security'; // Approver L2/Spesialis
        } elseif (Str::contains($jabatan, 'spv')) {
            return 'atasan_pemohon'; // Approver L1
        } elseif ($jabatan === 'kontraktor') {
            return 'pemohon'; // Pembuat Gatepass
        }
        
        return 'pemohon'; // Default ke pembuat gatepass
    }
    
    // ===================================================================
    // R - READ (Menampilkan Daftar Pengguna)
    // ===================================================================
    public function pengguna()
    {
        $users = User::paginate(10);
        return view('admin.pengguna', compact('users')); 
    }

    // ===================================================================
    // C - CREATE (Menampilkan Form Tambah)
    // ===================================================================
    public function buatPengguna()
    {
        // View Form Admin akan menggunakan daftar jabatan
        return view('admin.pengguna-form'); 
    }

    // ===================================================================
    // C - CREATE (Menyimpan Pengguna Baru) - MENGGUNAKAN JABATAN
    // ===================================================================
    public function simpanPengguna(Request $request)
    {
        // PERHATIAN: Memvalidasi 'jabatan_terpilih' bukan 'peran'
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'jabatan_terpilih' => 'required|string|max:255',
            // Pastikan kolom 'jabatan_terpilih' ditambahkan ke $fillable di Model User
        ]);

        // 1. Tentukan Role dari Jabatan yang dipilih Admin
        $roleFinal = $this->mapJabatanToRole($request->jabatan_terpilih);

        // 2. Buat pengguna baru
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'peran' => $roleFinal, // Simpan Role Sistem yang dipetakan
            'jabatan_default' => $request->jabatan_terpilih, // Simpan Jabatan Deskriptif
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.pengguna')->with('success', 'Pengguna baru berhasil ditambahkan dengan peran: ' . $roleFinal);
    }
    
    // U - UPDATE, D - DELETE, laporan() (Kode lainnya disesuaikan)
    // ...
    public function editPengguna(User $user) 
    {
        return view('admin.pengguna-form', compact('user'));
    }

    public function updatePengguna(Request $request, User $user)
    {
        // Validasi dan update disesuaikan seperti di atas...
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'jabatan_terpilih' => 'required|string|max:255', // Validasi jabatan
        ]);
        
        $roleFinal = $this->mapJabatanToRole($request->jabatan_terpilih);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->peran = $roleFinal; // Update peran
        $user->jabatan_default = $request->jabatan_terpilih; // Update jabatan

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        return redirect()->route('admin.pengguna')->with('success', 'Data pengguna berhasil diperbarui!');
    }

    public function hapusPengguna(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('admin.pengguna')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }
        $user->delete();
        return redirect()->route('admin.pengguna')->with('success', 'Pengguna berhasil dihapus!');
    }
    
    public function laporan(Request $request)
    {
        $totalIzin = IzinMasuk::count();
        $totalFinal = IzinMasuk::where('status', 'Disetujui Final')->count();
        $totalRejected = IzinMasuk::where('status', 'Ditolak')->count();
        $totalUsers = User::count();

        // Menggunakan 'pemohon' (relasi yang sudah diperbaiki) dan approver
        $izinsQuery = IzinMasuk::with('pemohon', 'approverL1', 'approverL2', 'approverL3')->orderBy('tanggal', 'desc');

        // Filter Logics...

        $izins = $izinsQuery->get(); 

        return view('admin.laporan', compact('totalIzin', 'totalFinal', 'totalRejected', 'totalUsers', 'izins'));
    }
}