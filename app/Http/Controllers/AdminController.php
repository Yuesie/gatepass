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
use Illuminate\Support\Str; 

class AdminController extends Controller
{
    /**
     * Menampilkan daftar pengguna (untuk route admin.pengguna)
     */
    public function index()
    {
        $users = User::paginate(10); 
        return view('admin.pengguna', compact('users'));
    }

    /**
     * Menampilkan form tambah pengguna
     */
    public function create()
    {
        return view('admin.pengguna-form');
    }

    /**
     * Menyimpan pengguna baru ke database
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

        $roleFinal = $this->mapJabatanToRole($validated['jabatan_terpilih']);

        if ($request->hasFile('signature_path')) {
            $file = $request->file('signature_path');
            
            $folder = 'ttd_approver';
            $fileName = time() . '_' . Str::slug($validated['jabatan_terpilih']) . '.' . $file->getClientOriginalExtension();
            
            try {
                $path = $file->storeAs($folder, $fileName, 'public');
                $signaturePath = $path; 
            } catch (\Exception $e) {
                Log::error("Gagal menyimpan file TTD: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan file TTD ke server.');
            }
        }

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

        if ($request->hasFile('signature_path')) {
            if ($user->signature_path) {
                Storage::disk('public')->delete($user->signature_path);
            }

            $file = $request->file('signature_path');
            $folder = 'ttd_approver';
            $fileName = time() . '_' . Str::slug($validated['jabatan_terpilih']) . '.' . $file->getClientOriginalExtension();
            
            try {
                $path = $file->storeAs($folder, $fileName, 'public');
                $signaturePath = $path;
            } catch (\Exception $e) {
                Log::error("Gagal update file TTD: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Gagal update file TTD ke server.');
            }
        }

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
     * Menghapus pengguna
     */
    public function destroy(User $user)
    {
        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
        }
        $user->delete();
        return redirect()->route('admin.pengguna')->with('success', 'Pengguna berhasil dihapus.');
    }

    // =================================================================
    // FUNGSI LAPORAN
    // =================================================================

    /**
     * Menampilkan Halaman Laporan Global
     */
    public function laporan(Request $request)
    {
        // 1. Ambil Data Statistik Ringkasan
        $totalIzin = IzinMasuk::count();
        $totalFinal = IzinMasuk::where('status', 'Disetujui Final')->count();
        $totalRejected = IzinMasuk::where('status', 'Ditolak')->count();
        $totalUsers = User::count();

        // 2. Query Data Izin berdasarkan Filter
        $query = IzinMasuk::with('pemohon'); 

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->input('end_date'));
        }
        if ($request->filled('jenis_izin')) {
            $query->where('jenis_izin', $request->input('jenis_izin'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $izins = $query->orderBy('tanggal', 'desc')->get();

        // 3. Kirim data ke View
        return view('admin.laporan', compact(
            'totalIzin', 
            'totalFinal', 
            'totalRejected', 
            'totalUsers',
            'izins'
        ));
    }
    
    /**
     * Menghapus Gatepass dari Laporan Admin.
     * INI ADALAH FUNGSI YANG HILANG DAN MENYEBABKAN ERROR ROUTE NOT DEFINED
     */
    public function deleteIzin(IzinMasuk $izin)
    {
        try {
            // Hapus TTD Pemohon jika ada
            if ($izin->ttd_pemohon_path) {
                Storage::disk('public')->delete($izin->ttd_pemohon_path);
            }

            // Hapus foto-foto barang terkait
            $izin->load('detailBarang');
            if ($izin->detailBarang) {
                foreach ($izin->detailBarang as $detail) {
                    if ($detail->foto_path) {
                        Storage::disk('public')->delete($detail->foto_path);
                    }
                }
            }

            $nomorIzin = $izin->nomor_izin;
            
            // Hapus Izin Masuk utama
            $izin->delete(); 
            
            return redirect()->route('admin.laporan')->with('success', "Gatepass **#{$nomorIzin}** berhasil dihapus permanen.");
            
        } catch (\Exception $e) {
            Log::error("Gagal menghapus Gatepass {$izin->id}: " . $e->getMessage());
            
            return redirect()->route('admin.laporan')->with('error', '❌ Gagal menghapus Gatepass. Terjadi kesalahan sistem.');
        }
    }

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