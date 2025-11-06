<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 
use App\Models\IzinMasuk; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; 
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Str; // Tambahkan ini

class AdminController extends Controller
{
    /**
     * Menampilkan daftar pengguna (untuk route admin.pengguna)
     */
    public function index()
    {
        // Mengambil semua pengguna dengan pagination
        $users = User::paginate(10); 
        return view('admin.pengguna', compact('users'));
    }

    /**
     * Menampilkan form tambah pengguna (untuk route admin.pengguna.buat)
     */
    public function create()
    {
        return view('admin.pengguna-form');
    }

    /**
     * Menyimpan pengguna baru ke database (untuk route admin.pengguna.store)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'jabatan_terpilih' => 'required|string|max:255', 
            'password' => 'required|confirmed|min:8',
            'signature_path' => 'nullable|image|mimes:png|max:2048', 
        ], [
            'jabatan_terpilih.required' => 'Jabatan / Posisi wajib dipilih.',
            'signature_path.mimes' => 'File tanda tangan harus berformat PNG.',
        ]);

        $signaturePath = null;

        // Tentukan ROLE dan JABATAN sebelum menyimpan user (diperlukan untuk nama file TTD)
        $roleFinal = $this->mapJabatanToRole($validated['jabatan_terpilih']);

        // 1. --- LOGIKA UPLOAD TANDA TANGAN ---
        if ($request->hasFile('signature_path')) {
            $file = $request->file('signature_path');
            
            // Generate nama unik untuk sementara (sebelum User ID tersedia)
            // Setelah user dibuat, kita akan update path-nya jika perlu,
            // tapi untuk STORE, kita gunakan nama unik berdasarkan jabatan dan waktu
            $folder = 'ttd_approver';
            $fileName = time() . '_' . Str::slug($validated['jabatan_terpilih']) . '.' . $file->getClientOriginalExtension();
            
            try {
                // 🛑 KOREKSI: Gunakan storeAs untuk memastikan path yang benar dan aman.
                $path = $file->storeAs($folder, $fileName, 'public');
                $signaturePath = $path; // Path relatif yang benar: ttd_approver/nama_file.png
            } catch (\Exception $e) {
                Log::error("Gagal menyimpan file TTD: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan file TTD ke server.');
            }
        }

        // 2. SIMPAN PENGGUNA
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'peran' => $roleFinal,
            'jabatan_default' => $validated['jabatan_terpilih'],
            'password' => Hash::make($validated['password']),
            'signature_path' => $signaturePath, 
        ]);

        return redirect()->route('admin.pengguna')->with('success', 'Data pengguna berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit pengguna
     */
    public function edit(User $user)
    {
        return view('admin.pengguna-form', compact('user'));
    }

    /**
     * Memperbarui pengguna di database
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'jabatan_terpilih' => 'required|string|max:255',
            'password' => 'nullable|confirmed|min:8',
            'signature_path' => 'nullable|image|mimes:png|max:2048', 
        ], [
            'jabatan_terpilih.required' => 'Jabatan / Posisi wajib dipilih.',
            'signature_path.mimes' => 'File tanda tangan harus berformat PNG.',
        ]);

        $signaturePath = $user->signature_path; 

        // 1. --- LOGIKA UPDATE/GANTI TANDA TANGAN ---
        if ($request->hasFile('signature_path')) {
            // Hapus file lama (jika ada)
            if ($user->signature_path) {
                Storage::disk('public')->delete($user->signature_path);
            }

            $file = $request->file('signature_path');
            $folder = 'ttd_approver';
            $fileName = time() . '_' . Str::slug($validated['jabatan_terpilih']) . '.' . $file->getClientOriginalExtension();
            
            try {
                // 🛑 KOREKSI: Gunakan storeAs untuk memastikan path yang benar dan aman.
                $path = $file->storeAs($folder, $fileName, 'public');
                $signaturePath = $path;
            } catch (\Exception $e) {
                Log::error("Gagal update file TTD: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Gagal update file TTD ke server.');
            }
        }

        // 2. UPDATE DATA PENGGUNA
        $roleFinal = $this->mapJabatanToRole($validated['jabatan_terpilih']);
        
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->peran = $roleFinal;
        $user->jabatan_default = $validated['jabatan_terpilih'];
        $user->signature_path = $signaturePath; 

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();

        return redirect()->route('admin.pengguna')->with('success', 'Data pengguna berhasil diperbarui!');
    }

    /**
     * Menghapus pengguna (Menggantikan delete() dengan destroy() standar Laravel)
     */
    public function destroy(User $user) // PERUBAHAN NAMA METHOD
    {
        // Hapus TTD fisik jika ada
        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
        }
        $user->delete();
        return redirect()->route('admin.pengguna')->with('success', 'Pengguna berhasil dihapus.');
    }

    // ... (Fungsi laporan dan mapJabatanToRole tidak diubah) ...
    protected function mapJabatanToRole(string $jabatan): string
    {
        return match ($jabatan) {
            'Spv II MPS', 
            'SPV II HSSE & FS', 
            'Sr Spv RSD', 
            'Spv I QQ', 
            'Spv I SSGA' => 'atasan_pemohon',
            'Jr Assistant Security TNI/POLRI' => 'security',
            'IT Manager Banjarmasin', 
            'Pjs IT Manager Banjarmasin' => 'manager',
            'Admin' => 'admin',
            default => 'pemohon',
        };
    }
}