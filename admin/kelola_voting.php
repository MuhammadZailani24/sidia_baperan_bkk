<?php
session_start();
if (!isset($_SESSION['admin_logged'])) { header("Location: index.php"); exit; }
require_once '../config/database.php';

// --- BLOK KHUSUS AJAX (AUTO REFRESH DATA SAJA) ---
if (isset($_GET['ajax'])) {
    $stmt_rekap = $pdo->query("
        SELECT p.*, COUNT(v.id) as total_suara 
        FROM petugas p 
        LEFT JOIN voting_petugas v ON p.id = v.petugas_id 
        GROUP BY p.id 
        ORDER BY total_suara DESC
    ");
    $rekap_voting = $stmt_rekap->fetchAll();
    
    if (empty($rekap_voting)) {
        echo '<tr><td colspan="4" style="text-align: center; color: #777;">Belum ada data petugas.</td></tr>';
    } else {
        $no = 1; 
        foreach ($rekap_voting as $row) {
            ?>
            <tr>
                <td><strong>#<?= $no++; ?>. <?= htmlspecialchars($row['nama_petugas']); ?></strong></td>
                <td><?= htmlspecialchars($row['jabatan']); ?></td>
                <td>
                    <span style="font-weight: bold; color: #e83e8c; font-size: 1.1rem;"><?= $row['total_suara']; ?> Suara</span>
                    <div style="background: #e9ecef; border-radius: 4px; margin-top: 5px; height: 10px; width: 100%; overflow: hidden;">
                        <div style="background: #e83e8c; height: 100%; width: <?= min($row['total_suara'] * 10, 100); ?>%;"></div>
                    </div>
                </td>
                <td>
                    <a href="kelola_voting.php?hapus=<?= $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Hapus petugas ini?');">Hapus</a>
                </td>
            </tr>
            <?php
        }
    }
    exit; // Stop render HTML lain saat AJAX
}
// --- AKHIR BLOK AJAX ---

// Tambah Petugas Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_petugas'])) {
    $nama_petugas = trim($_POST['nama_petugas']);
    $jabatan = trim($_POST['jabatan']);

    $stmt = $pdo->prepare("INSERT INTO petugas (nama_petugas, jabatan) VALUES (?, ?)");
    $stmt->execute([$nama_petugas, $jabatan]);
    header("Location: kelola_voting.php");
    exit;
}

// Hapus Petugas
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $pdo->prepare("DELETE FROM petugas WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: kelola_voting.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Voting Petugas - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= time(); ?>">
</head>
<body>
    <div class="container">
        <div class="nav-admin">
            <div>
                <strong>Kelola Petugas & Grafik Voting</strong>
                <span id="status-refresh" style="font-size: 0.8rem; color: #10b981; margin-left: 10px;">(🟢 Live Auto-Refresh Aktif)</span>
            </div>
            <div><a href="dashboard.php">&larr; Kembali ke Dashboard</a></div>
        </div>

        <div class="card" style="margin-bottom: 20px;">
            <h2>Tambah Petugas Baru</h2>
            <form action="" method="POST" style="margin-top: 15px;">
                <div class="form-group">
                    <label>Nama Petugas & Gelar</label>
                    <input type="text" name="nama_petugas" class="input-ketik" required placeholder="Contoh: Dr. Ahmad Fauzi">
                </div>
                <div class="form-group">
                    <label>Jabatan / Peran</label>
                    <input type="text" name="jabatan" class="input-ketik" required placeholder="Contoh: Analis Kekarantinaan">
                </div>
                <button type="submit" name="tambah_petugas" class="btn btn-primary">Simpan Petugas Baru</button>
            </form>
        </div>

        <div class="card">
            <h2>Rekapitulasi Poin Voting Petugas (Real-Time)</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Peringkat / Nama Petugas</th>
                        <th>Jabatan</th>
                        <th>Total Poin Suara</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="data-tabel">
                    <!-- Data akan dimuat oleh AJAX di sini -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- SCRIPT AJAX VOTING -->
    <script>
        let isEditing = false;
        const statusRefresh = document.getElementById('status-refresh');

        // Hentikan refresh jika admin sedang mengetik
        document.addEventListener('focusin', function(e) {
            if(e.target.classList.contains('input-ketik')) {
                isEditing = true;
                statusRefresh.innerHTML = '(⏸️ Mengetik...)';
                statusRefresh.style.color = '#f59e0b';
            }
        });

        // Lanjutkan refresh setelah form kosong/admin tidak fokus
        document.addEventListener('focusout', function(e) {
            setTimeout(() => {
                let inputs = document.querySelectorAll('.input-ketik');
                let hasValue = false;
                inputs.forEach(input => { if(input.value.trim() !== '') hasValue = true; });
                
                if (!hasValue) {
                    isEditing = false;
                    statusRefresh.innerHTML = '(🟢 Live Auto-Refresh Aktif)';
                    statusRefresh.style.color = '#10b981';
                }
            }, 500);
        });

        function muatData() {
            if(isEditing) return; // Abaikan request jika sedang mengetik
            
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'kelola_voting.php?ajax=1', true);
            xhr.onload = function() {
                if (this.status == 200) {
                    document.getElementById('data-tabel').innerHTML = this.responseText;
                }
            }
            xhr.send();
        }

        muatData();
        setInterval(muatData, 4000); // Polling grafik voting setiap 4 detik
    </script>
</body>
</html>