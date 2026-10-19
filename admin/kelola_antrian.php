<?php
require_once '../config/database.php'; 

$current_page = basename($_SERVER['PHP_SELF']);

// --- BLOK KHUSUS AJAX (AUTO REFRESH DATA SAJA) ---
if (isset($_GET['ajax'])) {
    $stmt_list = $pdo->query("SELECT * FROM antrian ORDER BY id ASC");
    $data_antrian = $stmt_list->fetchAll();

    // Cek apakah ada yang sedang dilayani di loket
    $is_serving = false;
    foreach ($data_antrian as $row) {
        if ($row['status'] == 'Sedang dilayani') {
            $is_serving = true;
            break;
        }
    }

    if (count($data_antrian) > 0) {
        foreach ($data_antrian as $row) {
            ?>
            <tr>
                <td style="font-size: 1.3rem; font-weight: bold; color: #0f766e;"><?= $row['nomor_antrian']; ?></td>
                <td><?= date('d-m-Y H:i', strtotime($row['waktu_ambil'])); ?> WIB</td>
                <td>
                    <?php if ($row['status'] == 'Sedang dalam antrian'): ?>
                        <span class="badge-wait">⏳ Menunggu</span>
                    <?php elseif ($row['status'] == 'Sedang dilayani'): ?>
                        <span class="badge-process">📢 Sedang Dilayani</span>
                    <?php else: ?>
                        <span class="badge-done">✅ Selesai</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($row['status'] == 'Sedang dalam antrian'): ?>
                        
                        <?php if ($is_serving): ?>
                            <!-- Loket penuh, tombol panggil mati -->
                            <span class="btn-action btn-disabled" onclick="alert('⚠ Loket penuh! Selesaikan dulu nomor yang sedang dilayani.');">📢 Panggil</span>
                        <?php else: ?>
                            <!-- Loket kosong (misal baru buka / baru reset), PANGGIL MANUAL -->
                            <a href="<?= $current_page; ?>?action=panggil&id=<?= $row['id']; ?>" class="btn-action btn-panggil">📢 Panggil Manual</a>
                        <?php endif; ?>

                    <?php elseif ($row['status'] == 'Sedang dilayani'): ?>
                        <!-- Tombol Sakti: Selesaikan yang ini, dan langsung tarik yang berikutnya -->
                        <a href="<?= $current_page; ?>?action=selesai&id=<?= $row['id']; ?>" class="btn-action btn-selesai">✔ Selesai & Panggil Berikutnya</a>
                    <?php else: ?>
                        <span style="color: #94a3b8; font-size: 0.9rem; font-weight: 500;">Tuntas</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php
        }
    } else {
        echo '<tr><td colspan="4" style="text-align: center; color: #64748b; padding: 40px 0;">Belum ada pengunjung yang mengambil antrian hari ini.</td></tr>';
    }
    exit; 
}
// --- AKHIR BLOK AJAX ---


// --- PROSES AKSI KLIK ADMIN ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_antrian = (int) $_GET['id'];
    $action = $_GET['action'];

    // 1. Aksi Panggil Manual (Khusus untuk memulai / A-001)
    if ($action == 'panggil') {
        $stmt_check = $pdo->query("SELECT COUNT(*) FROM antrian WHERE status = 'Sedang dilayani'");
        if ($stmt_check->fetchColumn() > 0) {
            header("Location: " . $current_page . "?msg=error_panggil");
            exit;
        } else {
            $stmt = $pdo->prepare("UPDATE antrian SET status = 'Sedang dilayani' WHERE id = ?");
            $stmt->execute([$id_antrian]);
            header("Location: " . $current_page . "?msg=panggil");
            exit;
        }
    }
    
    // 2. Aksi Centang Selesai & PANGGIL OTOMATIS SELANJUTNYA (Khusus A-002, A-003, dst)
    if ($action == 'selesai') {
        // Selesaikan dokumen saat ini
        $stmt = $pdo->prepare("UPDATE antrian SET status = 'Sudah Dilayani' WHERE id = ?");
        $stmt->execute([$id_antrian]);

        // Cari nomor antrean berikutnya yang berstatus menunggu (Secara Urut)
        $stmt_next = $pdo->query("SELECT id FROM antrian WHERE status = 'Sedang dalam antrian' ORDER BY id ASC LIMIT 1");
        $next_id = $stmt_next->fetchColumn();

        if ($next_id) {
            // Jika ada antrean selanjutnya, langsung panggil otomatis!
            $stmt_call = $pdo->prepare("UPDATE antrian SET status = 'Sedang dilayani' WHERE id = ?");
            $stmt_call->execute([$next_id]);
            header("Location: " . $current_page . "?msg=selesai_auto_panggil");
        } else {
            // Jika antrean di belakangnya sudah habis/kosong
            header("Location: " . $current_page . "?msg=selesai_habis");
        }
        exit;
    }
}

