<?php
session_start();
require_once '../config/database.php';

// Ambil data jenazah beserta nama wilayah kerja
$stmt = $pdo->query("SELECT p.*, w.nama_wilker FROM permohonan_jenazah p JOIN wilayah_kerja w ON p.wilayah_kerja_id = w.id ORDER BY p.created_at DESC");
$data_jenazah = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Permohonan Izin Angkut Jenazah</title>
    <style>
        /* Pengaturan Cetak (A4 Portrait) */
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: 'Times New Roman', Times, serif; color: #000; font-size: 11pt; }
        
        .kop-surat { display: flex; align-items: center; justify-content: space-between; border-bottom: 3px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .kop-surat img { width: 90px; height: 90px; object-fit: contain; }
        .kop-teks { text-align: center; flex-grow: 1; }
        .kop-teks h2, .kop-teks h3, .kop-teks p { margin: 0; line-height: 1.3; }
        .kop-teks h2 { font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .kop-teks h3 { font-size: 14pt; font-weight: bold; }
        .kop-teks p { font-size: 10pt; }
        
        .judul-laporan { text-align: center; font-size: 14pt; font-weight: bold; margin-bottom: 20px; text-transform: uppercase; text-decoration: underline; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 10pt; }
        th, td { border: 1px solid #000; padding: 8px 10px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; text-align: center; }
        
        .ttd-container { width: 100%; display: flex; justify-content: flex-end; margin-top: 40px; }
        .ttd { text-align: center; width: 250px; }
        .ttd p { margin: 0 0 70px 0; }
        .ttd strong { text-decoration: underline; }
        
        @media print { .no-print { display: none !important; } }
        .btn-print { background: #064e3b; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 12pt; display: block; margin: 20px auto; }
    </style>
</head>
<body>

    <button class="no-print btn-print" onclick="window.print()">🖨️ Cetak / Simpan sebagai PDF</button>

    <!-- KOP SURAT -->
    <div class="kop-surat">
        <img src="../assets/img/logo_bkkside2.webp" alt="Logo Kiri">
        <div class="kop-teks">
            <h2>KEMENTERIAN KESEHATAN REPUBLIK INDONESIA</h2>
            <h3>BALAI KEKARANTINAAN KESEHATAN KELAS I BANJARMASIN</h3>
            <p>Jalan H. Hasan Basry No.50, Banjarmasin, Kalimantan Selatan<br>Email: bkkbanjarmasin@kemkes.go.id | Website: sidiabaperan.com</p>
        </div>
        <img src="../assets/img/logo_karantina.webp" alt="Logo Kanan">
    </div>

    <div class="judul-laporan">Laporan Data Izin Angkut Jenazah</div>

    <!-- TABEL DATA -->
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">ID Permohonan</th>
                <th width="20%">Nama Pemohon</th>
                <th width="20%">Perusahaan Cargo</th>
                <th width="20%">Wilayah Kerja</th>
                <th width="15%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($data_jenazah as $row): ?>
            <tr>
                <td style="text-align: center;"><?= $no++; ?></td>
                <td style="text-align: center;"><strong><?= htmlspecialchars($row['id_permohonan']); ?></strong></td>
                <td><?= htmlspecialchars($row['nama_pemohon_cargo']); ?> <br> <small>(<?= htmlspecialchars($row['no_hp_pemohon']); ?>)</small></td>
                <td><?= htmlspecialchars($row['perusahaan_cargo']); ?></td>
                <td><?= htmlspecialchars($row['nama_wilker']); ?></td>
                <td style="text-align: center;"><?= htmlspecialchars($row['status']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- BAGIAN TANDA TANGAN -->
    <div class="ttd-container">
        <div class="ttd">
            <p>Banjarmasin, <?= date('d F Y'); ?><br>Mengetahui,<br>Pimpinan / Koordinator</p>
            <strong>........................................</strong>
            <br>NIP. 
        </div>
    </div>

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>