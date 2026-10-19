<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SIDIA BAPERAN</title>
    <!-- Tambahan parameter waktu agar browser selalu memuat CSS versi paling baru -->
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= time(); ?>">
</head>
<body>
    <!-- Skrip untuk mengikuti tema (terang/gelap) yang dipilih di halaman utama pengunjung -->
    <script>
        try { if (localStorage.getItem('sidia_theme') === 'dark') document.body.classList.add('dark-mode'); } catch (e) {}
    </script>

    <div class="container">
        <!-- BAR NAVIGASI ADMIN -->
        <div class="nav-admin">
            <div>
                <strong>Panel Administrator</strong> — Selamat datang, <?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?>
            </div>
            <div>
                <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari panel admin?');">Logout &rarr;</a>
            </div>
        </div>

        <!-- KARTU UTAMA -->
        <div class="card">
            <h2>Menu Pengelolaan Sistem</h2>
            <p style="color: var(--muted); margin-bottom: 25px;">Pilih modul di bawah ini untuk mengelola data permohonan, antrian, dan voting petugas secara real-time.</p>

            <!-- GRID MENU (Otomatis menyesuaikan layar HP/PC) -->
            <div class="dashboard-grid">
                <a href="kelola_clearance.php" class="dashboard-card">
                    <h3>Kelola Clearance In</h3>
                    <p>Verifikasi status, kelola data kapal, dan upload dokumen persetujuan.</p>
                </a>
                
                <a href="kelola_jenazah.php" class="dashboard-card">
                    <h3>Kelola Angkut Jenazah</h3>
                    <p>Verifikasi status cargo pemohon dan upload surat persetujuan.</p>
                </a>
                
                <a href="kelola_antrian.php" class="dashboard-card">
                    <h3>Kelola Antrian Dokumen</h3>
                    <p>Panggil nomor antrian pengunjung, tandai selesai, dan reset loket harian.</p>
                </a>
                
                <a href="kelola_voting.php" class="dashboard-card">
                    <h3>Kelola Voting Petugas</h3>
                    <p>Kelola daftar nama petugas dan pantau grafik poin suara secara live.</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>