<?php
require_once '../config/database.php';

$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
if ($jenis !== 'clearance' && $jenis !== 'jenazah') {
    header("Location: index.php");
    exit;
}

$data_surat = null;
$error_msg = '';
$kode_unik = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_unik = trim($_POST['kode_unik']);

    if ($jenis === 'clearance') {
        $stmt = $pdo->prepare("SELECT p.*, w.nama_wilker FROM permohonan_clearance p JOIN wilayah_kerja w ON p.wilayah_kerja_id = w.id WHERE p.kode_unik = ?");
    } else {
        $stmt = $pdo->prepare("SELECT p.*, w.nama_wilker FROM permohonan_jenazah p JOIN wilayah_kerja w ON p.wilayah_kerja_id = w.id WHERE p.kode_unik = ?");
    }
    
    $stmt->execute([$kode_unik]);
    $data_surat = $stmt->fetch();

    if (!$data_surat) {
        $error_msg = "Kode unik tidak ditemukan! Periksa kembali kode yang Anda masukkan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking <?= ($jenis === 'clearance') ? 'Clearance In' : 'Izin Angkut Jenazah'; ?> - SIDIA BAPERAN</title>
    <link rel="stylesheet" href="../assets/css/tracking.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>Tracking Status: <?= ($jenis === 'clearance') ? 'Clearance In' : 'Surat Izin Angkut Jenazah'; ?></h2>
            
            <form action="" method="POST" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Masukkan Kode Unik Permohonan</label>
                    <input type="text" name="kode_unik" required value="<?= htmlspecialchars($kode_unik); ?>" placeholder="Contoh: CLR-XXXXXX atau JNZ-XXXXXX">
                </div>
                <button type="submit" class="btn-submit">Cari / Lacak Surat</button>
            </form>

            <?php if ($error_msg): ?>
                <div style="background: #fef2f2; color: #991b1b; padding: 15px; border-radius: 12px; border: 1px solid #fecaca; text-align: center; margin-top: 20px;">
                    <?= $error_msg; ?>
                </div>
            <?php endif; ?>

            <?php if ($data_surat): ?>
                <div class="tracking-result">
                    <h3>Detail Progress Surat</h3>
                    <hr style="margin: 10px 0; border: 0; border-top: 1px solid #a7f3d0;">
                    
                    <p><strong>Kode Unik:</strong> <?= htmlspecialchars($data_surat['kode_unik']); ?></p>
                    <p><strong>Wilayah Kerja:</strong> <?= htmlspecialchars($data_surat['nama_wilker']); ?></p>
                    
                    <?php 
                        $nama_tampil = '';
                        if ($jenis === 'clearance') {
                            $nama_tampil = $data_surat['nama_petugas_keagenan'] . ' (' . $data_surat['keagenan_pelayaran'] . ')';
                        } else {
                            $nama_tampil = $data_surat['nama_pemohon_cargo'];
                        }
                    ?>
                    <p><strong>Pemohon:</strong> <?= htmlspecialchars($nama_tampil); ?></p>
                    
                    <p><strong>Tanggal Pengajuan:</strong> <?= $data_surat['created_at']; ?></p>
                    <p style="margin-top: 10px;"><strong>Status Terkini:</strong> 
                        <?php 
                            $st = $data_surat['status'];
                            $badge_class = 'status-pending';
                            if ($st === 'Sedang Ditinjau') $badge_class = 'status-proses';
                            if ($st === 'Selesai / Diterbitkan') $badge_class = 'status-selesai';
                        ?>
                        <span class="status-badge <?= $badge_class; ?>"><?= $st; ?></span>
                    </p>

                    <?php if ($st === 'Selesai / Diterbitkan' && !empty($data_surat['file_pdf'])): ?>
                        <div style="margin-top: 15px;">
                            <p style="font-size: 0.9rem; color: #064e3b; font-weight: bold;">Surat izin resmi telah diterbitkan oleh admin.</p>
                            <a href="../assets/uploads/<?= htmlspecialchars($data_surat['file_pdf']); ?>" target="_blank" class="btn-download">Download Surat (PDF)</a>
                        </div>
                    <?php endif; ?>

                    <!-- Catatan / Informasi Pelayanan Berwarna Merah (Lebih Sopan & Profesional) -->
                    <div style="margin-top: 20px; padding: 12px 15px; background: #fff5f5; border: 1px solid #feb2b2; border-radius: 8px; color: #c53030; font-size: 0.88rem; line-height: 1.5;">
                        <strong>Perhatian:</strong> Mohon pantau progres secara berkala. Estimasi proses penerbitan surat adalah selambat-lambatnya <strong>3 hari kerja</strong>. Apabila memerlukan proses yang mendesak/cepat, silakan hubungi Admin melalui WhatsApp di nomor <strong>085386045935</strong>.
                    </div>
                </div>
            <?php endif; ?>

            <div style="margin-top: 25px; text-align: center;">
                <a href="index.php" class="btn-back">&larr; Ganti Menu Tracking</a> &nbsp;|&nbsp; 
                <a href="../index.php" class="btn-back">Beranda Utama</a>
            </div>
        </div>
    </div>
</body>
</html>