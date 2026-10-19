<?php
require_once '../../config/database.php';

$id_permohonan = isset($_GET['id']) ? trim($_GET['id']) : '';

if (!$id_permohonan) {
    die("ID Permohonan tidak valid.");
}

$stmt = $pdo->prepare("SELECT p.*, w.nama_wilker FROM permohonan_jenazah p JOIN wilayah_kerja w ON p.wilayah_kerja_id = w.id WHERE p.id_permohonan = ?");
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
    <title>Bukti Registrasi - <?= htmlspecialchars($data['id_permohonan']); ?></title>
    <style>
        /* Setup Halaman A4 Presisi */
        @page { size: A4 portrait; margin: 10mm 15mm; }
        body { 
            font-family: 'Times New Roman', Times, serif; 
            color: #000; 
            font-size: 11pt; 
            line-height: 1.3; 
            background: #fff;
        }

        /* Kop Surat */
        .kop-surat { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
        }
        .kop-logo { width: 85px; object-fit: contain; }
        .kop-teks { text-align: center; flex-grow: 1; padding: 0 10px; }
        .kop-teks h2 { margin: 0; font-size: 13.5pt; font-weight: bold; text-transform: uppercase; }
        .kop-teks h1 { margin: 2px 0; font-size: 15pt; font-weight: bold; text-transform: uppercase; }
        .kop-teks p { margin: 0; font-size: 9pt; }

        /* Garis Bawah Kop Surat Resmi (Tebal & Tipis) */
        .garis-kop {
            width: 100%;
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
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
        .tabel-data td { padding: 8px 2px; vertical-align: top; }
        .td-label { font-weight: bold; width: 22%; }
        .td-titik { width: 2%; text-align: center; }
        .td-value { width: 26%; }

        /* Bagian Tanda Tangan & Stempel */
        .signature-area {
            width: 100%;
            margin-top: 35px;
            display: flex;
            justify-content: flex-end;
            page-break-inside: avoid;
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
            border: 3px solid #3b82f6; 
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #3b82f6;
            font-size: 8pt;
            font-weight: bold;
            transform: rotate(-15deg);
            opacity: 0.8;
            box-shadow: inset 0 0 0 1px #3b82f6;
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
            border-bottom: 1px solid #3b82f6;
        }

        .nama-terang { margin-top: 75px; font-weight: bold; text-decoration: underline; position: relative; z-index: 1;}

        /* Footer Keterangan */
        .footer-note { 
            margin-top: 40px; 
            font-size: 9pt; 
            font-style: italic; 
            text-align: justify;
        }

        /* Sembunyikan Tombol Print saat dicetak */
        @media print {
            .no-print { display: none !important; }
            .stempel { border-color: #1e3a8a; color: #1e3a8a; }
            @page { margin-top: 0; margin-bottom: 0; }
            body { padding-top: 15mm; padding-bottom: 15mm; }
        }
        
        .btn-print { background: #064e3b; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 11pt; font-weight: bold; display: block; margin: 15px auto 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
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
            <h3>TANDA BUKTI REGISTRASI PERMOHONAN IZIN ANGKUT JENAZAH</h3>
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
                
                <td class="td-label">Email Pemohon</td>
                <td class="td-titik">:</td>
                <td class="td-value"><?= htmlspecialchars($data['email_pemohon']); ?></td>
            </tr>
            <tr>
                <td class="td-label">Nama (Cargo)</td>
                <td class="td-titik">:</td>
                <td class="td-value"><?= htmlspecialchars($data['nama_pemohon_cargo']); ?></td>
                
                <td class="td-label">No. Handphone</td>
                <td class="td-titik">:</td>
                <td class="td-value"><?= htmlspecialchars($data['no_hp_pemohon']); ?></td>
            </tr>
            <tr>
                <td class="td-label">Perusahaan</td>
                <td class="td-titik">:</td>
                <td class="td-value" colspan="4"><?= htmlspecialchars($data['perusahaan_cargo']); ?></td>
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