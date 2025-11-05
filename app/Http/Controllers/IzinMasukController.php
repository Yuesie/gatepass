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
    // Variabel list berikut dihapus karena tidak terpakai lagi di view yang baru.
    
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
   public function simpan(Request $request)
    {
        // --- 0. ID APPROVER L2 & L3 GENERIK (Global/Sentral) ---
        // GANTI ID INI DENGAN ID AKUN GENERIK YANG SESUAI DI DATABASE ANDA!
        $ID_APPROVER_L2_GENERIK = 102; // Akun generik Security (Role 'security')
        $ID_APPROVER_L3_GENERIK = 103; // Akun generik Manager/PJS (Role 'manager')

        // 1. VALIDASI DATA
        $validated = $request->validate([
            // Data Umum Izin
            'tanggal' => 'required|date',
            'jenis_izin' => 'required|in:masuk,keluar',
            'fungsi_pemohon' => 'required|string|max:255',
            'jabatan_fungsi_pemohon' => 'required|string|max:255',
            'dasar_pekerjaan' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',

            // Data Pengiriman/Kendaraan
            'nama_perusahaan' => 'required|string|max:255',
            'tujuan_pengiriman' => 'required|string|max:255',
            'pembawa_barang' => 'required|string|max:255',
            'nomor_kendaraan' => 'nullable|string|max:255',
            'keterangan_umum' => 'nullable|string',
            
            // TANDA TANGAN DIGITAL
            'ttd_pemohon' => 'required|string', 

            // FIELD MANUAL APPROVER (DOKUMENTASI CETAK)
            'jabatan_approver_l1' => 'required|string|max:100',
            'nama_approver_l1' => 'required|string|max:100',
            'jabatan_approver_l2' => 'required|string|max:100',
            'nama_approver_l2' => 'required|string|max:100',
            'jabatan_approver_l3' => 'required|string|max:100',
            'nama_approver_l3' => 'required|string|max:100',

            // Daftar Barang
            'nama_item.*' => 'required|string|max:255',
            'qty.*' => 'required|integer|min:1',
            'satuan.*' => 'required|string|max:50',
            'keterangan_item.*' => 'required|string|max:255',
        ], [
            'ttd_pemohon.required' => 'Data Tanda Tangan kosong. Mohon berikan tanda tangan.',
            'jabatan_fungsi_pemohon.required' => 'Jabatan Fungsi Pemohon wajib diisi.',
            // ... (Custom error messages lainnya) ...
        ]);

        // --- 1a. MENENTUKAN ID APPROVER L1 SECARA DINAMIS BERDASARKAN FUNGSI ---
        $fungsiPemohon = $validated['fungsi_pemohon'];
        
        $approverL1User = User::where('peran', 'atasan_pemohon')
                              // Mencari user yang peran L1-nya sesuai dengan Fungsi Pemohon
                              ->where('jabatan_default', $fungsiPemohon) 
                              ->first();

        // Cek apakah akun generik L1 ditemukan
        if (!$approverL1User) {
            Log::error("Tidak ada akun Approver L1 generik terdaftar untuk Fungsi: " . $fungsiPemohon);
            return redirect()->back()->withInput()->with('error', '❌ Akun persetujuan L1 untuk fungsi **' . $fungsiPemohon . '** tidak ditemukan di sistem. Hubungi administrator.');
        }
        
        $ID_APPROVER_L1_FINAL = $approverL1User->id;

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

        // 3. PREPARASI NOMOR IZIN BARU
        $tahun = Carbon::now()->year; 
        $nomor_izin_final = null; 

        // 4. SIMPAN DATA UTAMA KE DATABASE & UPDATE NOMOR IZIN
        try {
            DB::beginTransaction();

            // 4a. Simpan Data dengan Placeholder untuk mendapatkan ID
            $izinId = DB::table('izin_masuk')->insertGetId([
                'nomor_izin' => 'TEMP_NUM', 
                'tanggal' => $validated['tanggal'],
                'jenis_izin' => $validated['jenis_izin'],
                'fungsi_pemohon' => $validated['fungsi_pemohon'],
                'jabatan_fungsi_pemohon' => $validated['jabatan_fungsi_pemohon'], 
                'dasar_pekerjaan' => $validated['dasar_pekerjaan'],
                'perihal' => $validated['perihal'],
                'nama_perusahaan' => $validated['nama_perusahaan'],
                'tujuan_pengiriman' => $validated['tujuan_pengiriman'],
                'pembawa_barang' => $validated['pembawa_barang'],
                'nomor_kendaraan' => $validated['nomor_kendaraan'],
                'keterangan_umum' => $validated['keterangan_umum'],
                'ttd_pemohon_path' => $ttd_path, 
                'id_pemohon' => Auth::id(), 
                'status' => 'Menunggu L1',
                
                // --- ID APPROVER (OTORISASI) ---
                'id_approver_l1' => $ID_APPROVER_L1_FINAL,       // DINAMIS BERDASARKAN FUNGSI
                'id_approver_l2' => $ID_APPROVER_L2_GENERIK,     // HARDCODED GENERIK
                'id_approver_l3' => $ID_APPROVER_L3_GENERIK,     // HARDCODED GENERIK

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

            // 4b. Buat dan Update Nomor Izin Final
            $nomor_izin_final = sprintf("%08d/GP-ITBJM/%d", $izinId, $tahun);
            
            DB::table('izin_masuk')
                ->where('id', $izinId)
                ->update(['nomor_izin' => $nomor_izin_final]);


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
        $data = [
            'total_izin_masuk' => 0,
            'approval_count' => 0, 
            'tugas_persetujuan_terbaru' => collect(), 
            'latest_user_submissions' => collect(), 
            'userPeran' => $peran, 
            'pending_izins' => collect(), 
            'riwayat_izin_count' => 0,
        ];

        // --- Logika untuk Dashboard Pembuat Gatepass ---
        // ✅ REVISI: Ganti 'pemohon' menjadi 'pembuat_gatepass'
        if ($peran == 'pembuat_gatepass') {
            $statusDiproses = ['Menunggu L1', 'Menunggu L2', 'Menunggu L3']; 
            
            // Ganti semua where('id_pembuat', $userId) menjadi where(function...)
            $data['pending_izins'] = IzinMasuk::where('id_pemohon', $userId) // Asumsi id_pemohon adalah pembuat gatepass
                                         ->whereIn('status', $statusDiproses) 
                                         ->orderBy('created_at', 'desc')
                                         ->get();
            
            // Terapkan filter yang sama pada latest_user_submissions
            $data['latest_user_submissions'] = IzinMasuk::where('id_pemohon', $userId) 
                                                 ->where('status', 'Disetujui Final') 
                                                 ->orderBy('created_at', 'desc')
                                                 ->limit(5)
                                                 ->get();
            
            // Terapkan filter yang sama pada total count
            $data['riwayat_izin_count'] = IzinMasuk::where('id_pemohon', $userId)->count();
        }
        
        // --- Logika untuk Dashboard Approver (L1, L2, L3, Teknik, HSSE) ---
        elseif (in_array($peran, ['atasan_pemohon', 'security', 'manager', 'teknik', 'hsse'])) {
            
            $query = IzinMasuk::with('pembuat'); 

            $query->where(function($q) use ($userId) {
                
                // 1. Tugas L1: Gatepass status 'Menunggu L1' dan id_approver_l1 = user
                $q->orWhere(function($subq) use ($userId) {
                    $subq->where('id_approver_l1', $userId)
                         ->where('status', 'Menunggu L1'); // <-- KOREKSI STATUS
                })
                
                // 2. Tugas L2: L1 sudah setuju, status 'Menunggu L2', dan id_approver_l2 = user
                ->orWhere(function($subq) use ($userId) {
                    $subq->where('id_approver_l2', $userId)
                         ->where('status', 'Menunggu L2'); // <-- KOREKSI STATUS
                })
                
                // 3. Tugas L3: L2 sudah setuju, status 'Menunggu L3', dan id_approver_l3 = user
                ->orWhere(function($subq) use ($userId) {
                    $subq->where('id_approver_l3', $userId)
                         ->where('status', 'Menunggu L3'); // <-- KOREKSI STATUS
                });
            });

            $data['approval_count'] = $query->count();
            $data['tugas_persetujuan_terbaru'] = (clone $query)->latest()->limit(5)->get();
        }

        // --- Logika untuk Dashboard Admin ---
        elseif ($peran == 'admin') {
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

        $query = IzinMasuk::with('pembuat', 'pemohon'); 
        
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
    public function cetak($id)
    {
        $izin = IzinMasuk::with('detailBarang', 'pembuat', 'approverL1', 'approverL2', 'approverL3')->findOrFail($id);

        if ($izin->status !== 'Disetujui Final') {
            return redirect()->back()->with('error', 'Gatepass hanya bisa dicetak setelah status Disetujui Final.');
        }

        $safeFileName = str_replace('/', '-', $izin->nomor_izin);
        
        $pdf = Pdf::loadView('izin.cetak', compact('izin'));

        return $pdf->download('Gatepass-' . $safeFileName . '.pdf');
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