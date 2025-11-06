<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IzinMasuk; 
use App\Models\DetailBarang; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException; 
use Illuminate\Validation\Rule;
use App\Models\User; 
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;


class IzinMasukController extends Controller
{
    /** ----------------------------
     * 1. Formulir Buat Gatepass
     * ---------------------------- */
    public function buat()
    {
        // Mengambil data Approver tidak lagi diperlukan di sini karena pemilihan Nama adalah input teks manual.
        
        $approverL1 = collect([]); 
        $approverL2 = collect([]);
        $approverL3 = collect([]);
        
        // Mengirimkan variabel kosong ke view (hanya untuk mencegah error Blade jika ada sisa loop lama)
        return view('izin.buat', compact('approverL1', 'approverL2', 'approverL3')); 
    }
    /** ----------------------------
     * 2. Helper Generate Nomor Gatepass (Format Final)
     * ---------------------------- */
    private function generateNomorGatepass() 
    {
        $tahun = Carbon::now()->year;
        $prefixPerusahaan = 'GP-ITBJM'; 
        
        $nomorUrutTerakhir = IzinMasuk::count(); 
        $nomorUrut = $nomorUrutTerakhir + 1; 
        $nomorUrutPadded = str_pad($nomorUrut, 8, '0', STR_PAD_LEFT);
        
        return "{$nomorUrutPadded}/{$prefixPerusahaan}/{$tahun}";
    }
    
    protected function checkGatepassStatus(IzinMasuk $izin): string
    {
        // Hitung total penolakan
        if ($izin->l1_rejected || $izin->l2_rejected || $izin->l3_rejected) {
            return 'Ditolak';
        }
        
        // Cek status persetujuan berjenjang
        if ($izin->tgl_persetujuan_l3) {
            return 'Disetujui Final';
        }
        
        if ($izin->tgl_persetujuan_l2) {
            return 'Menunggu L3';
        }

        if ($izin->tgl_persetujuan_l1) {
            return 'Menunggu L2';
        }

        // Status awal
        return 'Menunggu L1'; 
    }

    /** ----------------------------
     * 3. Fungsi 'simpan' (Menyimpan Pengajuan)
     * ---------------------------- */
    // Dalam App/Http/Controllers/IzinMasukController.php

public function simpan(Request $request)
{
    // --- 0. ID APPROVER L2 GENERIK ---
    $ID_APPROVER_L2_GENERIK = 8; // Akun generik Security (Role 'security')

    // 1. VALIDASI DATA
    try {
        $validated = $request->validate([
            // ... (Validasi lainnya) ...
            'tanggal' => 'required|date',
            'jenis_izin' => 'required|in:masuk,keluar',
            'fungsi_pemohon' => 'required|string|max:255',
            'nomor_telepon_pemohon' => 'nullable|string|max:20',
            'dasar_pekerjaan' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'nama_perusahaan' => 'required|string|max:255',
            'tujuan_pengiriman' => 'required|string|max:255',
            'pembawa_barang' => 'required|string|max:255',
            'nomor_kendaraan' => 'nullable|string|max:255',
            'ttd_pemohon' => 'required|string', 
            'jabatan_approver_l1' => 'required|string|max:100',
            'nama_approver_l1' => 'required|string|max:100',
            'jabatan_approver_l2' => 'required|string|max:100',
            'nama_approver_l2' => 'required|string|max:100',
            'jabatan_approver_l3' => 'required|string|max:100', // Pilihan L3
            'nama_approver_l3' => 'required|string|max:100',
            'nama_item.*' => 'required|string|max:255',
            'qty.*' => 'required|integer|min:1',
            'satuan.*' => 'required|string|max:50',
            'keterangan_item.*' => 'required|string|max:255',
        ], [
            'ttd_pemohon.required' => 'Data Tanda Tangan kosong. Mohon berikan tanda tangan.',
        ]);
    } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
    }

