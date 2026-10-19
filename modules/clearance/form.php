<?php
require_once '../../config/database.php';

$success_code = '';
$success_id = '';

// Logika Pembuatan ID Permohonan Otomatis (CIN-001, dst)
$stmt_last_id = $pdo->query("SELECT id_permohonan FROM permohonan_clearance ORDER BY id DESC LIMIT 1");
$last_id_row = $stmt_last_id->fetch();

if ($last_id_row && !empty($last_id_row['id_permohonan'])) {
    $last_num = (int) substr($last_id_row['id_permohonan'], 4);
    $next_num = $last_num + 1;
    $id_permohonan_display = 'CIN-' . str_pad($next_num, 3, '0', STR_PAD_LEFT);
} else {
    $id_permohonan_display = 'CIN-001';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_permohonan      = trim($_POST['id_permohonan']);
    $email_pemohon      = trim($_POST['email_pemohon']);
    $tanggal_kedatangan = trim($_POST['tanggal_kedatangan']);
    $jam_kedatangan     = trim($_POST['jam_kedatangan']);
    $keagenan_pelayaran = trim($_POST['keagenan_pelayaran']);
    $nama_petugas       = trim($_POST['nama_petugas_keagenan']);
    $nama_kapal         = trim($_POST['nama_kapal']);
    $besar_kapal        = trim($_POST['besar_kapal_grt']);
    $jumlah_abk         = (int)$_POST['jumlah_abk'];
    $jumlah_penumpang   = empty($_POST['jumlah_penumpang']) ? 0 : (int)$_POST['jumlah_penumpang'];
    $tgl_sscec          = trim($_POST['tanggal_terbit_sscec']);
    $tgl_sp3k           = trim($_POST['tanggal_terbit_sp3k']);
    
    $pelabuhan_asal = $_POST['pelabuhan_asal'];
    if ($pelabuhan_asal === 'Yang lain') {
        $pelabuhan_asal = trim($_POST['pelabuhan_asal_lain']);
    }

    $posisi_kapal = $_POST['posisi_kapal'];
    if ($posisi_kapal === 'Yang lain') {
        $posisi_kapal = trim($_POST['posisi_kapal_lain']);
    }

    $wilayah_kerja_id = 1;

    do {
        $kode_unik = 'CLR-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        $stmt_check = $pdo->prepare("SELECT id FROM permohonan_clearance WHERE kode_unik = ?");
        $stmt_check->execute([$kode_unik]);
    } while ($stmt_check->rowCount() > 0);

    $sql = "INSERT INTO permohonan_clearance 
            (id_permohonan, kode_unik, wilayah_kerja_id, email_pemohon, tanggal_kedatangan, jam_kedatangan, keagenan_pelayaran, nama_petugas_keagenan, nama_kapal, besar_kapal_grt, jumlah_abk, jumlah_penumpang, tanggal_terbit_sscec, tanggal_terbit_sp3k, pelabuhan_asal, posisi_kapal) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $id_permohonan, $kode_unik, $wilayah_kerja_id, $email_pemohon, $tanggal_kedatangan, $jam_kedatangan, 
        $keagenan_pelayaran, $nama_petugas, $nama_kapal, $besar_kapal, 
        $jumlah_abk, $jumlah_penumpang, $tgl_sscec, $tgl_sp3k, 
        $pelabuhan_asal, $posisi_kapal
    ]);

    // === KIRIM NOTIFIKASI TELEGRAM DENGAN TOMBOL AKSI CEPAT ===
    $pesan_telegram = "🚢 <b>PERMOHONAN CLEARANCE IN BARU</b>\n\n" .
                      "🆔 ID Permohonan: <b>{$id_permohonan}</b>\n" .
                      "🔑 Kode Unik: <code>{$kode_unik}</code>\n" .
                      "📧 Email: {$email_pemohon}\n" .
                      "🏢 Keagenan: {$keagenan_pelayaran}\n" .
                      "👤 Petugas: {$nama_petugas}\n" .
                      "⚓ Nama Kapal: {$nama_kapal} ({$besar_kapal} GRT)\n" .
                      "📅 Kedatangan: {$tanggal_kedatangan} Pukul {$jam_kedatangan}\n" .
                      "📍 Posisi: {$posisi_kapal}\n\n" .
                      "<i>Silakan klik tombol di bawah untuk memproses permohonan ini:</i>";
    
    $keyboard_clearance = [
        'inline_keyboard' => [
            [
                ['text' => '⏳ Ubah ke Sedang Ditinjau', 'callback_data' => "upd_clr_Tinjau_{$id_permohonan}"]
            ]
        ]
    ];

    if (function_exists('kirimNotifikasiTelegram')) {
        kirimNotifikasiTelegram($pesan_telegram, $keyboard_clearance);
    }

    $subjek_email = "Permohonan Clearance In Baru - " . $id_permohonan;
    $pesan_email = "<h3>Permohonan Clearance In Baru Masuk</h3>
                    <p>Halo Admin,</p>
                    <p>Ada permohonan Clearance In baru yang perlu ditinjau:</p>
                    <ul>
                        <li><strong>ID Permohonan:</strong> {$id_permohonan}</li>
                        <li><strong>Kode Unik:</strong> {$kode_unik}</li>
                        <li><strong>Email Pemohon:</strong> {$email_pemohon}</li>
                        <li><strong>Keagenan Pelayaran:</strong> {$keagenan_pelayaran}</li>
                        <li><strong>Nama Kapal:</strong> {$nama_kapal} ({$besar_kapal} GRT)</li>
                        <li><strong>Tanggal Kedatangan:</strong> {$tanggal_kedatangan} ({$jam_kedatangan})</li>
                    </ul>
                    <p>Silakan login ke Admin Panel SIDIA BAPERAN untuk memproses permohonan ini.</p>";
    
    if (function_exists('kirimNotifikasiEmail')) kirimNotifikasiEmail($subjek_email, $pesan_email);
    // ==========================================

    $success_code = $kode_unik;
    $success_id = $id_permohonan;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Clearance In - SIDIA BAPERAN</title>
    <link rel="stylesheet" href="../../assets/css/clearance.css?v=<?= time(); ?>">
