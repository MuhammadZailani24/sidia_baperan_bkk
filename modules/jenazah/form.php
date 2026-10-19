<?php
require_once '../../config/database.php';

$wilker_id = isset($_GET['wilker_id']) ? (int)$_GET['wilker_id'] : 0;

$stmt_w = $pdo->prepare("SELECT * FROM wilayah_kerja WHERE id = ? AND id > 1");
$stmt_w->execute([$wilker_id]);
$wilker = $stmt_w->fetch();

if (!$wilker) {
    header("Location: pilih_wilker.php");
    exit;
}

$success_code = '';
$success_id = '';

$stmt_last_id = $pdo->query("SELECT id_permohonan FROM permohonan_jenazah ORDER BY id DESC LIMIT 1");
$last_id_row = $stmt_last_id->fetch();

if ($last_id_row && !empty($last_id_row['id_permohonan'])) {
    $last_num = (int) substr($last_id_row['id_permohonan'], 5);
    $next_num = $last_num + 1;
    $id_permohonan_display = 'SIAJ-' . str_pad($next_num, 3, '0', STR_PAD_LEFT);
} else {
    $id_permohonan_display = 'SIAJ-001';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_permohonan      = trim($_POST['id_permohonan']);
    $email_pemohon      = trim($_POST['email_pemohon']);
    $nama_pemohon_cargo = trim($_POST['nama_pemohon_cargo']);
    $no_hp_pemohon      = trim($_POST['no_hp_pemohon']);
    $perusahaan_cargo   = trim($_POST['perusahaan_cargo']);

    do {
        $kode_unik = 'JNZ-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        $stmt_check = $pdo->prepare("SELECT id FROM permohonan_jenazah WHERE kode_unik = ?");
        $stmt_check->execute([$kode_unik]);
    } while ($stmt_check->rowCount() > 0);

    $stmt = $pdo->prepare("INSERT INTO permohonan_jenazah (id_permohonan, kode_unik, wilayah_kerja_id, email_pemohon, nama_pemohon_cargo, no_hp_pemohon, perusahaan_cargo, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->execute([$id_permohonan, $kode_unik, $wilker_id, $email_pemohon, $nama_pemohon_cargo, $no_hp_pemohon, $perusahaan_cargo]);

    // === KIRIM NOTIFIKASI TELEGRAM DENGAN TOMBOL AKSI CEPAT ===
    $pesan_telegram = "⚰️ <b>PERMOHONAN IZIN ANGKUT JENAZAH</b>\n\n" .
                      "🆔 ID Permohonan: <b>{$id_permohonan}</b>\n" .
                      "🔑 Kode Unik: <code>{$kode_unik}</code>\n" .
                      "📧 Email: {$email_pemohon}\n" .
                      "📍 Wilayah Kerja: <b>{$wilker['nama_wilker']}</b>\n" .
                      "👤 Pemohon (Cargo): {$nama_pemohon_cargo}\n" .
                      "📞 No HP Pemohon: {$no_hp_pemohon}\n" .
                      "🏢 Perusahaan Cargo: {$perusahaan_cargo}\n\n" .
                      "<i>Silakan klik tombol di bawah untuk memproses permohonan ini:</i>";
    
    $keyboard_jenazah = [
        'inline_keyboard' => [
            [
                ['text' => '⏳ Ubah ke Sedang Ditinjau', 'callback_data' => "upd_jnz_Tinjau_{$id_permohonan}"]
            ]
        ]
    ];

    if (function_exists('kirimNotifikasiTelegram')) {
        kirimNotifikasiTelegram($pesan_telegram, $keyboard_jenazah);
    }

    $subjek_email = "Permohonan Izin Angkut Jenazah Baru - " . $id_permohonan;
    $pesan_email = "<h3>Permohonan Izin Angkut Jenazah Masuk</h3>
                    <p>Halo Admin,</p>
                    <p>Ada permohonan Izin Angkut Jenazah baru di Wilker <strong>{$wilker['nama_wilker']}</strong>:</p>
                    <ul>
                        <li><strong>ID Permohonan:</strong> {$id_permohonan}</li>
                        <li><strong>Kode Unik:</strong> {$kode_unik}</li>
                        <li><strong>Email Pemohon:</strong> {$email_pemohon}</li>
                        <li><strong>Nama Pemohon:</strong> {$nama_pemohon_cargo}</li>
                        <li><strong>No HP:</strong> {$no_hp_pemohon}</li>
                        <li><strong>Perusahaan Cargo:</strong> {$perusahaan_cargo}</li>
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
    <title>Permohonan Izin Angkut Jenazah - SIDIA BAPERAN</title>
    <link rel="stylesheet" href="../../assets/css/jenazah.css?v=<?= time(); ?>">
</head>
<body>
    <script>
        // Ikuti tema (terang/gelap) yang dipilih di halaman beranda
        try { if (localStorage.getItem('sidia_theme') === 'dark') document.body.classList.add('dark-mode'); } catch (e) {}
    </script>

    <div class="container">
        <div class="card">
            <h2>Permohonan Izin Angkut Jenazah</h2>
            <p class="wilker-note">Wilayah Kerja: <strong><?= htmlspecialchars($wilker['nama_wilker']); ?></strong></p>

            <?php if ($success_code): ?>
                <div class="alert-success">
                    <h3>Permohonan Berhasil Dikirim!</h3>
                    <p>ID Permohonan Anda: <strong><?= $success_id; ?></strong></p>
                    <p>Simpan kode unik berikut untuk melacak progress izin angkut jenazah Anda:</p>
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
                        <input type="text" inputmode="email" name="email_pemohon" required placeholder="contoh: nama@gmail.com" pattern=".*@gmail\.com$" title="Harap masukkan alamat email berakhiran @gmail.com">
                    </div>

                    <div class="form-group">
                        <label>Nama Pemohon (Cargo) <span class="required">*</span></label>
                        <input type="text" name="nama_pemohon_cargo" required placeholder="Jawaban Anda">
                    </div>
                    <div class="form-group">
                        <label>No Handphone Pemohon (Cargo) <span class="required">*</span></label>
                        <input type="text" name="no_hp_pemohon" required placeholder="Jawaban Anda">
                    </div>
                    <div class="form-group">
                        <label>Perusahaan Cargo <span class="required">*</span></label>
                        <input type="text" name="perusahaan_cargo" required placeholder="Jawaban Anda">
                    </div>
                    <button type="submit" class="btn-submit">Kirim Permohonan</button>
                </form>
                <div class="back-wrap">
                    <a href="pilih_wilker.php" class="btn-back">&larr; Ganti Wilayah Kerja</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>