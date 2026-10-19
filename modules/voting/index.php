<?php
require_once '../../config/database.php';

// Gunakan IP Address / Session sebagai identifier pemilih untuk mencegah spam vote
$identifier = $_SERVER['REMOTE_ADDR'];
$success_vote = false;
$error_vote = '';

// Proses voting jika tombol diklik
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['petugas_id'])) {
    $petugas_id = (int)$_POST['petugas_id'];

    // Cek apakah identifier ini sudah pernah voting sebelumnya
    $stmt_check = $pdo->prepare("SELECT id FROM voting_petugas WHERE identifier_user = ?");
    $stmt_check->execute([$identifier]);

    if ($stmt_check->rowCount() > 0) {
        $error_vote = "Anda sudah melakukan voting sebelumnya dari perangkat/jaringan ini!";
    } else {
        // Simpan vote
        $stmt_insert = $pdo->prepare("INSERT INTO voting_petugas (petugas_id, identifier_user) VALUES (?, ?)");
        $stmt_insert->execute([$petugas_id, $identifier]);
        $success_vote = "Terima kasih! Vote Anda untuk petugas pilihan telah berhasil disimpan.";
    }
}

// Ambil daftar petugas dari database
$stmt_petugas = $pdo->query("SELECT * FROM petugas");
$petugas_list = $stmt_petugas->fetchAll();

// Cek status apakah user sudah vote sebelumnya
$stmt_my_vote = $pdo->prepare("SELECT p.nama_petugas FROM voting_petugas v JOIN petugas p ON v.petugas_id = p.id WHERE v.identifier_user = ?");
$stmt_my_vote->execute([$identifier]);
$my_voted = $stmt_my_vote->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Petugas Terbaik - SIDIA BAPERAN</title>
    <link rel="stylesheet" href="../../assets/css/voting.css?v=<?= time(); ?>">
</head>
<body>
    <script>
        // Ikuti tema (terang/gelap) yang dipilih di halaman beranda
        try { if (localStorage.getItem('sidia_theme') === 'dark') document.body.classList.add('dark-mode'); } catch (e) {}
    </script>

    <div class="container">
        <div class="card">
            <h2>Voting Petugas Terbaik</h2>
            <p class="wilker-note">Wilayah Kerja: <strong>Wilker Induk</strong> — Pilih petugas teladan dengan pelayanan terbaik.</p>

            <?php if ($success_vote): ?>
                <div class="alert-success"><?= $success_vote; ?></div>
            <?php endif; ?>

            <?php if ($error_vote): ?>
                <div class="alert-error">
                    <?= $error_vote; ?>
                </div>
            <?php endif; ?>

            <?php if ($my_voted): ?>
                <div class="alert-info">
                    Anda telah memberikan suara untuk: <strong><?= htmlspecialchars($my_voted['nama_petugas']); ?></strong>
                </div>
            <?php endif; ?>

            <?php if (empty($petugas_list)): ?>
                <p class="empty-state">Belum ada data petugas yang ditambahkan oleh admin.</p>
            <?php else: ?>
                <div class="petugas-grid">
                    <?php foreach ($petugas_list as $p): ?>
                        <div class="petugas-card">
                            <h3><?= htmlspecialchars($p['nama_petugas']); ?></h3>
                            <p><?= htmlspecialchars($p['jabatan']); ?></p>
                            
                            <?php if (!$my_voted): ?>
                                <form action="" method="POST">
                                    <input type="hidden" name="petugas_id" value="<?= $p['id']; ?>">
                                    <button type="submit" class="btn-vote" onclick="return confirm('Yakin ingin memilih petugas ini?');">Pilih / Vote</button>
                                </form>
                            <?php else: ?>
                                <button type="button" class="btn-vote" disabled>Sudah Voting</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="back-wrap">
                <a href="../../index.php" class="btn-back">&larr; Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>