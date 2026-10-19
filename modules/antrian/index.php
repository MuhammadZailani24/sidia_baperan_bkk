<?php
require_once '../../config/database.php';

// --- BLOK KHUSUS AJAX (AUTO REFRESH NOMOR PANGGILAN) ---
if (isset($_GET['ajax'])) {
    $stmt_current = $pdo->query("SELECT nomor_antrian FROM antrian WHERE status = 'Sedang dilayani' ORDER BY id DESC LIMIT 1");
    $current_served = $stmt_current->fetchColumn();
    // Jika tidak ada panggilan, kirim '---'. Jika ada, kirim nomor urutnya
    echo $current_served ? $current_served : '---';
    exit; 
}
// --- AKHIR BLOK AJAX ---

$nomor_baru = '';

// Proses jika tombol ambil antrian diklik
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ambil_antrian'])) {
    $stmt_last = $pdo->query("SELECT nomor_antrian FROM antrian ORDER BY id DESC LIMIT 1");
    $last_queue = $stmt_last->fetchColumn();

    if ($last_queue) {
        $last_num = (int) substr($last_queue, 2);
        $next_num = $last_num + 1;
    } else {
        $next_num = 1;
    }

    $nomor_baru = 'A-' . str_pad($next_num, 3, '0', STR_PAD_LEFT);

    $stmt_insert = $pdo->prepare("INSERT INTO antrian (nomor_antrian, status) VALUES (?, 'Sedang dalam antrian')");
    $stmt_insert->execute([$nomor_baru]);
}

// Menampilkan antrian yang "Sedang Dilayani" saat ini
$stmt_current = $pdo->query("SELECT nomor_antrian FROM antrian WHERE status = 'Sedang dilayani' ORDER BY id DESC LIMIT 1");
$current_served = $stmt_current->fetchColumn();
$current_served = $current_served ? $current_served : '---';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambil Antrian - SIDIA BAPERAN</title>
    <link rel="stylesheet" href="../../assets/css/antrian.css?v=<?= time(); ?>">
</head>
<body>
    <script>
        try { if (localStorage.getItem('sidia_theme') === 'dark') document.body.classList.add('dark-mode'); } catch (e) {}
    </script>

    <div class="wrapper">
        
        <div class="header-title">
            <h2>Loket Pelayanan</h2>
            <p>Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin</p>
        </div>

        <?php if ($nomor_baru): ?>
            <!-- Tampilan jika berhasil ambil antrian -->
            <div class="alert-success">
                <p class="tiket-label">Nomor Antrian Anda:</p>
                <h1><?= $nomor_baru; ?></h1>
                <p class="tiket-hint">Harap tunggu panggilan dari petugas atau perhatikan layar antrian.</p>
            </div>
            <a href="index.php" class="btn-ambil btn-link">Selesai & Kembali</a>
        <?php else: ?>
            
            <!-- CARD 1: DISPLAY INFO ANTRIAN -->
            <div class="card card-display">
                <h3>Sedang Dilayani Saat Ini: <span style="font-size: 0.75rem; color: #10b981; font-weight: normal; vertical-align: middle; margin-left: 5px;">(🟢 Live)</span></h3>
                <div class="nomor" id="nomor-aktif"><?= $current_served; ?></div>
            </div>

            <!-- CARD 2: AKSI AMBIL ANTRIAN -->
            <div class="card card-action">
                <p>Silakan ambil tiket antrian Anda di bawah ini:</p>
                <form action="" method="POST">
                    <button type="submit" name="ambil_antrian" class="btn-ambil" id="btn-tiket">🎫 Ambil Nomor Antrian</button>
                </form>
            </div>
            
        <?php endif; ?>

        <br>
        <a href="../../index.php" class="btn-back">&larr; Kembali ke Halaman Utama</a>
    </div>

    <!-- SCRIPT AJAX & GOOGLE VOICE (TTS) OTOMATIS -->
    <script>
        let suaraAktif = false;
        let lastAnnounced = '<?= $current_served; ?>';

        // Trik 1: Paksa panggil suara kosong saat halaman pertama diload. 
        // Jika browser percaya pada situs Anda (sering dikunjungi), suara akan tembus otomatis.
        setTimeout(() => {
            if (!suaraAktif) {
                let silentMsg = new SpeechSynthesisUtterance('');
                silentMsg.volume = 0;
                window.speechSynthesis.speak(silentMsg);
                suaraAktif = true;
            }
        }, 1000);

        // Trik 2: Sadap klik/sentuhan apa saja di layar (termasuk tombol Ambil Antrian) untuk melepas blokir browser
        function bukaBlokirSuara() {
            if (!suaraAktif) {
                let silentMsg = new SpeechSynthesisUtterance('');
                silentMsg.volume = 0;
                window.speechSynthesis.speak(silentMsg);
                suaraAktif = true;
            }
        }
        document.body.addEventListener('click', bukaBlokirSuara, { once: true });
        document.body.addEventListener('touchstart', bukaBlokirSuara, { once: true });

        // Format angka (A-001 -> A, kosong, kosong, satu) agar lafalnya natural
        function formatUntukSuara(teks) {
            if (!teks || teks === '---') return "";
            let bagian = teks.split('-');
            if (bagian.length === 2) {
                let huruf = bagian[0];
                let angka = bagian[1].split('').join(', '); // 001 -> 0, 0, 1
                angka = angka.replace(/0/g, 'kosong');
                return huruf + ", " + angka;
            }
            return teks;
        }

        // Fungsi pemutar text-to-speech
        function panggilSuara(teks) {
            if ('speechSynthesis' in window) {
                let msg = new SpeechSynthesisUtterance();
                msg.text = teks;
                msg.lang = 'id-ID'; // Logat Indonesia
                msg.rate = 0.85;    // Diperlambat sedikit agar jelas
                msg.pitch = 1;
                window.speechSynthesis.speak(msg);
            }
        }

        // Fungsi Auto-Refresh
        function muatDataAntrian() {
            var el = document.getElementById('nomor-aktif');
            if (!el) return; // Abaikan jika pengunjung sedang di halaman sukses

            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'index.php?ajax=1', true);
            xhr.onload = function() {
                if (this.status == 200) {
                    let nomorBaru = this.responseText.trim();
                    el.innerHTML = nomorBaru;

                    // Logika Deteksi Perubahan Status
                    if (nomorBaru !== lastAnnounced) {
                        
                        // 1. Suara Penyelesaian: Jika nomor sebelumnya ada, beri tahu bahwa sudah selesai dilayani
                        if (lastAnnounced !== '---') {
                            panggilSuara("Nomor antrean, " + formatUntukSuara(lastAnnounced) + ", sudah selesai dilayani.");
                        }

                        // 2. Suara Pemanggilan Baru: Jika ada nomor baru, otomatis ngantre diucapkan setelah suara pertama
                        if (nomorBaru !== '---') {
                            panggilSuara("Nomor antrean, " + formatUntukSuara(nomorBaru) + ", silakan menuju loket pelayanan.");
                        }

                        lastAnnounced = nomorBaru;
                    }
                }
            }
            xhr.send();
        }

        // Polling dengan kecepatan sangat tinggi setiap 2 detik
        setInterval(muatDataAntrian, 2000);
    </script>
</body>
</html>