    // --- 1a & 1b. MENCARI ID APPROVER L1 ---
    $fungsiPemohon = $validated['fungsi_pemohon'];
    $jabatanApproverL1Penuh = match ($fungsiPemohon) {
        'MPS' => 'Spv II MPS', 'HSSE & FS' => 'SPV II HSSE & FS', 'RSD' => 'Sr Spv RSD',
        'QQ' => 'Spv I QQ', 'SSGA' => 'Spv I SSGA', default => null, 
    };
    
    if (is_null($jabatanApproverL1Penuh)) {
        Log::error("Fungsi Pemohon tidak valid: " . $fungsiPemohon);
        return redirect()->back()->withInput()->with('error', '❌ Fungsi Pemohon tidak valid.');
    }

    $approverL1User = User::where('peran', 'atasan_pemohon')
                            ->where('jabatan_default', $jabatanApproverL1Penuh) 
                            ->first();

    if (!$approverL1User) {
        Log::error("Tidak ada akun Approver L1 generik terdaftar untuk Jabatan: " . $jabatanApproverL1Penuh);
        return redirect()->back()->withInput()->with('error', '❌ Akun persetujuan L1 generik untuk fungsi **' . $fungsiPemohon . '** (Jabatan: ' . $jabatanApproverL1Penuh . ') tidak ditemukan di sistem.');
    }
    
    $ID_APPROVER_L1_FINAL = $approverL1User->id;


    // --- 1c. MENCARI ID APPROVER L3 (Manager/PJS) DARI PILIHAN FORM ---
    $jabatanL3Form = $validated['jabatan_approver_l3']; 
    
    // 🛑 KOREKSI: MENGGUNAKAN PENCARIAN KETAT (STRICT WHERE)
    // Asumsi: File buat.blade.php sudah mengirim string LENGKAP seperti di DB.
    $approverL3User = User::where('peran', 'manager')
                            ->where('jabatan_default', $jabatanL3Form) // PENCARIAN KETAT
                            ->first();

    if (!$approverL3User) {
        Log::error("Tidak ada akun Approver L3 terdaftar untuk Jabatan: " . $jabatanL3Form);
        return redirect()->back()->withInput()->with('error', '❌ Akun persetujuan L3 untuk jabatan **' . $jabatanL3Form . '** tidak ditemukan di sistem. Pastikan akun tersebut sudah diregister dengan Peran **manager** dan Jabatan Default yang sama persis.');
    }
    
    $ID_APPROVER_L3_FINAL = $approverL3User->id; // ID FINAL L3 DIDAPATKAN


    // 2. PEMROSESAN TANDA TANGAN DIGITAL
    $ttd_pemohon_base64 = $request->ttd_pemohon;
    $ttd_pemohon_base64 = preg_replace('/^data:image\/(png|jpg|jpeg);base64,/', '', $ttd_pemohon_base64);
    $ttd_pemohon_binary = base64_decode($ttd_pemohon_base64);

    $fileName = 'ttd/ttd_pemohon_' . time() . '_' . Auth::id() . '.png';
    $ttd_path = null;
    
    try {
        Storage::disk('public')->put($fileName, $ttd_pemohon_binary);
        $ttd_path = $fileName;
    } catch (\Exception $e) {
        Log::error("Gagal menyimpan TTD Pemohon: " . $e->getMessage());
        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan file Tanda Tangan. Silakan coba lagi.');
    }

    // 3. PREPARASI NOMOR IZIN BARU BERDASARKAN NOMOR URUT MAKSIMUM
    $tahun = Carbon::now()->year; 
    
    $nomorTerakhir = DB::table('izin_masuk')
                       ->whereYear('created_at', $tahun)
                       ->orderBy('nomor_izin', 'desc')
                       ->value('nomor_izin'); 
    
    $nomorUrut = 1;

    if ($nomorTerakhir) {
        $nomorUrutTerakhir = intval(substr($nomorTerakhir, 0, 8));
        $nomorUrut = $nomorUrutTerakhir + 1;
    }

    $nomor_izin_final = sprintf("%08d/GP-ITBJM/%d", $nomorUrut, $tahun);


