<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IzinMasuk;
use App\Models\User; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator; // Tetap dipertahankan, meskipun tidak selalu diperlukan
use Illuminate\Support\Collection; // Tetap dipertahankan

class AdminController extends Controller
{
    // Daftar Peran yang Diizinkan (Pastikan ini sesuai dengan data Anda)
    private $allowedRoles = ['admin', 'pemohon', 'atasan_pemohon', 'security', 'manager', 'teknik', 'hsse'];
    
    // ===================================================================
    // R - READ (Menampilkan Daftar Pengguna) - PRODUCTION MODE
    // ===================================================================
    public function pengguna()
    {
        // ⬅️ KEMBALI KE MODE PRODUKSI (Menggunakan Database)
        $users = User::paginate(10);
        
        // ⚠️ Jika error 500 masih terjadi di sini, masalahnya ada pada koneksi DB atau Model User.
        return view('admin.pengguna', compact('users')); 
    }

    // ===================================================================
    // C - CREATE (Menampilkan Form Tambah)
    // ===================================================================
    public function buatPengguna()
    {
        $allowedRoles = $this->allowedRoles;
        return view('admin.pengguna-form', compact('allowedRoles')); 
    }

    // ===================================================================
    // C - CREATE (Menyimpan Pengguna Baru)
    // ===================================================================
    public function simpanPengguna(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'peran' => ['required', Rule::in($this->allowedRoles)],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'peran' => $request->peran,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.pengguna')->with('success', 'Pengguna baru berhasil ditambahkan!');
    }
    
    // ===================================================================
    // U - UPDATE (Menampilkan Form Edit)
    // ===================================================================
    // ⬅️ Menggunakan Route Model Binding: User $user
    public function editPengguna(User $user) 
    {
        $allowedRoles = $this->allowedRoles;
        return view('admin.pengguna-form', compact('user', 'allowedRoles'));
    }

    // ===================================================================
    // U - UPDATE (Menyimpan Perubahan)
    // ===================================================================
    // ⬅️ Menggunakan Route Model Binding: User $user
    public function updatePengguna(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'peran' => ['required', Rule::in($this->allowedRoles)],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->peran = $request->peran;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.pengguna')->with('success', 'Data pengguna berhasil diperbarui!');
    }

    // ===================================================================
    // D - DELETE (Menghapus Pengguna)
    // ===================================================================
    // ⬅️ Menggunakan Route Model Binding: User $user
    public function hapusPengguna(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('admin.pengguna')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }
        
        $user->delete();

        return redirect()->route('admin.pengguna')->with('success', 'Pengguna berhasil dihapus!');
    }

    // ===================================================================
    // R - READ (Menampilkan Laporan Global)
    // ===================================================================
   public function laporan(Request $request)
    {
        // Ambil Data Statistik Ringkasan
        $totalIzin = IzinMasuk::count();
        $totalFinal = IzinMasuk::where('status', 'Disetujui Final')->count();
        $totalRejected = IzinMasuk::where('status', 'Ditolak')->count();
        $totalUsers = User::count();

        // Query Data Detail (Dikenakan Filter)
        // *** PERBAIKAN DI SINI ***
        $izinsQuery = IzinMasuk::with('pemohon', 'approverL1', 'approverL2', 'approverL3') // Muat semua relasi yang mungkin diakses di view
            ->orderBy('tanggal', 'desc');

        // Filter: Rentang Tanggal
        if ($request->filled('start_date')) {
            $izinsQuery->whereDate('tanggal', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $izinsQuery->whereDate('tanggal', '<=', $request->input('end_date'));
        }

        // Filter: Jenis Izin
        if ($request->filled('jenis_izin')) {
            $izinsQuery->where('jenis_izin', $request->input('jenis_izin'));
        }

        // Filter: Status
        if ($request->filled('status')) {
            $izinsQuery->where('status', $request->input('status'));
        }

        $izins = $izinsQuery->get(); 

        // Kirim data ke View
        return view('admin.laporan', compact('totalIzin', 'totalFinal', 'totalRejected', 'totalUsers', 'izins'));
    }
}
