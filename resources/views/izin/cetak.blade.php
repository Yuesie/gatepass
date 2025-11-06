<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gatepass Cetak - {{ $izin->nomor_izin ?? 'N/A' }}</title>
    
    <style>
        /* CSS Dasar untuk DomPDF */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px; 
            color: #000;
            font-size: 9pt; 
        }
        .gatepass-container {
            width: 100%;
            margin: 0 auto;
        }
        
        /* HEADER LOGO & JUDUL */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            padding: 0;
            vertical-align: top;
        }
        .header-title {
            text-align: center;
        }
        .header-title h1 {
            font-size: 20pt; 
            margin: 0;
        }
        .header-title p {
            font-size: 11pt; 
            margin: 0;
            font-weight: bold;
        }
        .logo-container {
            width: 120px; 
            text-align: right;
        }
        .logo-img {
            max-width: 100px; 
            height: auto; 
            display: block;
            margin-left: auto; 
        }
        /* Border keliling untuk seluruh area header diaktifkan kembali */
        .title-box {
            border: 2px solid #000; 
            padding: 5px 10px;
            margin-bottom: 15px;
        }
        .title-box .status-badge {
            font-weight: bold;
            color: darkgreen;
            font-size: 9pt;
            text-align: center;
            border-top: 1px solid #000; 
            padding-top: 5px;
            margin-top: 5px;
        }

        /* TABEL UTAMA (DETAIL & BARANG) */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .main-table th, .main-table td {
            padding: 5px;
            border: 1px solid #000; /* BORDER AKTIF */
            font-size: 9pt;
            vertical-align: top;
        }
        .section-header {
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 5px;
            border: 1px solid #000; /* BORDER AKTIF */
            text-align: left;
            margin-top: 0; 
        }

        /* DETAIL RINGKAS */
        .detail-row td:nth-child(odd) {
            width: 20%; 
            font-weight: bold;
            background-color: #f7f7f7; /* BACKGROUND AKTIF */
        }
        .detail-row td:nth-child(even) {
            width: 30%; 
        }

        /* TABEL PERSETUJUAN */
        .approval-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px; 
        }
        .approval-table th, .approval-table td {
            border: none;
            padding: 5px;
            text-align: center;
            vertical-align: top;
            height: 90px;
            font-size: 8.5pt;
        }
        .approval-table th {
             background-color: transparent; 
             border-bottom: none;
        }

        /* TANDA TANGAN CONTAINER */
        .signature-container {
            height: 40px;
            margin-top: 5px;
            margin-left: 20px;
            margin-right: 20px;
            position: relative;
        }
        /* Style untuk garis TTD (signature-area) */
        .signature-area {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            border-bottom: 1px solid #000;
        }
        /* Style untuk gambar TTD (ttd-img) */
        .ttd-img {
            max-width: 90%;
            max-height: 50px; /* Batasi tinggi gambar */
            position: absolute;
            top: -10px; /* Sesuaikan posisi vertikal agar tepat di atas garis */
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }

        .signed-name {
            font-weight: bold;
            display: block;
            margin-top: 2px;
        }
        .approval-status-info {
            font-size: 8pt;
            margin-top: 5px;
        }
        
        /* BOX KHUSUS L3 (Manajemen) */
        .l3-container {
            width: 100%;
            text-align: center;
            margin-top: 20px; 
        }
        .l3-box {
            display: inline-block; 
            width: 300px; 
            margin: 0 auto;
            text-align: center;
        }

        .note {
            margin-top: 15px;
            font-size: 8pt;
        }
    </style>