    // 4. SIMPAN DATA UTAMA KE DATABASE
    try {
        DB::beginTransaction();

        DB::table('izin_masuk')->insert([
            'nomor_izin' => $nomor_izin_final, 
            'tanggal' => $validated['tanggal'],
            'jenis_izin' => $validated['jenis_izin'],
            'fungsi_pemohon' => $validated['fungsi_pemohon'],
            'nomor_telepon_pemohon' => $request->nomor_telepon_pemohon,
            'jabatan_fungsi_pemohon' => Auth::user()->jabatan_default ?? $validated['fungsi_pemohon'], 
            'dasar_pekerjaan' => $validated['dasar_pekerjaan'],
            'perihal' => $validated['perihal'],
            'nama_perusahaan' => $validated['nama_perusahaan'],
            'tujuan_pengiriman' => $validated['tujuan_pengiriman'],
            'pembawa_barang' => $validated['pembawa_barang'],
            'nomor_kendaraan' => $validated['nomor_kendaraan'],
            'ttd_pemohon_path' => $ttd_path, 
            'id_pemohon' => Auth::id(), 
            'status' => 'Menunggu L1',
            
            // --- ID APPROVER (OTORISASI) ---
            'id_approver_l1' => $ID_APPROVER_L1_FINAL, 
            'id_approver_l2' => $ID_APPROVER_L2_GENERIK, 
            'id_approver_l3' => $ID_APPROVER_L3_FINAL, // MENGGUNAKAN ID L3 DINAMIS

            // --- FIELD MANUAL APPROVER (DOKUMENTASI CETAK) ---
            'jabatan_approver_l1' => $validated['jabatan_approver_l1'],
            'nama_approver_l1' => $validated['nama_approver_l1'],
            'jabatan_approver_l2' => $validated['jabatan_approver_l2'],
            'nama_approver_l2' => $validated['nama_approver_l2'],
            'jabatan_approver_l3' => $validated['jabatan_approver_l3'],
            'nama_approver_l3' => $validated['nama_approver_l3'],

            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        
        $izinId = DB::table('izin_masuk')
                   ->where('nomor_izin', $nomor_izin_final)
                   ->value('id');

        if (!$izinId) {
            throw new \Exception("Gagal mendapatkan ID Izin Masuk yang baru disimpan.");
        }

        // 5. SIMPAN DETAIL BARANG
        foreach ($validated['nama_item'] as $index => $nama_item) {
            DB::table('detail_barang')->insert([
                'izin_masuk_id' => $izinId, 
                'nama_item' => $nama_item,
                'qty' => $validated['qty'][$index],
                'satuan' => $validated['satuan'][$index],
                'keterangan_item' => $validated['keterangan_item'][$index],
            ]);
        }

        DB::commit();

        return redirect()->route('izin.riwayat')->with('success', "✅ Pengajuan Gatepass **#$nomor_izin_final** berhasil disimpan dan menunggu persetujuan L1 dari fungsi **$fungsiPemohon**.");

    } catch (\Exception $e) {
        DB::rollBack();
        if (isset($ttd_path)) {
            Storage::disk('public')->delete($ttd_path);
        }
        Log::error("Error saat menyimpan Gatepass: " . $e->getMessage());

        return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem saat menyimpan pengajuan. ' . $e->getMessage());
    }
}

    /** ----------------------------
     * 4. Halaman Dashboard (Perbaikan Logika Tugas Persetujuan)
     * ---------------------------- */
   public function dashboard()
{
    $user = Auth::user();
    $peran = $user->peran;
    $userId = $user->id;

    // Default data agar tidak error di view
    $data = [
        'userPeran' => $peran,
        'total_izin_masuk' => 0,
        'total_pengguna' => 0,
        'approval_count' => 0,
        'pending_izins' => collect(),
        'latest_user_submissions' => collect(),
        'riwayat_izin_count' => 0,
        'tugas_persetujuan_terbaru' => collect(),
    ];

    // ===================================================
    // PEMBUAT GATEPASS (PEMOHON)
    // ===================================================
    // KOREKSI 1: Menambahkan 'pemohon' ke dalam kondisi IF
    if ($peran === 'pembuat_gatepass' || $peran === 'pemohon') { 
        $statusDiproses = ['Menunggu L1', 'Menunggu L2', 'Menunggu L3'];

        // KOREKSI 2: Mengganti 'id_pembuat' menjadi 'id_pemohon' di semua kueri
        $data['pending_izins'] = IzinMasuk::where('id_pemohon', $userId)
            ->whereIn('status', $statusDiproses)
            ->orderBy('created_at', 'desc')
            ->get();

        $data['latest_user_submissions'] = IzinMasuk::where('id_pemohon', $userId)
            ->where('status', 'Disetujui Final')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $data['riwayat_izin_count'] = IzinMasuk::where('id_pemohon', $userId)
            ->where('status', 'Disetujui Final')
            ->count();
    }

    // ===================================================
    // APPROVER (L1 / L2 / L3 / SECURITY / TEKNIK / HSSE)
    // ===================================================
    elseif (in_array($peran, ['atasan_pemohon', 'security', 'manager', 'teknik', 'hsse'])) {

        $query = IzinMasuk::with('pemohon') // relasi dari model
            ->where(function ($q) use ($userId) {
                $q->orWhere(function ($subq) use ($userId) {
                    $subq->where('id_approver_l1', $userId)
                         ->where('status', 'Menunggu L1');
                })
                ->orWhere(function ($subq) use ($userId) {
                    $subq->where('id_approver_l2', $userId)
                         ->where('status', 'Menunggu L2');
                })
                ->orWhere(function ($subq) use ($userId) {
                    $subq->where('id_approver_l3', $userId)
                         ->where('status', 'Menunggu L3');
                });
            });

        $data['approval_count'] = $query->count();
        $data['tugas_persetujuan_terbaru'] = (clone $query)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    // ===================================================
    // ADMIN
    // ===================================================
    elseif ($peran === 'admin') {
        $data['total_izin_masuk'] = IzinMasuk::count();
        $data['total_pengguna'] = User::count();
    }

    return view('dashboard', $data);
}


    /** ----------------------------
     * 5. Fungsi 'daftarPersetujuan' (Menampilkan daftar yang perlu disetujui)
     * ---------------------------- */
    public function daftarPersetujuan()
    {
        $user = Auth::user();
        $userId = $user->id; 
        $peran = $user->peran;

        $query = IzinMasuk::with('pemohon'); 
        
        if (in_array($peran, ['atasan_pemohon', 'security', 'manager', 'teknik', 'hsse'])) {
            
            // Logika Sequential sama seperti di dashboard
            $query->where(function($q) use ($userId) {
                
                // 1. Tugas L1
                $q->orWhere(function($subq) use ($userId) {
                    $subq->where('id_approver_l1', $userId)
                         ->where('status', 'Menunggu L1'); 
                })
                // 2. Tugas L2
                ->orWhere(function($subq) use ($userId) {
                    $subq->where('id_approver_l2', $userId)
                         ->where('status', 'Menunggu L2');
                })
                // 3. Tugas L3
                ->orWhere(function($subq) use ($userId) {
                    $subq->where('id_approver_l3', $userId)
                         ->where('status', 'Menunggu L3');
                });
            });
        }
        
        $izins = $query->latest()->get(); // Ambil data
        
        return view('izin.persetujuan', compact('izins'));
    }

    /** ----------------------------
     * 8. Fungsi 'show' (Detail Izin Masuk)
     * ---------------------------- */
    public function detail($id)
    {
        $izin = IzinMasuk::with([
            'pemohon', 
            'detailBarang', 
            'approverL1', 
            'approverL2', 
            'approverL3', 
        ])->findOrFail($id);

        $userId = Auth::id();
        $userPeran = Auth::user()->peran;

        // KOREKSI UTAMA: Cek Kompatibilitas Pemilik Data (id_pemohon)
        $isCreator = ($izin->id_pemohon === $userId);
        
        $isApprover = ($izin->id_approver_l1 === $userId) || 
                      ($izin->id_approver_l2 === $userId) || 
                      ($izin->id_approver_l3 === $userId);
        
        // Admin, Teknik, HSSE selalu boleh melihat
        $isAdminOrNewRole = in_array($userPeran, ['admin', 'teknik', 'hsse']); 
        
        if ($isCreator || $isApprover || $isAdminOrNewRole) {
            
            $l1_status = $izin->tgl_persetujuan_l1 ? 'approved' : 'pending';
            $l2_status = $izin->tgl_persetujuan_l2 ? 'approved' : 
                          ($l1_status == 'approved' ? 'pending' : 'waiting'); 
            $l3_status = $izin->tgl_persetujuan_l3 ? 'approved' : 
                          ($l2_status == 'approved' ? 'pending' : 'waiting'); 
            
            // Tambahkan cek rejected
            if (($izin->l1_rejected ?? false)) $l1_status = 'rejected';
            if (($izin->l2_rejected ?? false)) $l2_status = 'rejected';
            if (($izin->l3_rejected ?? false)) $l3_status = 'rejected';
            
            return view('izin.detail', compact('izin', 'l1_status', 'l2_status', 'l3_status'));
        }

        return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses untuk melihat detail Gatepass ini.');
    }

    /** ----------------------------
     * 9. Fungsi 'riwayat' (Riwayat Pengajuan Pemohon)
     * ---------------------------- */
    public function riwayat()
    {
        $userId = Auth::id();

        // KOREKSI: Gunakan 'id_pemohon' untuk pengambilan riwayat
        $riwayatIzin = IzinMasuk::where('id_pemohon', $userId)
                                 ->orderBy('created_at', 'desc')
                                 ->paginate(10); 

        return view('izin.riwayat', compact('riwayatIzin'));
    }

    /** ----------------------------
     * 10. Fungsi 'batal' (Membatalkan Pengajuan)
     * ---------------------------- */
    public function batal($id)
    {
        // ✅ REVISI: Cek id_pemohon, bukan id_pembuat (karena id_pemohon yang digunakan di form)
        $izin = IzinMasuk::where('id_pemohon', Auth::id())->findOrFail($id);

        if ($izin->status == 'Menunggu L1') {
            $izin->status = 'Dibatalkan';
            $izin->save();
            return redirect()->route('izin.riwayat')->with('success', 'Pengajuan Gatepass berhasil dibatalkan.');
        }

        return redirect()->route('izin.riwayat')->with('error', 'Gatepass tidak dapat dibatalkan karena sudah dalam status ' . $izin->status);
    }
    
    /** ----------------------------
     * 11. Fungsi 'cetak' (Cetak Izin Masuk)
     * ---------------------------- */
   public function cetak(IzinMasuk $izin) 
{
    // Menggunakan eager loading untuk memuat semua relasi yang digunakan di view
    // Relasi yang dibutuhkan: approverL1, approverL2, approverL3, detailBarang
    
    $izin = $izin->load([
        'approverL1', 
        'approverL2', 
        'approverL3', 
        'detailBarang' 
    ]);

    // 🛑 KOREKSI KRITIS: Hapus atau ganti karakter yang tidak diizinkan ('/')
    // Jika $izin->nomor_izin = "00000001/GP-ITBJM/2025", ini akan diubah menjadi "00000001-GP-ITBJM-2025"
    $safeNomorIzin = str_replace(['/', '\\'], '-', ($izin->nomor_izin ?? $izin->id));

    // Buat nama file yang aman untuk HTTP Header
    $fileName = 'Gatepass-' . $safeNomorIzin . '.pdf';

    // Panggil view yang sudah benar ('izin.cetak')
    $pdf = PDF::loadView('izin.cetak', compact('izin'));
    
    // Stream PDF menggunakan nama file yang sudah difilter
    return $pdf->stream($fileName);
}

    public function prosesPersetujuan(Request $request, IzinMasuk $izin)
    {
        $user = Auth::user();

        $request->validate([
            'level' => ['required', 'string', Rule::in(['L1', 'L2', 'L3'])], 
            'action' => ['required', 'string', Rule::in(['approve', 'reject'])], 
            'catatan_penolakan' => 'nullable|string|max:500', 
        ]);
        
        $level = $request->input('level');
        $action = $request->input('action');
        $catatan = $request->input('catatan_penolakan');
        
        if ($izin->status === 'Ditolak' || $izin->status === 'Disetujui Final' || $izin->status === 'Dibatalkan') {
            return redirect()->route('izin.persetujuan')->with('error', 'Gatepass sudah selesai diproses.');
        }

        $tgl_kolom = 'tgl_persetujuan_' . strtolower($level);
        $reject_kolom = strtolower($level) . '_rejected';
        $ttd_kolom = 'ttd_approver_' . strtolower($level); 

        // 3. Cek Giliran Approver (Termasuk Manager Override Logic)
        $isMyTurn = false;
        
        // Logika Sequential Asli
        if ($level === 'L1' && $izin->id_approver_l1 == $user->id && is_null($izin->tgl_persetujuan_l1)) {
            $isMyTurn = true;
        } elseif ($level === 'L2' && $izin->id_approver_l2 == $user->id && $izin->tgl_persetujuan_l1 && is_null($izin->tgl_persetujuan_l2)) {
            $isMyTurn = true;
        } elseif ($level === 'L3' && $izin->id_approver_l3 == $user->id && $izin->tgl_persetujuan_l2 && is_null($izin->tgl_persetujuan_l3)) {
            $isMyTurn = true;
        }

        // KOREKSI TAMBAHAN: MANAGER (L3) OVERRIDE Aksi Timing
        if ($user->peran === 'manager' && $izin->id_approver_l3 == $user->id && $level === 'L3' && is_null($izin->tgl_persetujuan_l3)) {
             $isMyTurn = true;
        }
        
        if (!$isMyTurn) {
            return redirect()->route('izin.persetujuan')->with('error', 'Anda bukan approver yang bertugas pada giliran ini atau Gatepass belum mencapai giliran Anda.');
        }

        DB::beginTransaction();

        try {
            $successMessage = 'Proses persetujuan berhasil.';

            if ($action === 'approve') {
                $izin->$tgl_kolom = Carbon::now();
                $izin->$reject_kolom = false;
                $successMessage = '✅ Persetujuan Level ' . substr($level, 1) . ' berhasil dicatat.';
                
                // === IMPLEMENTASI TANDA TANGAN TEMPLATE PNG ===
                if ($user->signature_path) {
                    $izin->$ttd_kolom = $user->signature_path; // Menyimpan Path TTD Template PNG
                } else {
                    Log::warning("User ID {$user->id} menyetujui Gatepass tanpa template tanda tangan.");
                }

            } elseif ($action === 'reject') {
                if (empty($catatan)) {
                    throw ValidationException::withMessages(['catatan_penolakan' => 'Catatan penolakan wajib diisi jika Anda menolak pengajuan.']);
                }
                
                $izin->$tgl_kolom = null; 
                $izin->$reject_kolom = true;
                $izin->$ttd_kolom = null; 
                
                $currentNotes = json_decode($izin->rejection_notes ?? '[]', true);
                $currentNotes[$level] = $catatan;
                $izin->rejection_notes = json_encode($currentNotes);

                $successMessage = '❌ Gatepass berhasil Ditolak.';
            }
            
            $newStatus = $this->checkGatepassStatus($izin);
            $izin->status = $newStatus; 
            
            $izin->save();
            DB::commit();

            $nomorIzinTampil = $izin->nomor_izin ?? 'N/A';

            if ($newStatus === 'Ditolak') {
                 return redirect()->route('izin.persetujuan')->with('error', "❌ Gatepass #{$nomorIzinTampil} telah resmi Ditolak.");
            } elseif ($newStatus === 'Disetujui Final') {
                return redirect()->route('izin.persetujuan')->with('success', "✅ Gatepass #{$nomorIzinTampil} berhasil disetujui penuh.");
            }
            
            return redirect()->route('izin.persetujuan')->with('success', $successMessage);

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saat memproses Gatepass: ' . $e->getMessage());
            return redirect()->route('izin.persetujuan')->with('error', '❌ Gagal memproses Gatepass. Silakan coba lagi.');
        }
    }
}