</head>
<body>
    <script>
        // Ikuti tema (terang/gelap) yang dipilih di halaman beranda
        try { if (localStorage.getItem('sidia_theme') === 'dark') document.body.classList.add('dark-mode'); } catch (e) {}
    </script>

    <div class="container">
        <div class="card">
            <h2>Permohonan Clearance In</h2>
            <p class="wilker-note">Wilayah Kerja: <strong>Wilker Induk</strong></p>

            <?php if ($success_code): ?>
                <div class="alert-success">
                    <h3>Permohonan Berhasil Dikirim!</h3>
                    <p>ID Permohonan Anda: <strong><?= $success_id; ?></strong></p>
                    <p>Simpan kode unik berikut untuk melacak progress surat Anda:</p>
                    <div class="code-box"><?= $success_code; ?></div>
                    <p class="note">*Jika Anda lupa kode unik ini, Anda bisa memulihkannya menggunakan ID Permohonan.</p>
                    
                    <div class="success-actions">
                        <a href="cetak_bukti.php?id=<?= $success_id; ?>" target="_blank" class="btn-download">📄 Unduh Bukti Permohonan (PDF)</a>
                    </div>
                </div>
                <div class="back-wrap">
                    <a href="../../index.php" class="btn-back">&larr; Kembali ke Beranda</a>
                </div>
            <?php else: ?>
                <form action="" method="POST">
                    
                    <div class="form-group">
                        <label>ID Permohonan <span class="required">*</span></label>
                        <input type="text" name="id_permohonan" value="<?= $id_permohonan_display; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Alamat Email (Wajib Gmail) <span class="required">*</span></label>
                        <span class="help-text">*Notifikasi status dokumen akan dikirimkan ke email ini.</span>
                        <input type="text" inputmode="email" name="email_pemohon" required placeholder="Contoh: ptbanjarmasin@gmail.com" pattern=".*@gmail\.com$" title="Harap masukkan alamat email berakhiran @gmail.com">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Tanggal Kedatangan Kapal <span class="required">*</span></label>
                            <input type="date" name="tanggal_kedatangan" required>
                        </div>
                        <div class="form-group">
                            <label>Jam Kedatangan Kapal <span class="required">*</span></label>
                            <input type="time" name="jam_kedatangan" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Keagenan Pelayaran <span class="required">*</span></label>
                        <span class="help-text">*Perlu menulis PT. dan tidak boleh disingkat, Contoh: PT. Banjarmasin Sehat Sugar</span>
                        <input type="text" name="keagenan_pelayaran" required placeholder="Jawaban Anda">
                    </div>

                    <div class="form-group">
                        <label>Nama Petugas Keagenan <span class="required">*</span></label>
                        <input type="text" name="nama_petugas_keagenan" required placeholder="Jawaban Anda">
                    </div>

                    <div class="form-group">
                        <label>Nama Kapal <span class="required">*</span></label>
                        <span class="help-text">*hanya diinput masing-masing per kapal baik TB atau BG</span>
                        <input type="text" name="nama_kapal" required placeholder="Jawaban Anda">
                    </div>

                    <div class="form-group">
                        <label>Besar Kapal (GRT) <span class="required">*</span></label>
                        <span class="help-text">*hanya diinput masing-masing per kapal baik TB atau BG</span>
                        <input type="text" name="besar_kapal_grt" required placeholder="Jawaban Anda">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Jumlah ABK <span class="required">*</span></label>
                            <input type="number" name="jumlah_abk" min="0" required placeholder="Jawaban Anda">
                        </div>
                        <div class="form-group">
                            <label>Jumlah Penumpang</label>
                            <input type="number" name="jumlah_penumpang" min="0" placeholder="Jawaban Anda">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Tanggal Terbit Dokumen SSCEC/SSCC <span class="required">*</span></label>
                            <input type="date" name="tanggal_terbit_sscec" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Terbit Dokumen SP3K/Medical Chest <span class="required">*</span></label>
                            <input type="date" name="tanggal_terbit_sp3k" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 30px;">
                        <label>Pelabuhan Asal <span class="required">*</span></label>
                        <div class="radio-group">
                            <?php 
                                $pelabuhan_opsi = ['Kelanis', 'Rangga Ilung', 'Sungai Puting', 'Tanjung Perak', 'Tanjung Priok'];
                                foreach($pelabuhan_opsi as $p): 
                            ?>
                                <label class="radio-label">
                                    <input type="radio" name="pelabuhan_asal" value="<?= $p; ?>" required> <?= $p; ?>
                                </label>
                            <?php endforeach; ?>
                            <label class="radio-label">
                                <input type="radio" name="pelabuhan_asal" value="Yang lain" id="radio_pelabuhan_lain"> 
                                <span style="white-space: nowrap;">Yang lain:</span>
                                <input type="text" name="pelabuhan_asal_lain" class="input-lainnya" onclick="document.getElementById('radio_pelabuhan_lain').checked = true;" placeholder="Ketik di sini...">
                            </label>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 30px; margin-bottom: 30px;">
                        <label>Posisi Kapal <span class="required">*</span></label>
                        <div class="radio-group">
                            <?php 
                                $posisi_opsi = ['Alalak', 'Banjaraya', 'Barito Kuala', 'Basirih', 'Kawi', 'Mantuil', 'Martapura Baru', 'Muara Tamban', 'Rede Trisakti', 'Pasir Mas', 'Taboneo', 'Trisakti'];
                                foreach($posisi_opsi as $pos): 
                            ?>
                                <label class="radio-label">
                                    <input type="radio" name="posisi_kapal" value="<?= $pos; ?>" required> <?= $pos; ?>
                                </label>
                            <?php endforeach; ?>
                            <label class="radio-label">
                                <input type="radio" name="posisi_kapal" value="Yang lain" id="radio_posisi_lain"> 
                                <span style="white-space: nowrap;">Yang lain:</span> 
                                <input type="text" name="posisi_kapal_lain" class="input-lainnya" onclick="document.getElementById('radio_posisi_lain').checked = true;" placeholder="Ketik di sini...">
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">Kirim Permohonan</button>
                </form>
                <div class="back-wrap">
                    <a href="../../index.php" class="btn-back">&larr; Kembali ke Beranda</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>