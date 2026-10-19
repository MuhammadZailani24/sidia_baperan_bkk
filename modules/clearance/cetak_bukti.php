<?php
require_once '../../config/database.php';

$id_permohonan = isset($_GET['id']) ? trim($_GET['id']) : '';

if (!$id_permohonan) {
    die("ID Permohonan tidak valid.");
}

$stmt = $pdo->prepare("SELECT p.*, w.nama_wilker FROM permohonan_clearance p JOIN wilayah_kerja w ON p.wilayah_kerja_id = w.id WHERE p.id_permohonan = ?");
$stmt->execute([$id_permohonan]);
$data = $stmt->fetch();

if (!$data) {
    die("Data permohonan tidak ditemukan.");
}

// Format Tanggal Indonesia
function tgl_indo($tanggal){
    $bulan = array (
        1 =>   'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $pecahkan = explode('-', date('Y-m-d', strtotime($tanggal)));
    return (int)$pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Registrasi - <?= htmlspecialchars($data['id_permohonan']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700&display=swap">
    <style>
        :root {
            --bg: #f2f7f7;
            --brand: #0a6b67;
            --brand-deep: #06464a;
            --flag: #f5b722;
            --on-flag: #3a2a00;
        }

        /* Setup Halaman A4 Presisi (Cegah Halaman ke-2) */
        @page { size: A4 portrait; margin: 10mm 15mm; }
        body { 
            font-family: 'Times New Roman', Times, serif; 
            color: #000; 
            font-size: 11pt; 
            line-height: 1.3; 
            background: #fff;
        }

        /* Tampilan di layar: kertas A4 di atas latar teal muda, sama dengan halaman lain */
        @media screen {
            html { background: var(--bg); }
            body {
                max-width: 210mm;
                margin: 28px auto 96px;
                padding: 10mm 15mm 14mm;
                border-top: 6px solid var(--brand-deep);
                border-radius: 4px;
                box-shadow: 0 10px 30px rgba(6, 70, 74, .15);
            }
        }

        /* Agar stempel (z-index negatif) tetap terlihat di atas kertas */
        body > div { isolation: isolate; }

        /* Kop Surat */
        .kop-surat { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
        }
        .kop-logo { width: 85px; object-fit: contain; }
        .kop-teks { text-align: center; flex-grow: 1; padding: 0 10px; }
        .kop-teks h2 { margin: 0; font-size: 13.5pt; font-weight: bold; text-transform: uppercase; }
        .kop-teks h1 { margin: 2px 0; font-size: 15pt; font-weight: bold; text-transform: uppercase; color: var(--brand-deep); }
        .kop-teks p { margin: 0; font-size: 9pt; }

        /* Garis Bawah Kop Surat Resmi (Tebal & Tipis) */
        .garis-kop {
            width: 100%;
            border-top: 3px solid var(--brand-deep);
            border-bottom: 1px solid var(--brand-deep);
            height: 2px;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        /* Judul Dokumen */
        .judul-dokumen { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .judul-dokumen h3 { 
            margin: 0; 
            font-size: 12.5pt; 
            font-weight: bold; 
            text-decoration: underline; 
            text-transform: uppercase; 
            color: var(--brand-deep);
        }
        .judul-dokumen p { 
            margin: 5px 0 0; 
            font-size: 11pt; 
            font-weight: bold; 
        }

        /* Konten */
        .pengantar { text-align: justify; margin-bottom: 15px; }
        
        /* Tabel Data 2 Kolom (Borderless & Kompak) */
        .tabel-data { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .tabel-data td { padding: 5px 2px; vertical-align: top; }
        .td-label { font-weight: bold; width: 22%; }
        .td-titik { width: 2%; text-align: center; }
        .td-value { width: 26%; }
        .td-header {
            font-weight: bold;
            text-transform: uppercase;
            padding-top: 15px !important;
            font-size: 10.5pt;
            text-decoration: underline;
            color: var(--brand);
        }

        /* Bagian Tanda Tangan & Stempel */
        .signature-area {
            width: 100%;
            margin-top: 25px;
            display: flex;
            justify-content: flex-end;
            page-break-inside: avoid; /* Cegah terpotong beda halaman */
        }
        .signature-box {
            width: 300px;
            text-align: center;
            position: relative;
        }
        .signature-box p { margin: 2px 0; }
        .signature-box .bold { font-weight: bold; }
        
        /* Stempel CSS Berada di Tengah Tanda Tangan */
        .stempel {
            position: absolute;
            left: 50%;
            margin-left: -50px; /* Agar tepat di tengah */
            top: 40px;
            width: 100px;
            height: 100px;
            border: 3px solid var(--brand); 
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--brand);
            font-size: 8pt;
            font-weight: bold;
            transform: rotate(-15deg);
            opacity: 0.8;
            box-shadow: inset 0 0 0 1px var(--brand);
            z-index: -1;
        }
        .stempel::before {
            content: 'SIDIA BAPERAN';
            position: absolute;
            top: 25px;
            font-size: 9pt;
        }
        .stempel::after {
            content: 'BKK KELAS I BANJARMASIN';
            position: absolute;
            bottom: 25px;
            font-size: 6.5pt;
        }
        .stempel-garis {
            width: 90%;
            border-bottom: 1px solid var(--brand);
        }

        .nama-terang { margin-top: 75px; font-weight: bold; text-decoration: underline; position: relative; z-index: 1;}

        /* Footer Keterangan */
        .footer-note { 
            margin-top: 30px; 
            padding-left: 10px;
            border-left: 3px solid var(--flag);
            font-size: 9pt; 
            font-style: italic; 
            text-align: justify;
        }

        /* Tombol cetak: melayang di layar, tidak ikut tercetak */
        .btn-print {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 26px;
            font-family: 'Plus Jakarta Sans', 'Segoe UI', system-ui, sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--on-flag);
            background: var(--flag);
            border: none;
            border-radius: 999px;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(6, 70, 74, .35);
            transition: filter .15s, transform .15s;
        }
        .btn-print:hover { filter: brightness(1.08); }
        .btn-print:active { transform: translateX(-50%) scale(.97); }
        .btn-print:focus-visible { outline: 3px solid var(--brand-deep); outline-offset: 3px; }

        /* Layar kecil (HP) */
        @media screen and (max-width: 820px) {
            html { background: var(--bg); }
            body { margin: 0 0 96px; padding: 16px 14px 24px; border-radius: 0; font-size: 10pt; }
            .kop-logo { width: 52px !important; }
            .kop-teks h2 { font-size: 10pt; }
            .kop-teks h1 { font-size: 11pt; }
            .kop-teks p { font-size: 7.5pt; }
            .signature-box { width: 100%; max-width: 300px; }
        }

        /* Sembunyikan Tombol Print saat dicetak */
        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .stempel { border-color: #064e4b; color: #064e4b; }
            /* Mencegah browser print menambahkan header/footer default */
            @page { margin-top: 0; margin-bottom: 0; }
            body { padding-top: 15mm; padding-bottom: 15mm; }
        }
    </style>
</head>
<body>

    <button class="no-print btn-print" onclick="window.print()">🖨️ Cetak / Simpan sebagai PDF</button>

    <div style="position: relative;">
        <!-- KOP SURAT -->
        <div class="kop-surat">
            <img src="../../assets/img/logo_bkkside2.webp" class="kop-logo" alt="Logo Kemenkes">
            <div class="kop-teks">
                <h2>KEMENTERIAN KESEHATAN REPUBLIK INDONESIA</h2>
                <h1>BALAI KEKARANTINAAN KESEHATAN KELAS I BANJARMASIN</h1>
                <p>Jl. Belitung Darat No.118A, Belitung Selatan, Kec. Banjarmasin Barat, Kalimantan Selatan</p>
                <p>Telepon: (0511) 3353948 | Email: balaikarkesbanjarmasin@kemkes.go.id</p>
            </div>
            <img src="../../assets/img/logo_karantina.webp" class="kop-logo" alt="Logo Karantina" style="width: 70px;">
        </div>
        
        <!-- GARIS BAWAH KOP -->
        <div class="garis-kop"></div>

        <!-- JUDUL -->
        <div class="judul-dokumen">
            <h3>TANDA BUKTI REGISTRASI PERMOHONAN CLEARANCE IN</h3>
            <p>Nomor: <?= htmlspecialchars($data['id_permohonan']); ?></p>
        </div>

        <!-- PENGANTAR -->
        <div class="pengantar">
            Telah diterima permohonan layanan publik melalui Sistem Digitalisasi Alat, Bahan Pemeriksaan Kekarantinaan (SIDIA BAPERAN) Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin dengan rincian data pemohon sebagai berikut:
        </div>

        <!-- TABEL DATA -->
        <table class="tabel-data">
            <tr>
                <td class="td-label">Tgl Registrasi</td>
                <td class="td-titik">:</td>
                <td class="td-value"><?= tgl_indo(date('Y-m-d', strtotime($data['created_at']))); ?></td>
                
                <td class="td-label">Kode Unik</td>
                <td class="td-titik">:</td>
                <td class="td-value" style="font-weight: bold;"><?= htmlspecialchars($data['kode_unik']); ?></td>
            </tr>
            <tr>
                <td class="td-label">Wilayah Kerja</td>
                <td class="td-titik">:</td>
                <td class="td-value"><?= htmlspecialchars($data['nama_wilker']); ?></td>
                
                <td class="td-label">Email</td>
                <td class="td-titik">:</td>
                <td class="td-value"><?= htmlspecialchars($data['email_pemohon']); ?></td>
            </tr>
            <tr>
                <td class="td-label">Keagenan</td>
                <td class="td-titik">:</td>
                <td class="td-value"><?= htmlspecialchars($data['keagenan_pelayaran']); ?></td>
                
                <td class="td-label">Nama Kapal</td>
                <td class="td-titik">:</td>
                <td class="td-value"><?= htmlspecialchars($data['nama_kapal']); ?></td>
            </tr>
            <tr>
                <td class="td-label">Petugas Agen</td>
                <td class="td-titik">:</td>
                <td class="td-value"><?= htmlspecialchars($data['nama_petugas_keagenan']); ?></td>
                
                <td class="td-label">Besar Kapal</td>
                <td class="td-titik">:</td>
                <td class="td-value"><?= htmlspecialchars($data['besar_kapal_grt']); ?> GRT</td>
            </tr>
            <tr>
                <td class="td-label">Tgl Kedatangan</td>
                <td class="td-titik">:</td>
                <td class="td-value"><?= tgl_indo($data['tanggal_kedatangan']); ?></td>
                
                <td class="td-label">Pelabuhan Asal</td>
                <td class="td-titik">:</td>
                <td class="td-value"><?= htmlspecialchars($data['pelabuhan_asal']); ?></td>
            </tr>
            <tr>
                <td class="td-label">Jam Kedatangan</td>
                <td class="td-titik">:</td>
                <td class="td-value"><?= date('H:i', strtotime($data['jam_kedatangan'])); ?> WIB</td>
                
                <td class="td-label">Posisi Kapal</td>
                <td class="td-titik">:</td>
                <td class="td-value"><?= htmlspecialchars($data['posisi_kapal']); ?></td>
            </tr>

            <!-- Rincian Jumlah Muatan & Dokumen diintegrasikan ke tabel -->
            <tr>
                <td colspan="6" class="td-header">Rincian Jumlah Muatan:</td>
            </tr>
            <tr>
                <td class="td-label">Jml ABK</td>
                <td class="td-titik">:</td>
                <td class="td-value"><strong><?= htmlspecialchars($data['jumlah_abk']); ?> Orang</strong></td>
                
                <td class="td-label">Jml Penumpang</td>
                <td class="td-titik">:</td>
                <td class="td-value"><strong><?= htmlspecialchars($data['jumlah_penumpang']); ?> Orang</strong></td>
            </tr>

            <tr>
                <td colspan="6" class="td-header">Dokumen Kesehatan Kapal:</td>
            </tr>
            <tr>
                <td class="td-label">Masa SSCEC/SSCC</td>
                <td class="td-titik">:</td>
                <td class="td-value"><strong><?= tgl_indo($data['tanggal_terbit_sscec']); ?></strong></td>
                
                <td class="td-label">Masa SP3K/Med Chest</td>
                <td class="td-titik">:</td>
                <td class="td-value"><strong><?= tgl_indo($data['tanggal_terbit_sp3k']); ?></strong></td>
            </tr>
        </table>

        <!-- FOOTER & TANDA TANGAN -->
        <div class="signature-area">
            <div class="signature-box">
                <p>Banjarmasin, <?= tgl_indo(date('Y-m-d')); ?></p>
                <p class="bold">Balai Kekarantinaan Kesehatan</p>
                <p class="bold">Kelas I Banjarmasin</p>
                
                <!-- Stempel Tengah -->
                <div class="stempel">
                    <div class="stempel-garis"></div>
                </div>

                <div class="nama-terang">Tim Pelayanan Publik</div>
            </div>
        </div>

        <!-- KETERANGAN BAWAH -->
        <div class="footer-note">
            * Bukti registrasi ini dicetak secara otomatis oleh sistem sebagai tanda terima yang sah. Silakan gunakan <strong>Kode Unik</strong> atau <strong>ID Permohonan</strong> Anda untuk melacak status layanan melalui portal SIDIA BAPERAN BKK Kelas I Banjarmasin.
        </div>
    </div>
</body>
</html>