// 3. Aksi Reset
if (isset($_POST['reset_antrian'])) {
    $pdo->query("TRUNCATE TABLE antrian");
    header("Location: " . $current_page . "?msg=reset");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Antrian - Admin SIDIA BAPERAN</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f1f5f9; padding: 30px; margin: 0; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border-top: 6px solid #0f766e; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #e2e8f0; }
        h2 { margin: 0; color: #0f766e; font-size: 1.8rem; }
        
        .btn-reset { background-color: #ef4444; color: white; border: none; padding: 12px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2); }
        .btn-reset:hover { background-color: #dc2626; transform: translateY(-2px); }
        
        table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 10px; }
        th, td { padding: 16px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background-color: #f8fafc; color: #475569; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
        
        .badge-wait { background-color: #fef3c7; color: #b45309; padding: 6px 12px; border-radius: 50px; font-size: 0.85rem; font-weight: 700; display: inline-block;}
        .badge-process { background-color: #e0f2fe; color: #0369a1; padding: 6px 12px; border-radius: 50px; font-size: 0.85rem; font-weight: 700; display: inline-block;}
        .badge-done { background-color: #dcfce7; color: #15803d; padding: 6px 12px; border-radius: 50px; font-size: 0.85rem; font-weight: 700; display: inline-block;}
        
        .btn-action { text-decoration: none; padding: 8px 16px; border-radius: 6px; font-size: 0.9rem; font-weight: 600; display: inline-block; transition: 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.1); cursor: pointer;}
        .btn-panggil { background-color: #0ea5e9; color: white; }
        .btn-panggil:hover { background-color: #0284c7; }
        .btn-selesai { background-color: #10b981; color: white; border: 2px solid #059669; }
        .btn-selesai:hover { background-color: #059669; transform: scale(1.03); }
        .btn-disabled { background-color: #cbd5e1; color: #64748b; cursor: not-allowed; box-shadow: none; }
        
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; font-weight: 500; }
        .alert-info { background-color: #f0fdfa; color: #0f766e; border: 1px solid #ccfbf1; }
        .alert-auto { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .alert-error { background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h2>Loket Admin Antrian</h2>
                <span id="status-refresh" style="font-size: 0.85rem; color: #10b981; font-weight: bold;">(🟢 Live Auto-Refresh Aktif)</span>
            </div>
            
            <form action="" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin mereset antrian?\n\nSemua data antrian hari ini akan dihapus dan besok akan dimulai dari A-001 lagi.');">
                <button type="submit" name="reset_antrian" class="btn-reset">🔄 Reset & Tutup Loket</button>
            </form>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] == 'error_panggil'): ?>
                <div class="alert alert-error">
                    ⚠️ <b>GAGAL MEMANGGIL:</b> Selesaikan terlebih dahulu nomor antrian yang <b>Sedang Dilayani</b> sebelum memanggil nomor baru!
                </div>
            <?php elseif ($_GET['msg'] == 'selesai_auto_panggil'): ?>
                <div class="alert alert-auto">
                    🚀 <b>SELESAI & OTOMATIS PANGGIL:</b> Layanan tuntas. Sistem langsung memanggil nomor urut selanjutnya ke loket!
                </div>
            <?php elseif ($_GET['msg'] == 'selesai_habis'): ?>
                <div class="alert alert-info">
                    ✅ <b>PELAYANAN TUNTAS:</b> Semua antrean saat ini sudah habis dilayani. Menunggu pengunjung baru...
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <?php 
                        if ($_GET['msg'] == 'panggil') echo "📢 Panggilan manual berhasil dikirim ke layar pengunjung.";
                        if ($_GET['msg'] == 'reset') echo "♻️ Antrian berhasil di-reset ke kondisi awal (A-001).";
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>No. Antrian</th>
                    <th>Waktu Pengambilan</th>
                    <th>Status</th>
                    <th>Aksi Admin</th>
                </tr>
            </thead>
            <tbody id="data-tabel">
                <!-- Data akan dimuat oleh AJAX di sini -->
            </tbody>
        </table>
        
        <br><br>
        <a href="index.php" style="color: #64748b; text-decoration: none; font-weight: 500;">&larr; Kembali ke Dashboard Utama Admin</a>
    </div>

    <!-- SCRIPT AJAX ANTRIAN -->
    <script>
        function muatData() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '<?= $current_page; ?>?ajax=1', true);
            xhr.onload = function() {
                if (this.status == 200) {
                    document.getElementById('data-tabel').innerHTML = this.responseText;
                }
            }
            xhr.send();
        }
        
        muatData();
        setInterval(muatData, 2000); // Polling antrian secara cepat (2 detik)
    </script>
</body>
</html>