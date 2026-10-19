<?php
require_once '../../config/database.php';
// Ambil data wilker khusus BSN, Kotabaru, Batulicin (ID > 1)
$stmt = $pdo->prepare("SELECT * FROM wilayah_kerja WHERE id > 1");
$stmt->execute();
$wilkers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Wilayah Kerja - Izin Angkut Jenazah</title>
    <link rel="stylesheet" href="../../assets/css/jenazah.css?v=<?= time(); ?>">
</head>
<body>
    <script>
        // Ikuti tema (terang/gelap) yang dipilih di halaman beranda
        try { if (localStorage.getItem('sidia_theme') === 'dark') document.body.classList.add('dark-mode'); } catch (e) {}
    </script>

    <div class="container">
        <div class="card">
            <h2>Pilih Wilayah Kerja Izin Angkut Jenazah</h2>
            <p class="wilker-note">Silakan pilih wilayah kerja tujuan permohonan Anda:</p>
            
            <div class="wilker-grid">
                <?php foreach ($wilkers as $w): ?>
                    <a href="form.php?wilker_id=<?= $w['id']; ?>" class="wilker-card">
                        <?= htmlspecialchars($w['nama_wilker']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="back-wrap">
                <a href="../../index.php" class="btn-back">&larr; Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>