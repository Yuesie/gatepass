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
use Illuminate\Support\Str;
// 🔥 IMPOR BARU UNTUK FIREBASE 🔥
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class IzinMasukController extends Controller
{
    // ID APPROVER L2 GENERIK (SECURITY)
    private const ID_APPROVER_L2_GENERIK = 8; // ID Akun generik Security (Peran 'security')

    /** ----------------------------
     * 1. Formulir Buat Gatepass
     * ---------------------------- */
    public function buat()
    {
        $approverL1 = collect([]); 
        $approverL2 = collect([]);
        $approverL3 = collect([]);
        
        return view('izin.buat', compact('approverL1', 'approverL2', 'approverL3')); 
    }
    
    /** ----------------------------
     * 2. Helper Cek Status Gatepass
     * ---------------------------- */
    private function checkGatepassStatus(IzinMasuk $izin): string
    {
        // 🔥 PERUBAHAN PARALEL: Logika Pengecekan Status
        
        // 1. Jika ada satu saja yang menolak, status final adalah Ditolak.
        if (($izin->l1_rejected || $izin->l2_rejected || $izin->l3_rejected) ?? false) {
            return 'Ditolak';
        }
        
        // 2. Jika L1, L2, DAN L3 sudah setuju, status final adalah Disetujui.
        if ($izin->tgl_persetujuan_l1 && $izin->tgl_persetujuan_l2 && $izin->tgl_persetujuan_l3) {
            return 'Disetujui Final';
        }
        
        // 3. Jika tidak ditolak dan belum disetujui semua, statusnya Menunggu.
        return 'Menunggu Persetujuan'; 
    }

    /** ----------------------------
     * 3. Helper untuk menyimpan TTD Base64
     * ---------------------------- */
    private function saveBase64Signature($base64_data, $unique_id, $folder = 'gatepass_signatures')
    {
        try {
            // Hapus header data URI (data:image/png;base64,...)
            $base64_image = substr($base64_data, strpos($base64_data, ',') + 1);
            $binary_data = base64_decode($base64_image);
            
            $filename = 'ttd_pemohon_' . $unique_id . '_' . Str::random(8) . '.png';
            $path = $folder . '/' . $filename;

            // Simpan file ke disk 'public'
            Storage::disk('public')->put($path, $binary_data);

            return $path;
        } catch (\Exception $e) {
            Log::error("Gagal menyimpan TTD Base64: " . $e->getMessage());
            return null;
        }
    }

    /** ----------------------------
     * 4. Fungsi 'simpan' (Menyimpan Pengajuan)
     * ---------------------------- */
    public function simpan(Request $request)
    {
        // 1. VALIDASI DATA
        try {
            $validated = $request->validate([
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
                'jabatan_approver_l3' => 'required|string|max:100',
                'nama_approver_l3' => 'required|string|max:100',
                'barang' => 'required|array|min:1',
                'barang.*.nama_item' => 'required|string|max:255',
                'barang.*.qty' => 'required|integer|min:1',
                'barang.*.satuan' => 'required|string|max:50',
                'barang.*.keterangan_item' => 'required|string|max:255',
                'barang.*.foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
            ], [
                'ttd_pemohon.required' => 'Data Tanda Tangan kosong. Mohon berikan tanda tangan.',
                'barang.required' => 'Minimal satu item barang wajib diisi.',
                'barang.*.foto.image' => 'File harus berupa gambar (jpeg, png, jpg).',
                'barang.*.foto.max' => 'Ukuran file foto maksimal 2MB.',
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        // 2. MENCARI ID APPROVER L1 & L3
        $ID_APPROVER_L1_FINAL = null;
        $ID_APPROVER_L3_FINAL = null;

        // Cari ID Approver L1
        $fungsiPemohon = $validated['fungsi_pemohon'];
        $jabatanApproverL1Penuh = match ($fungsiPemohon) {
            'MPS' => 'Spv II MPS', 'HSSE & FS' => 'SPV II HSSE & FS', 'RSD' => 'Sr Spv RSD',
            'QQ' => 'Spv I QQ', 'SSGA' => 'Spv I SSGA', default => null, 
        };

        if (!is_null($jabatanApproverL1Penuh)) {
            $approverL1User = User::where('peran', 'atasan_pemohon')
                                ->where('jabatan_default', $jabatanApproverL1Penuh) 
                                ->first();
            if ($approverL1User) {
                $ID_APPROVER_L1_FINAL = $approverL1User->id;
            }
        }
        if (is_null($ID_APPROVER_L1_FINAL)) {
            return redirect()->back()->withInput()->with('error', '❌ Akun persetujuan L1 generik untuk fungsi **' . $fungsiPemohon . '** tidak ditemukan di sistem.');
        }

        // Cari ID Approver L3
        $approverL3User = User::where('peran', 'manager')
                                ->where('jabatan_default', $validated['jabatan_approver_l3']) 
                                ->first();
        if ($approverL3User) {
            $ID_APPROVER_L3_FINAL = $approverL3User->id;
        }
        if (is_null($ID_APPROVER_L3_FINAL)) {
            return redirect()->back()->withInput()->with('error', '❌ Akun persetujuan L3 untuk jabatan **' . $validated['jabatan_approver_l3'] . '** tidak ditemukan di sistem.');
        }


        // 3. PREPARASI NOMOR IZIN & TTD PEMOHON
        $tahun = Carbon::now()->year; 
        
        $nomorTerakhir = DB::table('izin_masuk')
                            ->whereYear('created_at', $tahun)
                            ->orderBy('nomor_izin', 'desc')
                            ->value('nomor_izin'); 
        
        $nomorUrut = 1;

        if ($nomorTerakhir) {
            // Asumsi format: XXXXXXXX/GP-ITBJM/YYYY
            $nomorUrutTerakhir = intval(substr($nomorTerakhir, 0, 8)); 
            $nomorUrut = $nomorUrutTerakhir + 1;
        }

        $nomor_izin_final = sprintf("%08d/GP-ITBJM/%d", $nomorUrut, $tahun);
        
        // Simpan TTD Pemohon Base64
        $ttd_path = $this->saveBase64Signature($request->ttd_pemohon, $nomor_izin_final);
        if (is_null($ttd_path)) {
            return redirect()->back()->withInput()->with('error', 'Gagal memproses Tanda Tangan. Silakan coba lagi.');
        }


        // 4. SIMPAN DATA KE DATABASE (MENGGUNAKAN TRANSAKSI)
        try {
            DB::beginTransaction();

            $izinId = DB::table('izin_masuk')->insertGetId([
                'nomor_izin' => $nomor_izin_final, 
                'tanggal' => $validated['tanggal'],
                'jenis_izin' => $validated['jenis_izin'],
                'fungsi_pemohon' => $validated['fungsi_pemohon'],
                'nomor_telepon_pemohon' => $validated['nomor_telepon_pemohon'],
                'jabatan_fungsi_pemohon' => Auth::user()->jabatan_default ?? $validated['fungsi_pemohon'], 
                'dasar_pekerjaan' => $validated['dasar_pekerjaan'],
                'perihal' => $validated['perihal'],
                'nama_perusahaan' => $validated['nama_perusahaan'],
                'tujuan_pengiriman' => $validated['tujuan_pengiriman'],
                'pembawa_barang' => $validated['pembawa_barang'],
                'nomor_kendaraan' => $validated['nomor_kendaraan'],
                'ttd_pemohon_path' => $ttd_path, 
                'id_pemohon' => Auth::id(), 
                'status' => 'Menunggu Persetujuan', // 🔥 PERUBAHAN PARALEL (dari 'Menunggu L1')
                
                // ID APPROVER (OTORISASI)
                'id_approver_l1' => $ID_APPROVER_L1_FINAL, 
                'id_approver_l2' => self::ID_APPROVER_L2_GENERIK, 
                'id_approver_l3' => $ID_APPROVER_L3_FINAL, 

                // FIELD MANUAL APPROVER (DOKUMENTASI CETAK)
                'jabatan_approver_l1' => $validated['jabatan_approver_l1'],
                'nama_approver_l1' => $validated['nama_approver_l1'],
                'jabatan_approver_l2' => $validated['jabatan_approver_l2'],
                'nama_approver_l2' => $validated['nama_approver_l2'],
                'jabatan_approver_l3' => $validated['jabatan_approver_l3'],
                'nama_approver_l3' => $validated['nama_approver_l3'],

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // 5. SIMPAN DETAIL BARANG DAN FOTO
            $detailBarangData = [];
            foreach ($validated['barang'] as $index => $item) {
                $fotoPath = null;
                $file = $request->file("barang.{$index}.foto"); // Ambil file dari request

                if ($file && $file->isValid()) {
                    // Simpan foto ke storage (folder 'gatepass_photos')
                    $fotoPath = $file->store('gatepass_photos', 'public');
                }

                $detailBarangData[] = [
                    'izin_masuk_id' => $izinId, 
                    'nama_item' => $item['nama_item'],
                    'qty' => $item['qty'],
                    'satuan' => $item['satuan'],
                    'keterangan_item' => $item['keterangan_item'],
                    'foto_path' => $fotoPath, // Simpan path foto
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
            
            DB::table('detail_barang')->insert($detailBarangData);

            DB::commit();

            // 🔥 PERUBAHAN PARALEL: Kirim Notif ke L1, L2, dan L3
            $pemohon = Auth::user();
            $urlTujuan = route('izin.persetujuan');

            // Kirim ke L1
            if ($ID_APPROVER_L1_FINAL) {
                $this->sendFcmNotification(
                    $ID_APPROVER_L1_FINAL,
                    'Tugas Persetujuan Baru!',
                    "Gatepass #{$nomor_izin_final} dari {$pemohon->name} menunggu persetujuan L1 Anda.",
                    $urlTujuan
                );
            }
            
            // Kirim ke L2
            $this->sendFcmNotification(
                self::ID_APPROVER_L2_GENERIK, // ID L2
                'Tugas Persetujuan Baru!',
                "Gatepass #{$nomor_izin_final} dari {$pemohon->name} menunggu persetujuan L2 Anda.",
                $urlTujuan
            );

            // Kirim ke L3
            if ($ID_APPROVER_L3_FINAL) {
                $this->sendFcmNotification(
                    $ID_APPROVER_L3_FINAL,
                    'Tugas Persetujuan Baru!',
                    "Gatepass #{$nomor_izin_final} dari {$pemohon->name} menunggu persetujuan L3 Anda.",
                    $urlTujuan
                );
            }

            return redirect()->route('izin.riwayat')->with('success', "✅ Pengajuan Gatepass **#$nomor_izin_final** berhasil disimpan dan menunggu persetujuan.");

        } catch (\Exception $e) {
            DB::rollBack();
            // Hapus TTD jika transaksi gagal
            if (isset($ttd_path)) {
                Storage::disk('public')->delete($ttd_path);
            }
            
            Log::error("Error saat menyimpan Gatepass: " . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem saat menyimpan pengajuan. ' . $e->getMessage());
        }
    }

    /** ----------------------------
     * 5. Halaman Dashboard
     * ---------------------------- */
    public function dashboard()
    {
        $user = Auth::user();
        $peran = $user->peran;
        $userId = $user->id;

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

        // PEMBUAT GATEPASS (PEMOHON)
        if ($peran === 'pembuat_gatepass' || $peran === 'pemohon') { 
            // 🔥 PERUBAHAN PARALEL
            $statusDiproses = ['Menunggu Persetujuan'];

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

        // APPROVER (L1 / L2 / L3 / SECURITY / TEKNIK / HSSE)
        elseif (in_array($peran, ['atasan_pemohon', 'security', 'manager', 'teknik', 'hsse'])) {

            // 🔥 PERUBAHAN PARALEL: Query baru untuk paralel
            $query = IzinMasuk::with('pemohon')
                ->where('status', 'Menunggu Persetujuan') // Hanya tampilkan yang statusnya menunggu
                ->where(function($q) use ($userId) {
                    
                    // Tampilkan ke L1 JIKA dia belum setuju
                    $q->orWhere(function($subq) use ($userId) {
                        $subq->where('id_approver_l1', $userId)
                             ->whereNull('tgl_persetujuan_l1');
                    });
                    
                    // Tampilkan ke L2 JIKA dia belum setuju
                    $q->orWhere(function($subq) use ($userId) {
                        $subq->where('id_approver_l2', $userId)
                             ->whereNull('tgl_persetujuan_l2');
                    });
                    
                    // Tampilkan ke L3 JIKA dia belum setuju
                    $q->orWhere(function($subq) use ($userId) {
                        $subq->where('id_approver_l3', $userId)
                             ->whereNull('tgl_persetujuan_l3');
                    });
                });


            $data['approval_count'] = $query->count();
            $data['tugas_persetujuan_terbaru'] = (clone $query)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        // ADMIN
        elseif ($peran === 'admin') {
            $data['total_izin_masuk'] = IzinMasuk::count();
            $data['total_pengguna'] = User::count();
        }

        return view('dashboard', $data);
    }


    /** ----------------------------
     * 6. Fungsi 'daftarPersetujuan'
     * ---------------------------- */
    public function daftarPersetujuan()
    {
        $user = Auth::user();
        $userId = $user->id; 
        $peran = $user->peran;

        $query = IzinMasuk::with('pemohon'); 
        
        if (in_array($peran, ['atasan_pemohon', 'security', 'manager', 'teknik', 'hsse'])) {
            
            // 🔥 PERUBAHAN PARALEL: Query baru untuk paralel
            $query->where('status', 'Menunggu Persetujuan') // Hanya tampilkan yang statusnya menunggu
                ->where(function($q) use ($userId) {
                    
                    // Tampilkan ke L1 JIKA dia belum setuju
                    $q->orWhere(function($subq) use ($userId) {
                        $subq->where('id_approver_l1', $userId)
                             ->whereNull('tgl_persetujuan_l1');
                    });
                    
                    // Tampilkan ke L2 JIKA dia belum setuju
                    $q->orWhere(function($subq) use ($userId) {
                        $subq->where('id_approver_l2', $userId)
                             ->whereNull('tgl_persetujuan_l2');
                    });
                    
                    // Tampilkan ke L3 JIKA dia belum setuju
                    $q->orWhere(function($subq) use ($userId) {
                        $subq->where('id_approver_l3', $userId)
                             ->whereNull('tgl_persetujuan_l3');
                    });
                });
        }
        
        $izins = $query->latest()->get(); 
        
        return view('izin.persetujuan', compact('izins'));
    }

    /** ----------------------------
     * 7. Fungsi 'detail'
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

        $isCreator = ($izin->id_pemohon === $userId);
        
        $isApprover = ($izin->id_approver_l1 === $userId) || 
                        ($izin->id_approver_l2 === $userId) || 
                        ($izin->id_approver_l3 === $userId);
        
        $isAdminOrNewRole = in_array($userPeran, ['admin', 'teknik', 'hsse']); 
        
        if ($isCreator || $isApprover || $isAdminOrNewRole) {
            
            // 🔥 PERUBAHAN PARALEL: Status tidak lagi berurutan ('waiting')
            
            $l1_status = 'pending';
            if ($izin->tgl_persetujuan_l1) $l1_status = 'approved';
            if ($izin->l1_rejected) $l1_status = 'rejected';

            $l2_status = 'pending';
            if ($izin->tgl_persetujuan_l2) $l2_status = 'approved';
            if ($izin->l2_rejected) $l2_status = 'rejected';

            $l3_status = 'pending';
            if ($izin->tgl_persetujuan_l3) $l3_status = 'approved';
            if ($izin->l3_rejected) $l3_status = 'rejected';
            
            return view('izin.detail', compact('izin', 'l1_status', 'l2_status', 'l3_status'));
        }

        return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses untuk melihat detail Gatepass ini.');
    }

    /** ----------------------------
     * 8. Fungsi 'riwayat'
     * ---------------------------- */
    public function riwayat()
    {
        $userId = Auth::id();

        $riwayatIzin = IzinMasuk::where('id_pemohon', $userId)
                                ->orderBy('created_at', 'desc')
                                ->paginate(10); 

        return view('izin.riwayat', compact('riwayatIzin'));
    }

    /** ----------------------------
     * 9. Fungsi 'batal'
     * ---------------------------- */
    public function batal($id)
    {
        $izin = IzinMasuk::where('id_pemohon', Auth::id())->findOrFail($id);

        // 🔥 PERUBAHAN PARALEL
        if ($izin->status == 'Menunggu Persetujuan') {
            $izin->status = 'Dibatalkan';
            $izin->save();
            return redirect()->route('izin.riwayat')->with('success', 'Pengajuan Gatepass berhasil dibatalkan.');
        }

        return redirect()->route('izin.riwayat')->with('error', 'Gatepass tidak dapat dibatalkan karena sudah dalam status ' . $izin->status);
    }
    
    /** ----------------------------
     * 10. Fungsi 'cetak'
     * ---------------------------- */
    public function cetak(IzinMasuk $izin) 
    {
        $izin = $izin->load([
            'approverL1', 
            'approverL2', 
            'approverL3', 
            'detailBarang' 
        ]);

        $safeNomorIzin = str_replace(['/', '\\'], '-', ($izin->nomor_izin ?? $izin->id));

        $fileName = 'Gatepass-' . $safeNomorIzin . '.pdf';

        $pdf = PDF::loadView('izin.cetak', compact('izin'));
        
        return $pdf->stream($fileName);
    }

    /** ----------------------------
     * 11. Fungsi 'prosesPersetujuan'
     * ---------------------------- */
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

        // 🔥 PERUBAHAN PARALEL: Cek Giliran Approver (LOGIKA PARALEL MURNI)
        $isMyTurn = false;
        
        // Cek L1: Dia adalah L1, dan dia belum setuju ATAU tolak
        if ($level === 'L1' && $izin->id_approver_l1 == $user->id && is_null($izin->tgl_persetujuan_l1) && !$izin->l1_rejected) {
            $isMyTurn = true;
        // Cek L2: Dia adalah L2, dan dia belum setuju ATAU tolak
        } elseif ($level === 'L2' && $izin->id_approver_l2 == $user->id && is_null($izin->tgl_persetujuan_l2) && !$izin->l2_rejected) {
            $isMyTurn = true;
        // Cek L3: Dia adalah L3, dan dia belum setuju ATAU tolak
        } elseif ($level === 'L3' && $izin->id_approver_l3 == $user->id && is_null($izin->tgl_persetujuan_l3) && !$izin->l3_rejected) {
            $isMyTurn = true;
        }

        // HAPUS BLOK OVERRIDE L3 (sudah tidak diperlukan oleh logika paralel)
        
        if (!$isMyTurn) {
            // 🔥 PERUBAHAN PARALEL: Update pesan error
            return redirect()->route('izin.persetujuan')->with('error', 'Anda bukan approver yang bertugas atau Anda sudah memproses Gatepass ini.');
        }

        DB::beginTransaction();

        try {
            $successMessage = 'Proses persetujuan berhasil.';

            if ($action === 'approve') {
                $izin->$tgl_kolom = Carbon::now();
                $izin->$reject_kolom = false;
                $successMessage = '✅ Persetujuan Level ' . substr($level, 1) . ' berhasil dicatat.';
                
                // IMPLEMENTASI TANDA TANGAN TEMPLATE PNG
                if ($user->signature_path) {
                    $izin->$ttd_kolom = $user->signature_path; 
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
            
            // 🔥 PERUBAHAN PARALEL: Panggil helper baru yang sudah paralel
            $newStatus = $this->checkGatepassStatus($izin);
            $izin->status = $newStatus; 
            
            $izin->save();
            DB::commit();

            $nomorIzinTampil = $izin->nomor_izin ?? 'N/A';
            $pemohon = $izin->pemohon; // Ambil relasi pemohon

            // 🔥 PERUBAHAN PARALEL: Logika notifikasi ke Pemohon
            if (!$pemohon) {
                 Log::warning("Gatepass #{$izin->id} tidak memiliki relasi Pemohon, notif progres gagal.");
                 return redirect()->route('izin.persetujuan')->with('success', $successMessage);
            }

            if ($newStatus === 'Ditolak') {
                // Kirim notif ke Pemohon bahwa gatepass DITOLAK
                $this->sendFcmNotification(
                    $pemohon->id,
                    'Gatepass Ditolak',
                    "Gatepass #{$nomorIzinTampil} Anda ditolak oleh {$user->name} ({$level}).",
                    route('izin.detail', $izin->id)
                );
                return redirect()->route('izin.persetujuan')->with('error', "❌ Gatepass #{$nomorIzinTampil} telah resmi Ditolak.");
            
            } elseif ($newStatus === 'Disetujui Final') {
                // Kirim notif ke Pemohon bahwa gatepass SELESAI
                $this->sendFcmNotification(
                    $pemohon->id,
                    'Gatepass Disetujui Penuh!',
                    "Gatepass #{$nomorIzinTampil} Anda telah disetujui penuh.",
                    route('izin.detail', $izin->id)
                );
                return redirect()->route('izin.persetujuan')->with('success', "✅ Gatepass #{$nomorIzinTampil} berhasil disetujui penuh.");
            
            } else {
                // Jika Belum Final (misal L1 setuju, tapi L2 & L3 belum)
                // Kirim notif ke Pemohon bahwa ada progres
                $this->sendFcmNotification(
                    $pemohon->id,
                    'Progres Gatepass',
                    "Gatepass #{$nomorIzinTampil} telah disetujui oleh {$user->name} ({$level}).",
                    route('izin.detail', $izin->id)
                );
            }
            
            return redirect()->route('izin.persetujuan')->with('success', $successMessage);

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saat memproses Gatepass: ' . $e->getMessage());
            return redirect()->route('izin.persetojuan')->with('error', '❌ Gagal memproses Gatepass. Silakan coba lagi.');
        }
    }
    
    /** ----------------------------
     * 12. Helper Pengiriman Notifikasi FCM
     * ---------------------------- */
   protected function sendFcmNotification(int $targetUserId, string $title, string $body, ?string $url = null)
{
    $targetUser = User::find($targetUserId); 

    if (!$targetUser || !$targetUser->fcm_token) {
        Log::warning("FCM Skip: User ID {$targetUserId} tidak memiliki Token FCM.");
        return false;
    }

    try {
        $factory = (new Factory)->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));
        $messaging = $factory->createMessaging();

        // 👇 PASTIKAN MENGGUNAKAN toToken() BUKAN withToken() 👇
        $message = CloudMessage::new()
            ->toToken($targetUser->fcm_token) // INI YANG BENAR
            ->withNotification([
                'title' => $title,
                'body' => $body,
            ])
            ->withData([
                'click_action' => $url ?? route('dashboard'),
            ]);

        $messaging->send($message);
        Log::info("✅ FCM berhasil dikirim ke User ID {$targetUserId}.");
        return true;

    } catch (\Kreait\Firebase\Exception\MessagingException $e) {
        // Ini akan menangkap error "Requested entity was not found"
        Log::error("❌ FCM Messaging Error: " . $e->getMessage() . " (User: {$targetUserId})");
    } catch (\Kreait\Firebase\Exception\FirebaseException $e) {
        // Ini akan menangkap error "No such file or directory"
        Log::error("❌ FCM CREDENTIALS ERROR: " . $e->getMessage());
    } catch (\Exception $e) {
        Log::error("❌ FCM General Error: " . $e->getMessage());
    }
    return false;
}
}