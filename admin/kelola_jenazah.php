<?php
session_start();
if (!isset($_SESSION['admin_logged'])) { header("Location: index.php"); exit; }
require_once '../config/database.php';

// --- BLOK KHUSUS AJAX (AUTO REFRESH DATA SAJA) ---
if (isset($_GET['ajax'])) {
    $stmt = $pdo->query("SELECT p.*, w.nama_wilker FROM permohonan_jenazah p JOIN wilayah_kerja w ON p.wilayah_kerja_id = w.id ORDER BY p.created_at DESC");
    $data = $stmt->fetchAll();
    
    if (empty($data)) {
        echo '<tr><td colspan="4" style="text-align: center; color: #777;">Belum ada permohonan masuk.</td></tr>';
    } else {
        foreach ($data as $row) {
            ?>
            <tr>
                <td>
                    <strong style="color: #059669; font-size: 1.1rem;"><?= htmlspecialchars($row['kode_unik']); ?></strong><br>
                    <span class="badge badge-info" style="margin-top: 5px; display: inline-block;"><?= htmlspecialchars($row['nama_wilker']); ?></span>
                </td>
                <td>
                    <strong><?= htmlspecialchars($row['nama_pemohon_cargo']); ?></strong><br>
                    <small style="color: #64748b;">Tanggal: <?= date('d/m/Y H:i', strtotime($row['created_at'])); ?></small>
                </td>
                <td>
                    <strong>Telp/HP:</strong> <?= htmlspecialchars($row['no_hp_pemohon']); ?><br>
                    <small style="color: #64748b;"><strong>Perusahaan:</strong> <?= htmlspecialchars($row['perusahaan_cargo']); ?></small>
                </td>
                <td>
                    <form action="kelola_jenazah.php" method="POST" enctype="multipart/form-data" style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <input type="hidden" name="id" value="<?= $row['id']; ?>">
                        <div style="margin-bottom: 10px;">
                            <select name="status" class="input-interaktif" style="padding: 6px; font-size: 0.8rem; border-radius: 6px; width: 100%;">
                                <option value="Pending" <?= $row['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Sedang Ditinjau" <?= $row['status'] === 'Sedang Ditinjau' ? 'selected' : ''; ?>>Sedang Ditinjau</option>
                                <option value="Selesai / Diterbitkan" <?= $row['status'] === 'Selesai / Diterbitkan' ? 'selected' : ''; ?>>Selesai / Diterbitkan</option>
                            </select>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <label style="font-size: 0.75rem; color: #475569; display:block; margin-bottom: 3px;">Upload PDF:</label>
                            <input type="file" name="file_pdf" class="input-interaktif" accept="application/pdf" style="font-size: 0.75rem; padding: 5px; width: 100%; box-sizing: border-box;">
                            <?php if (!empty($row['file_pdf'])): ?>
                                <div style="margin-top: 5px;">
                                    <a href="../assets/uploads/<?= $row['file_pdf']; ?>" target="_blank" style="font-size: 0.75rem; color: #0ea5e9; font-weight: bold; text-decoration: none;">📄 Lihat PDF Saat Ini</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-success input-interaktif" style="width: 100%; border-radius: 6px; padding: 8px; font-size: 0.8rem; cursor: pointer;">Simpan Perubahan</button>
                    </form>
                </td>
            </tr>
            <?php
        }
    }
    exit;
}
// --- AKHIR BLOK AJAX ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    $file_pdf = null;

    if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file_pdf']['tmp_name'];
        $file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9._]/", "", basename($_FILES['file_pdf']['name']));
        $upload_dir = '../assets/uploads/';
        
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $file_pdf = $file_name;
        }
    }

    if ($file_pdf) {
        $stmt = $pdo->prepare("UPDATE permohonan_jenazah SET status = ?, file_pdf = ? WHERE id = ?");
        $stmt->execute([$status, $file_pdf, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE permohonan_jenazah SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }

    header("Location: kelola_jenazah.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Izin Angkut Jenazah - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= time(); ?>">
    <style>
        .filter-panel {
            background: #f8fafc;
            padding: 15px 20px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .filter-title {
            font-size: 0.95rem;
            font-weight: bold;
            color: #334155;
            margin: 0;
            margin-right: 10px;
        }
        .btn-unduh {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            color: white;
            transition: 0.2s;
        }
        .btn-unduh-pdf { background: #0ea5e9; }
        .btn-unduh-pdf:hover { background: #0284c7; }
        .btn-unduh-excel { background: #10b981; }
        .btn-unduh-excel:hover { background: #059669; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-admin">
            <div><strong>Kelola Permohonan Izin Angkut Jenazah</strong> <span id="status-refresh" style="font-size: 0.8rem; color: #10b981; margin-left: 10px;">(🟢 Auto-Refresh Aktif)</span></div>
            <div><a href="dashboard.php">&larr; Kembali ke Dashboard</a></div>
        </div>

        <div class="card" style="padding: 30px;">
            <h2>Daftar Permohonan Izin Angkut Jenazah</h2>
            
            <!-- PANEL FILTER UNDUH LAPORAN -->
            <form id="form-filter" method="GET" target="_blank" class="filter-panel">
                <p class="filter-title">Filter Unduhan:</p>
                
                <select name="filter_waktu" id="filter_waktu" class="input-interaktif" style="padding: 8px; border-radius: 5px; border: 1px solid #cbd5e1;" onchange="toggleCustomDate()">
                    <option value="semua">Semua Data</option>
                    <option value="hari_ini">Hari Ini</option>
                    <option value="bulan_ini">Bulan Ini</option>
                    <option value="tahun_ini">Tahun Ini</option>
                    <option value="custom">Custom Tanggal</option>
                </select>

                <div id="custom-date" style="display: none; align-items: center; gap: 8px;">
                    <input type="date" name="tgl_mulai" class="input-interaktif" style="padding: 8px; border-radius: 5px; border: 1px solid #cbd5e1;">
                    <span style="font-size: 0.9rem; color: #64748b;">s/d</span>
                    <input type="date" name="tgl_akhir" class="input-interaktif" style="padding: 8px; border-radius: 5px; border: 1px solid #cbd5e1;">
                </div>

                <div style="margin-left: auto; display: flex; gap: 10px;">
                    <button type="button" onclick="submitUnduh('pdf')" class="btn-unduh btn-unduh-pdf input-interaktif">📄 Unduh PDF</button>
                    <button type="button" onclick="submitUnduh('excel')" class="btn-unduh btn-unduh-excel input-interaktif">📊 Unduh Excel</button>
                </div>
            </form>

            <div style="overflow-x: auto;">
                <table class="table" style="min-width: 800px;">
                    <thead>
                        <tr>
                            <th>Kode & Wilker</th>
                            <th>Pemohon (Cargo)</th>
                            <th>Kontak & Perusahaan</th>
                            <th style="width: 220px;">Status & Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="data-tabel">
                        <!-- Data akan di-load oleh AJAX di sini -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SCRIPT AJAX AUTO REFRESH & FILTER -->
    <script>
        // --- LOGIKA FILTER TANGGAL ---
        function toggleCustomDate() {
            var filter = document.getElementById('filter_waktu').value;
            var customDiv = document.getElementById('custom-date');
            if (filter === 'custom') {
                customDiv.style.display = 'flex';
            } else {
                customDiv.style.display = 'none';
            }
        }

        function submitUnduh(jenis) {
            var form = document.getElementById('form-filter');
            if (jenis === 'pdf') {
                form.action = 'cetak_laporan_jenazah.php';
            } else if (jenis === 'excel') {
                form.action = 'export_excel_jenazah.php';
            }
            form.submit();
        }

        // --- LOGIKA AJAX AUTO REFRESH ---
        let isEditing = false;
        const statusRefresh = document.getElementById('status-refresh');

        document.addEventListener('focusin', function(e) {
            if(e.target.classList.contains('input-interaktif')) {
                isEditing = true;
                statusRefresh.innerHTML = '(⏸️ Interaksi Aktif...)';
                statusRefresh.style.color = '#f59e0b';
            }
        });

        document.addEventListener('focusout', function(e) {
            setTimeout(() => {
                let fileInputs = document.querySelectorAll('input[type="file"]');
                let hasFile = false;
                fileInputs.forEach(input => { if(input.files.length > 0) hasFile = true; });
                
                let filterFocused = document.activeElement && document.activeElement.classList.contains('input-interaktif');
                
                if (!hasFile && !filterFocused) {
                    isEditing = false;
                    statusRefresh.innerHTML = '(🟢 Auto-Refresh Aktif)';
                    statusRefresh.style.color = '#10b981';
                }
            }, 500);
        });

        function muatData() {
            if(isEditing) return; 
            
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'kelola_jenazah.php?ajax=1', true);
            xhr.onload = function() {
                if (this.status == 200) {
                    document.getElementById('data-tabel').innerHTML = this.responseText;
                }
            }
            xhr.send();
        }

        muatData();
        setInterval(muatData, 4000); 
    </script>
</body>
</html>