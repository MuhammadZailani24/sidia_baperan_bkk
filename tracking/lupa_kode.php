<?php
require_once '../config/database.php';

$kode_ditemukan = '';
$error_msg = '';
$jenis_terpilih = ''; // Menyimpan jenis surat untuk link tujuan pelacakan

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis = $_POST['jenis'];
    $id_permohonan = trim($_POST['id_permohonan']);
    $jenis_terpilih = $jenis;

    if ($jenis === 'clearance') {
        $stmt = $pdo->prepare("SELECT kode_unik FROM permohonan_clearance WHERE id_permohonan = ?");
        $stmt->execute([$id_permohonan]);
        $result = $stmt->fetch();
        
        if ($result) {
            $kode_ditemukan = $result['kode_unik'];
        } else {
            $error_msg = "ID Permohonan Clearance '$id_permohonan' tidak ditemukan. Pastikan Anda mengetik dengan benar (contoh: CIN-001).";
        }
    } else if ($jenis === 'jenazah') {
        // Logika pencarian kode unik untuk Izin Angkut Jenazah sudah aktif
        $stmt = $pdo->prepare("SELECT kode_unik FROM permohonan_jenazah WHERE id_permohonan = ?");
        $stmt->execute([$id_permohonan]);
        $result = $stmt->fetch();
        
        if ($result) {
            $kode_ditemukan = $result['kode_unik'];
        } else {
            $error_msg = "ID Permohonan Izin Jenazah '$id_permohonan' tidak ditemukan. Pastikan Anda mengetik dengan benar (contoh: SIAJ-001).";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kode Unik - SIDIA BAPERAN</title>
    <link rel="stylesheet" href="../assets/css/tracking.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>Lupa Kode Unik Tracking</h2>
            <p style="margin-bottom: 25px; font-size: 0.9rem; color: #64748b; text-align: center;">Masukkan ID Permohonan (Contoh: CIN-001 atau SIAJ-001) untuk mendapatkan kembali Kode Unik (CLR-XXX / JNZ-XXX) Anda.</p>

            <?php if ($error_msg): ?>
                <div style="background: #fef2f2; color: #991b1b; padding: 15px; border-radius: 12px; border: 1px solid #fecaca; text-align: center; margin-bottom: 20px;">
                    <?= $error_msg; ?>
                </div>
            <?php endif; ?>

            <?php if ($kode_ditemukan): ?>
                <div class="tracking-result" style="text-align: center;">
                    <h3>Kode Unik Anda Ditemukan!</h3>
                    <div style="background: #ffffff; padding: 15px 25px; font-size: 1.5rem; font-weight: 700; color: #059669; border: 2px dashed #34d399; border-radius: 12px; margin: 15px auto; display: inline-block; letter-spacing: 3px;">
                        <?= htmlspecialchars($kode_ditemukan); ?>
                    </div>
                    <p style="font-size: 0.9rem; color: #475569;">Gunakan kode di atas pada menu Tracking untuk melacak status surat Anda.</p>
                    <div style="margin-top: 15px;">
                        <!-- Link dinamis akan otomatis mengarah ke form tracking yang sesuai -->
                        <a href="result.php?jenis=<?= htmlspecialchars($jenis_terpilih); ?>" class="btn-submit" style="text-decoration: none; display: inline-block; width: auto; padding: 10px 20px;">Langsung ke Pelacakan</a>
                    </div>
                </div>
            <?php else: ?>
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Jenis Layanan Surat <span style="color: #ef4444;">*</span></label>
                        <select name="jenis" required>
                            <option value="">-- Pilih Jenis Surat --</option>
                            <option value="clearance">Clearance In</option>
                            <option value="jenazah">Surat Izin Angkut Jenazah</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ID Permohonan <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="id_permohonan" required placeholder="Contoh: CIN-001 atau SIAJ-001">
                    </div>
                    <button type="submit" class="btn-submit">Cari Kode Unik</button>
                </form>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 25px;">
                <a href="index.php" class="btn-back">&larr; Kembali ke Menu Tracking</a>
            </div>
        </div>
    </div>
</body>
</html>