</head>
<body>
    @php
        use Illuminate\Support\Str;
        
        // FUNGSI HELPER UNTUK KONVERSI GAMBAR KE BASE64 (FINAL VERSION)
        function getBase64Image($path) {
            if (empty($path)) return null;

            $finalPath = null;
            
            // 1. Coba di lokasi STORAGE (path teraman untuk file yang diunggah)
            $storagePath = storage_path('app/public/' . $path);
            
            // 2. Coba di lokasi PUBLIC/STORAGE (symlink)
            $publicStoragePath = public_path('storage/' . $path);

            if (file_exists($storagePath)) {
                $finalPath = $storagePath;
            } elseif (file_exists($publicStoragePath)) {
                $finalPath = $publicStoragePath;
            }
            
            // 1. Pastikan file ditemukan.
            // 2. Pastikan file dapat dibaca (is_readable).
            if ($finalPath && is_readable($finalPath)) { 
                try {
                    $type = pathinfo($finalPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($finalPath);
                    
                    // Cek jika file_get_contents gagal (mengembalikan false)
                    if ($data === false) { 
                        return null; // Gagal baca, cegah error
                    }

                    return 'data:image/' . $type . ';base64,' . base64_encode($data);
                } catch (\Exception $e) {
                    // Tangkap sisa error jika ada, return null untuk keamanan
                    return null;
                }
            }
            
            // Gagal menemukan file atau tidak bisa dibaca
            return null; 
        }

        // PANGGILAN FUNGSI TTD
        // Note: Asumsi kolom di tabel izin_masuk adalah ttd_pemohon_path, ttd_approver_l1, etc.
        $ttdPemohonSrc = getBase64Image($izin->ttd_pemohon_path ?? $izin->pemohon->signature_path ?? null);
        // KODE YANG DIKOREKSI (MEMPRIORITASKAN KOLOM DI TABEL IzinMasuk)
        $ttdL1Src = getBase64Image($izin->ttd_approver_l1 ?? $izin->approverL1->signature_path ?? null);
        $ttdL2Src = getBase64Image($izin->ttd_approver_l2 ?? $izin->approverL2->signature_path ?? null);
        $ttdL3Src = getBase64Image($izin->ttd_approver_l3 ?? $izin->approverL3->signature_path ?? null);

        // Logo
        $logoFileName = 'logo.png'; 
        // Menggunakan public_path untuk aset statis non-uploaded
        $logoPath = public_path('images/' . $logoFileName);
        $logoSrc = null;

        if (file_exists($logoPath)) {
            $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/' . $logoType . ';base64,' . $logoData;
        }
    @endphp

    <div class="gatepass-container">

        {{-- JUDUL DAN LOGO --}}
        <div class="title-box">
            <table class="header-table">
                <tr>
                    <td class="header-title" style="width: 70%;">
                        <h1>GATEPASS</h1>
                        <p>IJIN MASUK / KELUAR BARANG & MATERIAL</p>
                    </td>
                    <td class="logo-container">
                        @if ($logoSrc)
                            <img src="{{ $logoSrc }}" alt="Logo Perusahaan" class="logo-img">
                        @else
                            <div style="font-size: 8pt; color: red; text-align: center;">[Logo tidak ditemukan: {{ $logoFileName }}]</div>
                        @endif
                    </td>
                </tr>
            </table>
            <div class="status-badge">
                STATUS: {{ strtoupper($izin->status) }}
            </div>
        </div>


        {{-- INFORMASI RINGKAS --}}
        <div class="section-header">Informasi Gatepass</div>
        <table class="main-table">
            <tbody>
                <tr class="detail-row">
                    <td>Nomor Gatepass</td>
                    <td>**{{ $izin->nomor_izin ?? 'BELUM FINAL' }}**</td>
                    <td>Fungsi Pemohon</td>
                    <td>{{ $izin->fungsi_pemohon }}</td>
                </tr>
                
                {{-- ✅ NEW: Jabatan Fungsi Pemohon --}}
                <tr class="detail-row">
                    <td>Jabatan Fungsi Pemohon</td>
                    <td>{{ $izin->jabatan_fungsi_pemohon ?? '-' }}</td>
                    <td>Tanggal Pengajuan</td>
                    <td>{{ \Carbon\Carbon::parse($izin->tanggal)->format('d F Y') }}</td>
                </tr>
                
                <tr class="detail-row">
                    <td>Dasar Pekerjaan</td>
                    <td>{{ $izin->dasar_pekerjaan }}</td>
                    <td>Perihal</td>
                    <td>{{ $izin->perihal }}</td>
                </tr>
                {{-- Baris Perihal lama diganti isinya dengan Tujuan dan Perusahaan --}}
                <tr class="detail-row">
                    <td>Nama Perusahaan</td>
                    <td>{{ $izin->nama_perusahaan ?? '-' }}</td>
                    <td>Tujuan Pengiriman</td>
                    <td>{{ $izin->tujuan_pengiriman }}</td>
                </tr>
                <tr class="detail-row">
                    <td>Pembawa Barang</td>
                    <td>{{ $izin->pembawa_barang }}</td>
                    <td>Nomor Kendaraan</td>
                    <td>{{ $izin->nomor_kendaraan ?? '-' }}</td>
                </tr>
                {{-- NEW: Tambahkan Keterangan Umum jika ada --}}
                @if($izin->keterangan_umum)
                <tr class="detail-row">
                    <td>Keterangan Umum</td>
                    <td colspan="3">{{ $izin->keterangan_umum }}</td>
                </tr>
                @endif
                
            </tbody>
        </table>

        {{-- DETAIL BARANG --}}
        <div class="section-header">Detail Barang yang Dibawa</div>
        <table class="main-table">
            <thead>
                <tr class="barang-header">
                    <th style="width: 5%;">No.</th>
                    <th style="width: 30%;">Nama Barang</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 10%;">Satuan</th>
                    <th style="width: 45%;">Keterangan Item</th>
                </tr>
            </thead>
            <tbody>
                @forelse($izin->detailBarang as $index => $item)
                <tr class="barang-content">
                    <td>{{ $index + 1 }}</td>
                    {{-- KOREKSI: nama_barang -> nama_item --}}
                    <td>{{ $item->nama_item ?? $item->nama_barang }}</td> 
                    {{-- KOREKSI: volume -> qty --}}
                    <td>{{ $item->qty ?? $item->volume }}</td>
                    <td>{{ $item->satuan }}</td>
                    <td>{{ $item->keterangan_item ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Tidak ada detail barang yang dilampirkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- BAGIAN PERSETUJUAN (L1 & L2) - TTD DISISIPKAN --}}
        <div class="section-header" style="margin-top: 15px;">Persetujuan</div>
        <table class="approval-table">
            <thead>
                <tr>
                    <th style="width: 33.3%;">Diajukan Oleh (Pembawa Barang)</th>
                    <th style="width: 33.3%;">Disetujui L1 (Atasan)</th>
                    <th style="width: 33.3%;">Disetujui L2 (Security)</th> 
                </tr>
            </thead>
            <tbody>
                <tr>
                    {{-- Kolom Pemohon (Diajukan) --}}
                    <td>
                        <div class="signature-container">
                            @if ($ttdPemohonSrc)
                                <img src="{{ $ttdPemohonSrc }}" alt="TTD Pemohon" class="ttd-img">
                            @endif
                            <div class="signature-area"></div>
                        </div>
                        <span class="signed-name">{{ $izin->pembawa_barang ?? 'N/A' }}</span>
                        <div class="approval-status-info">
                            Tgl: {{ \Carbon\Carbon::parse($izin->created_at)->format('d/m/Y H:i') }}
                        </div>
                    </td>

                    {{-- Kolom L1 --}}
                    <td>
                        @if($izin->tgl_persetujuan_l1)
                            <div class="signature-container">
                                @if ($ttdL1Src)
                                    <img src="{{ $ttdL1Src }}" alt="TTD L1" class="ttd-img">
                                @endif
                                <div class="signature-area"></div>
                            </div>
                            <span class="signed-name">{{ $izin->approverL1->name ?? 'N/A' }}</span>
                            <div class="approval-status-info" style="color: green;">
                                **Disetujui**<br> Tgl: {{ \Carbon\Carbon::parse($izin->tgl_persetujuan_l1)->format('d/m/Y H:i') }}
                            </div>
                        @elseif($izin->l1_rejected)
                             <div class="signature-area"></div>
                             <span class="signed-name">{{ $izin->approverL1->name ?? 'N/A' }}</span>
                             <div style="color: red; font-weight: bold;">DITOLAK</div>
                        @else
                            <div style="margin-top: 40px;">Menunggu</div>
                        @endif
                    </td>

                    {{-- Kolom L2 (Security) --}}
                    <td>
                        @if($izin->tgl_persetujuan_l2)
                            <div class="signature-container">
                                @if ($ttdL2Src)
                                    <img src="{{ $ttdL2Src }}" alt="TTD L2" class="ttd-img">
                                @endif
                                <div class="signature-area"></div>
                            </div>
                            <span class="signed-name">{{ $izin->approverL2->name ?? 'N/A' }}</span>
                            <div class="approval-status-info" style="color: green;">
                                **Disetujui**<br> Tgl: {{ \Carbon\Carbon::parse($izin->tgl_persetujuan_l2)->format('d/m/Y H:i') }}
                            </div>
                        @elseif($izin->l2_rejected)
                             <div class="signature-area"></div>
                             <span class="signed-name">{{ $izin->approverL2->name ?? 'N/A' }}</span>
                             <div style="color: red; font-weight: bold;">DITOLAK</div>
                        @else
                            <div style="margin-top: 40px;">Menunggu</div>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
        
        {{-- BOX KHUSUS L3 (Manajemen) - TTD DISISIPKAN --}}
        <div class="l3-container">
            <div class="l3-box">
                <h3 style="font-size: 9pt; font-weight: bold; margin-bottom: 5px;">Disetujui L3 (Manajemen / Final)</h3>
                @if($izin->tgl_persetujuan_l3)
                    <div class="signature-container">
                        @if ($ttdL3Src)
                            <img src="{{ $ttdL3Src }}" alt="TTD L3" class="ttd-img">
                        @endif
                        <div class="signature-area"></div>
                    </div>
                    <span class="signed-name">{{ $izin->approverL3->name ?? 'N/A' }}</span>
                    <div class="approval-status-info" style="color: darkgreen; font-weight: bold;">
                        **DISETUJUI FINAL**<br> Tgl: {{ \Carbon\Carbon::parse($izin->tgl_persetujuan_l3)->format('d/m/Y H:i') }}
                    </div>
                @elseif($izin->l3_rejected)
                    <div class="signature-area"></div>
                    <span class="signed-name">{{ $izin->approverL3->name ?? 'N/A' }}</span>
                    <div style="color: red; font-weight: bold;">DITOLAK</div>
                @else
                    <div style="margin-top: 40px;">Menunggu</div>
                @endif
            </div>
        </div>

        <div class="note">
            <p>**Catatan:** Dokumen ini sah tanpa tanda tangan basah jika disetujui secara elektronik.</p>
            <p style="text-align: right; margin-top: 5px;">Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y H:i') }}</p>
        </div>

    </div>
</body>
</html>