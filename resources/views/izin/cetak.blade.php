<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gatepass Cetak - {{ $izin->nomor_izin ?? '-' }}</title>
    
    <style>
    /* 1. KOREKSI CSS UTAMA */
    @page { 
        margin: 10mm; /* Margin standar DomPDF */
    }
    body { 
        font-family: Arial, sans-serif; 
        margin: 0; 
        padding: 0; /* Pastikan padding body nol */
        color: #000; 
        font-size: 9pt; 
    }
    .gatepass-container { 
        width: 100%; 
        margin: 0 auto; 
        /* KUNCI 1: Hapus padding di bagian bawah container utama */
        padding-bottom: 0 !important; 
    }

    /* 2. KOREKSI PAGE BREAK */
    /* Pastikan elemen-elemen utama tidak memaksa break yang tidak perlu */
    .approval-table, .l3-container, .notes-container {
        page-break-inside: avoid;
    }

    /* Ini adalah pemisah halaman yang menyebabkan masalah jika diikuti oleh konten kosong */
    .page-break { 
        page-break-before: always;
        /* Tambahkan margin-top untuk keamanan visual */
        margin-top: 10px; 
        /* KUNCI 2: Pastikan tidak ada margin di bawah page-break */
        margin-bottom: 0; 
        page-break-inside: avoid;
    }

    /* [CSS Lainnya] - Disederhanakan untuk Fokus pada Perbaikan */
    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
    .header-table td { padding: 0; vertical-align: top; }
    .header-title { text-align: center; }
    .header-title h1 { font-size: 20pt; margin: 0; }
    .header-title p { font-size: 11pt; margin: 0; font-weight: bold; }
    .logo-img { width: 150px; max-width: 150px; height: auto; display: block; margin: 0 auto 5px auto; }
    .title-box { border: 2px solid #000; padding: 5px 10px; margin-bottom: 15px; }

    .info-gatepass-table { width: 100%; border-collapse: collapse; border: 1px solid #000; border-top: none; }
    .main-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; } 
    .main-table th, .main-table td { padding: 5px; border: 1px solid #000; font-size: 9pt; vertical-align: top; } 
    .shipping-detail-table { width: 50%; border-collapse: collapse; margin-bottom: 20px; font-size: 9pt; border: none; }
    
    .status-barang-table { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 20px; font-size: 10pt; }
    .status-barang-table td { border: none; padding: 8px 10px; text-align: center; vertical-align: middle; font-weight: bold; font-size: 10pt; }
    .status-barang-table .tipe-col { width: 25%; text-transform: uppercase; }
    
    .approval-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .approval-table th, .approval-table td { padding: 5px; text-align: center; vertical-align: top; height: 30px; font-size: 8.5pt; border: none; }
    .signature-container { height: 40px; margin-top: 5px; margin-left: auto; margin-right: auto; position: relative; width: 80%; max-width: 180px; }
    .signature-area { position: absolute; bottom: 0; left: 0; right: 0; border-bottom: 1px solid #000; }
    .ttd-img { max-width: 130%; max-height: 80px; position: absolute; top: 1px; left: 50%; transform: translateX(-50%); z-index: 10; }
    .signed-name { font-weight: bold; display: block; margin-top: 2px; }
    .l3-container { width: 100%; text-align: center; margin-top: 10px; }
    .l3-box { display: inline-block; width: 50%; height: auto; margin: 0 auto; text-align: center; border: none; padding: 5px; min-height: 100px; }

    .photo-grid { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 10px; }
    .photo-grid td { width: 50%; padding: 5px; border: none; vertical-align: top; page-break-inside: avoid; }
    .photo-box { border: 1px solid #aaa; padding: 8px; min-height: 200px; text-align: center; page-break-inside: avoid; }
    .photo-box p { margin: 0 0 5px 0; font-size: 9pt; text-align: left; }
    .photo-img { max-width: 100%; max-height: 250px; height: auto; display: block; margin: 5px auto; border: 1px solid #ddd; }

    .notes-container { 
        margin-top: 15px; 
        font-size: 8pt; 
        color: #000; 
        /* KUNCI 3: Hapus margin-bottom di notes container */
        margin-bottom: 0 !important;
    }
    .notes-list { list-style-type: none; padding-left: 10px; margin-top: 5px; margin-bottom: 0; }
    .notes-list li { text-indent: -5px; line-height: 1.5; font-size: 6pt; }
    </style>
</head>
<body>
    @php
        use Illuminate\Support\Str;
        use Illuminate\Support\Facades\Storage;
        use Illuminate\Support\Facades\File; 

        // FUNGSI HELPER UNTUK KONVERSI GAMBAR KE BASE64 (Dibiarkan tidak berubah)
        function getBase64Image($path) {
            if (empty($path)) return null;
            
            $cleanedPath = Str::startsWith($path, 'storage/') ? substr($path, 8) : $path;

            if (!Storage::disk('public')->exists($cleanedPath)) {
                 $noImagePath = public_path('images/no-image.png');
                 if (file_exists($noImagePath)) {
                    $type = pathinfo($noImagePath, PATHINFO_EXTENSION);
                    $data = file_get_contents($noImagePath);
                    return 'data:image/' . $type . ';base64,' . base64_encode($data);
                 }
                return null;
            }
            
            try {
                $data = Storage::disk('public')->get($cleanedPath);
                
                $extension = pathinfo($cleanedPath, PATHINFO_EXTENSION);
                $mime = "image/" . (in_array($extension, ['jpg', 'jpeg']) ? 'jpeg' : $extension);

                if ($data === false || $mime === false) { return null; }

                return 'data:' . $mime . ';base64,' . base64_encode($data);
            } catch (\Exception $e) {
                return null;
            }
        }

        $ttdPemohonSrc = getBase64Image($izin->ttd_pemohon_path ?? $izin->pemohon->signature_path ?? null);
        $ttdL1Src = getBase64Image($izin->ttd_approver_l1 ?? $izin->approverL1->signature_path ?? null);
        $ttdL2Src = getBase64Image($izin->ttd_approver_l2 ?? $izin->approverL2->signature_path ?? null);
        $ttdL3Src = getBase64Image($izin->ttd_approver_l3 ?? $izin->approverL3->signature_path ?? null);

        $logoFileName = 'logo.png'; 
        $logoPath = public_path('images/' . $logoFileName);
        $logoSrc = null;

        if (file_exists($logoPath)) {
            $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/' . $logoType . ';base64,' . $logoData;
        }

        $tipeIzin = $izin->jenis_izin ?? 'masuk'; 
        $perihalKeterangan = $izin->perihal ?? 'Material Masuk & Tidak Keluar kembali'; 

        // Ambil hanya item yang memiliki foto
        $itemsWithPhotos = $izin->detailBarang->filter(fn($item) => !empty($item->foto_path));
    @endphp

    <div class="gatepass-container">
        
        {{-- [HALAMAN 1: GATEPASS UTAMA] --}}
        
        {{-- JUDUL DAN LOGO --}}
        <div class="title-box">
            <table class="header-table">
                <tr>
                    <td class="header-title" style="width: 70%;, margin-top: 10;">
                        <h1>GATEPASS</h1>
                        <p>IJIN MASUK / KELUAR BARANG & MATERIAL</p>
                    </td>
                    <td class="logo-container">
                        @if ($logoSrc)
                            <img src="{{ $logoSrc }}" alt="Logo Perusahaan" class="logo-img">
                        @else
                            <div style="font-size: 8pt; color: red; text-align: center;">[Logo tidak ditemukan]</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <hr/>
        
        {{-- INFORMASI RINGKAS BARU --}}
        <div class="info-gatepass-container">
            <table class="info-gatepass-table" style="
                width: 100%; 
                border-collapse: collapse; 
                font-size: 9pt;
                border: 1px solid #000;
            ">
                <tbody>
                    {{-- BARIS 1: JUDUL UTAMA + NOMOR GATEPASS --}}
                    <tr>
                        <td colspan="6" style="
                            text-align: left; 
                            padding: 5px 10px; 
                            font-weight: bold; 
                            font-size: 10pt; 
                            border-bottom: 1px solid #000; 
                            border-top: none;
                            border-left: none;
                            border-right: none;
                        ">
                            NOMOR GATEPASS : {{ $izin->nomor_izin ?? '-' }}
                        </td>
                    </tr>
                    
                    {{-- BARIS 2: DATA RINGKAS --}}
                    <tr class="detail-row">
                        <td class="label-col" style="width: 15%; border: none; padding: 5px;">Dibuat Oleh</td>
                        <td class="colon-col" style="width: 2%; border: none; padding: 5px;">:</td>
                        <td class="value-col" style="width: 33%; border: none; padding: 5px;">{{ $izin->pembawa_barang ?? '-' }}</td>
                        
                        <td class="label-right-col" style="width: 15%; border: none; padding: 5px;">Fungsi</td>
                        <td class="colon-col" style="width: 2%; border: none; padding: 5px;">:</td>
                        <td class="value-right-col" style="width: 33%; border: none; padding: 5px;">{{ $izin->fungsi_pemohon ?? '-' }}</td>
                    </tr>
                    
                    {{-- BARIS 3: DATA RINGKAS --}}
                    <tr class="detail-row">
                        <td class="label-col" style="border: none; padding: 5px;">Dasar Pekerjaan</td>
                        <td class="colon-col" style="border: none; padding: 5px;">:</td>
                        <td class="value-col" style="border: none; padding: 5px;">{{ $izin->dasar_pekerjaan ?? '-' }}</td>
                        
                        <td class="label-right-col" style="border: none; padding: 5px;">Tanggal</td>
                        <td class="colon-col" style="border: none; padding: 5px;">:</td>
                        <td class="value-right-col" style="border: none; padding: 5px;">{{ \Carbon\Carbon::parse($izin->tanggal ?? $izin->created_at)->format('d F Y') }}</td>
                    </tr>
                    
                    @if($izin->keterangan_umum)
                    <tr class="detail-row">
                        <td class="label-col" style="border: none; padding: 5px;">Keterangan Umum</td>
                        <td class="colon-col" style="border: none; padding: 5px;">:</td>
                        <td class="full-value-col-4" colspan="4" style="border: none; padding: 5px;">{{ $izin->keterangan_umum ?? '-' }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <hr/>
        
        {{-- DETAIL BARANG --}}
        <table class="main-table" style="margin-top: 5px;">
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
                @php
                    $totalItems = $izin->detailBarang->count();
                    $maxRows = max(10, $totalItems); 
                    $displayedItems = 0;
                @endphp
                
                @forelse($izin->detailBarang as $index => $item)
                <tr class="barang-content">
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item->nama_item ?? $item->nama_barang ?? '-' }}</td> 
                    <td style="text-align: center;">{{ $item->qty ?? $item->volume ?? 0 }}</td>
                    <td style="text-align: center;">{{ $item->satuan ?? '-' }}</td>
                    <td>{{ $item->keterangan_item ?? '-' }}</td>
                </tr>
                @php $displayedItems++; @endphp
                @empty
                @endforelse

                {{-- Menambah BARIS KOSONG --}}
                @for ($i = $displayedItems; $i < $maxRows; $i++)
                <tr class="barang-content empty-row">
                    <td style="text-align: center;">{{ $i + 1 }}</td>
                    <td></td> <td></td> <td></td> <td></td>
                </tr>
                @endfor
            </tbody>
        </table>

        {{--- DETAIL PENGIRIMAN ---}}
        <table class="shipping-detail-table">
            <tbody>
                <tr>
                    <td class="label-col">Tujuan Pengiriman</td><td class="colon-col">:</td><td class="value-col">{{ $izin->tujuan_pengiriman ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label-col">No. Telp / HP</td><td class="colon-col">:</td><td class="value-col">{{ $izin->nomor_telepon_pemohon ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label-col">No. Polisi Kendaraan</td><td class="colon-col">:</td><td class="value-col">{{ $izin->nomor_kendaraan ?? '-' }}</td>
                </tr>
            </tbody>
        </table>


        {{-- TABEL STATUS BARANG --}}
        <table class="status-barang-table">
            <tbody>
                {{-- BARIS 1: JUDUL UTAMA --}}
                <tr>
                    <td colspan="2" style="
                        text-align: center; 
                        padding: 5px 10px; 
                        font-weight: bold; 
                        font-size: 11pt; 
                        border: 1px solid #000; 
                        border-bottom: none;
                    ">
                        BARANG / MATERIAL / ALAT KERJA TERSEBUT AKAN :
                    </td>
                </tr>
                
                {{-- BARIS 2: DATA JENIS IZIN DAN PERIHAL --}}
                <tr style="height: 50px;">
                    <td class="tipe-col" style="
                        border: 1px solid #000; 
                        border-right: none; 
                        text-align: center; 
                        vertical-align: middle; 
                        font-weight: bold;
                    ">
                        {{ strtoupper($tipeIzin) }}
                    </td>
                    
                    <td style="
                        border: 1px solid #000; 
                        text-align: center; 
                        vertical-align: middle; 
                        font-weight: bold;
                    ">
                        {{ $perihalKeterangan }}
                    </td>
                </tr>
            </tbody>
        </table>


        {{-- BAGIAN PERSETUJUAN (Mengizinkan TTD Manual) --}}
        <table class="approval-table" style="margin-top: 10px;">
            <thead>
                <tr>
                    <th style="width: 33.3%;">Pembawa Barang</th> 
                    <th style="width: 33.3%;">Security</th> 
                    <th style="width: 33.3%;">Atasan Pemohon</th> 
                </tr>
            </thead>
            <tbody>
                <tr>
                    {{-- Kolom Pemohon (Diajukan) --}}
                    <td>
                        <div class="signature-container">
                            @if ($ttdPemohonSrc) <img src="{{ $ttdPemohonSrc }}" alt="TTD Pemohon" class="ttd-img"> @endif
                            <div class="signature-area"></div>
                        </div>
                        <span class="signed-name">{{ $izin->pembawa_barang ?? '-' }}</span>
                    </td>

                    {{-- Kolom L2 (Security) - Tengah --}}
                    <td>
                        <div class="signature-container">
                            @if ($izin->tgl_persetujuan_l2 && $ttdL2Src) 
                                <img src="{{ $ttdL2Src }}" alt="TTD L2" class="ttd-img"> 
                            @endif
                            <div class="signature-area"></div>
                        </div>
                        <span class="signed-name">
                            @if($izin->l2_rejected)
                                <span style="color: red;">DITOLAK: {{ $izin->nama_approver_l2 ?? '-' }}</span>
                            @else
                                {{ $izin->nama_approver_l2 ?? '-' }}
                            @endif
                        </span>
                    </td>

                    {{-- Kolom L1 (Atasan) - Kanan --}}
                    <td>
                        <div class="signature-container">
                            @if ($izin->tgl_persetujuan_l1 && $ttdL1Src) 
                                <img src="{{ $ttdL1Src }}" alt="TTD L1" class="ttd-img"> 
                            @endif
                            <div class="signature-area"></div>
                        </div>
                        <span class="signed-name">
                            @if($izin->l1_rejected)
                                <span style="color: red;">DITOLAK: {{ $izin->nama_approver_l1 ?? '-' }}</span>
                            @else
                                {{ $izin->nama_approver_l1 ?? '-' }}
                            @endif
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
        
        {{-- BOX KHUSUS L3 (Manajemen) --}}
        <div class="l3-container">
            <div class="l3-box">
                <div class="l3-title-block">
                    <p style="font-size: 9pt; font-weight: normal; margin: 0;">Mengetahui :</p>
                    <p style="font-size: 9pt; font-weight: bold; margin-bottom: 5px;">{{ $izin->jabatan_approver_l3 ?? 'Integrated Manager' }}</p>
                </div>
                <div class="signature-container" style="height: 50px; width: 180px; margin: 5px auto 0 auto;"> 
                    @if ($izin->tgl_persetujuan_l3 && $ttdL3Src) 
                        <img src="{{ $ttdL3Src }}" alt="TTD L3" class="ttd-img"> 
                    @endif
                    <div class="signature-area"></div>
                </div>
                <span class="signed-name">
                    @if($izin->l3_rejected)
                        <span style="color: red;">DITOLAK: {{ $izin->nama_approver_l3 ?? 'IT Manager' }}</span>
                    @else
                        {{ $izin->nama_approver_l3 ?? 'IT Manager' }}
                    @endif
                </span>
            </div>
        </div>

        {{-- KUNCI 4: HAPUS CLEAR:BOTH UNTUK MEMPERBAIKI MASALAH FLOAT --}}
        {{-- Saya menghapus div clear:both pertama di sini. --}}
        
        {{-- CATATAN BARU --}}
        <div class="notes-container" style="
            margin-top: 40px; 
            width: 50%;
            float: left; 
            padding-left: 5px; 
            font-size: 6pt; 
            color: #000;
            /* KUNCI 5: Zero Margin Bottom untuk Catatan */
            margin-bottom: 0 !important;
        ">
            <p style="margin: 0; line-height: 1.4; font-size: 7pt;">
                <span class="notes-header">Catatan :</span> 
                - * : Coret Sesuai Dengan Keperluan
            </p>
            <ul class="notes-list">
                <li style="text-indent: -5px; line-height: 1.5; font-size: 6pt;">- Lembar No. 1 - Untuk Pembawa Barang</li>
                <li style="text-indent: -5px; line-height: 1.5; font-size: 6pt;">- Lembar No. 2 - Untuk Security</li>
                <li style="text-indent: -5px; line-height: 1.5; font-size: 6pt;">- Lembar No. 3 - Untuk Arsip (Fungsi)</li>
                @if ($itemsWithPhotos->count() > 0)
                    <li style="font-weight: bold; color: darkred; text-indent: -5px; line-height: 1.5; font-size: 8pt;">- Lembar Tambahan - Dilampirkan Foto Bukti Barang/Material (Lihat Halaman Berikutnya).</li>
                @endif
            </ul>
        </div>
        
        {{-- Kunci 6: Gunakan clear:both di sini SEBELUM page break --}}
        <div style="clear: both; margin: 0; padding: 0;"></div> 

        {{-- ================================================= --}}
        {{-- HALAMAN 2 DAN SETERUSNYA: FOTO BARANG (2 KOLOM) --}}
        {{-- ================================================= --}}
        
        {{-- PAGE BREAK INI ADALAH KUNCI PEMISAH KE HALAMAN 2 --}}
        {{-- Pastikan div ini adalah elemen terakhir sebelum konten baru --}}
        @if ($itemsWithPhotos->count() > 0)
            <div class="page-break"></div>
            
            <h2 style="text-align: center; font-size: 14pt; margin: 10px 0 5px 0;">
                LAMPIRAN FOTO BARANG / MATERIAL - GATEPASS: {{ $izin->nomor_izin ?? '-' }}
            </h2>
            <p style="text-align: center; margin-bottom: 10px; font-size: 10pt; color: gray;">Dokumen ini berisi bukti foto material yang diizinkan keluar/masuk.</p>

            {{-- Mengelompokkan item ke dalam pasang (pasangan 2 item) --}}
            @php
                $photoGroups = $itemsWithPhotos->chunk(2); // Membagi koleksi menjadi kelompok 2 item
            @endphp

            <table class="photo-grid">
                @foreach ($photoGroups as $group)
                    <tr>
                        @foreach ($group as $barang)
                            {{-- KOLOM FOTO UTAMA --}}
                            <td>
                                <div class="photo-box">
                                    <p style="font-weight: bold;">
                                        {{ $loop->parent->index * 2 + $loop->index + 1 }}. {{ $barang->nama_item ?? '-' }} 
                                        ({{ $barang->qty ?? 0 }} {{ $barang->satuan ?? '' }})
                                    </p>
                                    <p style="color: #555;">Keterangan: {{ $barang->keterangan_item ?? '-' }}</p>

                                    <img src="{{ getBase64Image($barang->foto_path) }}" 
                                        alt="Foto Barang {{ $loop->parent->index * 2 + $loop->index + 1 }}" 
                                        class="photo-img">
                                </div>
                            </td>
                        @endforeach
                        
                        {{-- Tambahkan sel kosong jika jumlah item ganjil --}}
                        @if ($group->count() == 1)
                            <td></td>
                        @endif
                    </tr>
                @endforeach
            </table>
        @endif

    </div>
</body>
</html>