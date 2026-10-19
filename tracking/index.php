<?php
/**
 * Menu Pilihan Tracking (Clearance In & Angkut Jenazah)
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Progress Surat - SIDIA BAPERAN</title>
    <link rel="stylesheet" href="../assets/css/tracking.css?v=<?= time(); ?>">
</head>
<body>
    <script>
        // Ikuti tema (terang/gelap) yang dipilih di halaman beranda
        try { if (localStorage.getItem('sidia_theme') === 'dark') document.body.classList.add('dark-mode'); } catch (e) {}
    </script>

    <div class="container">
        <div class="card">
            <h2>Cek Progress Surat Diajukan</h2>
            <p class="wilker-note">Pilih jenis surat yang ingin Anda lacak status progresnya:</p>

            <div class="menu-grid">
                <a href="result.php?jenis=clearance" class="menu-option">
                    <div class="opsi-judul">Clearance In</div>
                    <span class="opsi-desc">Lacak surat izin clearance kapal</span>
                </a>
                <a href="result.php?jenis=jenazah" class="menu-option">
                    <div class="opsi-judul">Izin Angkut Jenazah</div>
                    <span class="opsi-desc">Lacak surat izin pengangkutan jenazah</span>
                </a>
            </div>

            <!-- Tombol Lupa Kode Unik -->
            <div class="lupa-wrap">
                <a href="lupa_kode.php" class="lupa-link">🔍 Lupa Kode Unik Tracking? Klik di sini</a>
            </div>

            <div class="footer-nav">
                <a href="../index.php" class="btn-back">&larr; Kembali ke Beranda Utama</a>
            </div>
        </div>
    </div>
</body>
</html>