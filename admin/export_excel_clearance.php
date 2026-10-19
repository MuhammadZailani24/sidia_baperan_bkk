<?php
session_start();
if (!isset($_SESSION['admin_logged'])) { header("Location: index.php"); exit; }
require_once '../config/database.php';

// Header untuk memaksa browser mengunduh sebagai file Excel (.xls)
header("Content-Type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Clearance_In_" . date('Ymd_His') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

$stmt = $pdo->query("SELECT p.*, w.nama_wilker FROM permohonan_clearance p JOIN wilayah_kerja w ON p.wilayah_kerja_id = w.id ORDER BY p.created_at DESC");
$data = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table border="0" cellpadding="5" cellspacing="0" style="font-family: Arial, sans-serif; font-size: 11pt;">
        <tr>
            <td colspan="19" style="text-align: center; font-size: 16pt; font-weight: bold;">KEMENTERIAN KESEHATAN REPUBLIK INDONESIA</td>
        </tr>
        <tr>
            <td colspan="19" style="text-align: center; font-size: 14pt; font-weight: bold;">BALAI KEKARANTINAAN KESEHATAN KELAS I BANJARMASIN</td>
        </tr>
        <tr>
            <td colspan="19" style="text-align: center; font-size: 12pt; font-weight: bold; padding-bottom: 20px;">LAPORAN PERMOHONAN CLEARANCE IN</td>
        </tr>
        <tr>
            <td colspan="19"><strong>Tanggal Unduh:</strong> <?= date('d-m-Y H:i:s'); ?></td>
        </tr>
    </table>

    <table border="1" cellpadding="5" cellspacing="0" style="font-family: Arial, sans-serif; font-size: 10pt; width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #0f766e; color: #ffffff; font-weight: bold; text-align: center;">
                <th>No</th>
                <th>ID Permohonan</th>
                <th>Kode Unik</th>
                <th>Waktu Pengajuan</th>
                <th>Wilayah Kerja</th>
                <th>Email Pemohon</th>
                <th>Keagenan Pelayaran</th>
                <th>Petugas Keagenan</th>
                <th>Nama Kapal</th>
                <th>Besar Kapal (GRT)</th>
                <th>Jml ABK</th>
                <th>Jml Penumpang</th>
                <th>Tgl Kedatangan</th>
                <th>Jam Kedatangan</th>
                <th>Pelabuhan Asal</th>
                <th>Posisi Kapal</th>
                <th>Terbit SSCEC/SSCC</th>
                <th>Terbit SP3K/Med Chest</th>
                <th>Status Saat Ini</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (empty($data)): ?>
                <tr>
                    <td colspan="19" style="text-align: center;">Tidak ada data permohonan.</td>
                </tr>
            <?php else: foreach ($data as $row): ?>
                <tr>
                    <td style="text-align: center;"><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['id_permohonan']); ?></td>
                    <td style="font-weight: bold;"><?= htmlspecialchars($row['kode_unik']); ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                    <td><?= htmlspecialchars($row['nama_wilker']); ?></td>
                    <td><?= htmlspecialchars($row['email_pemohon']); ?></td>
                    <td><?= htmlspecialchars($row['keagenan_pelayaran']); ?></td>
                    <td><?= htmlspecialchars($row['nama_petugas_keagenan']); ?></td>
                    <td><?= htmlspecialchars($row['nama_kapal']); ?></td>
                    <td style="text-align: right;"><?= htmlspecialchars($row['besar_kapal_grt']); ?></td>
                    <td style="text-align: center;"><?= htmlspecialchars($row['jumlah_abk']); ?></td>
                    <td style="text-align: center;"><?= htmlspecialchars($row['jumlah_penumpang']); ?></td>
                    <td style="text-align: center;"><?= date('d/m/Y', strtotime($row['tanggal_kedatangan'])); ?></td>
                    <td style="text-align: center;"><?= date('H:i', strtotime($row['jam_kedatangan'])); ?></td>
                    <td><?= htmlspecialchars($row['pelabuhan_asal']); ?></td>
                    <td><?= htmlspecialchars($row['posisi_kapal']); ?></td>
                    <td style="text-align: center;"><?= date('d/m/Y', strtotime($row['tanggal_terbit_sscec'])); ?></td>
                    <td style="text-align: center;"><?= date('d/m/Y', strtotime($row['tanggal_terbit_sp3k'])); ?></td>
                    <td style="font-weight: bold; color: <?= $row['status'] == 'Selesai / Diterbitkan' ? '#15803d' : '#b45309'; ?>;">
                        <?= htmlspecialchars($row['status']); ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</body>
</html>