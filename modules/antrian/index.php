<?php
require_once '../../config/database.php';
date_default_timezone_set('Asia/Makassar'); // Sesuaikan timezone jika diperlukan

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
    <!-- Library untuk mengubah elemen HTML menjadi Gambar/Canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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
            <!-- AREA TIKET YANG AKAN DI-SCREENSHOT (DISIMPAN KE FOTO) -->
            <div id="area-cetak-tiket" class="alert-success" style="background-color: #ffffff; padding: 30px 20px; border-radius: 12px; border: 2px dashed #0a6b67; margin-bottom: 20px; text-align: center;">
                <h4 style="margin: 0; color: #0a6b67; font-size: 1.1rem; text-transform: uppercase;">Kekarantinaan Kesehatan<br>Kelas 1 Banjarmasin</h4>
                <hr style="border: 0; border-top: 1px solid #d3e1e2; margin: 15px 0;">
                
                <p class="tiket-label" style="color: #52666e; margin-bottom: 5px; font-weight: 600;">Nomor Antrian Anda:</p>
                <h1 style="font-size: 3.8rem; color: #0f766e; margin: 10px 0; line-height: 1;"><?= htmlspecialchars($nomor_baru); ?></h1>
                
                <p class="tiket-hint" style="font-size: 0.85rem; color: #64748b; margin-top: 15px;">Harap tunggu panggilan dari petugas.</p>
                <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 5px;">Diambil pada: <?= date('d/m/Y H:i'); ?></p>
            </div>
            
            <!-- TOMBOL AKSI (SIMPAN FOTO & SELESAI) -->
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button onclick="simpanTiket()" class="btn-ambil" style="flex: 1; background-color: #0ea5e9; border: none; cursor: pointer; color: white;">📸 Simpan Foto</button>
                <a href="index.php" class="btn-ambil btn-link" style="flex: 1; text-align: center; box-sizing: border-box;">✅ Selesai</a>
            </div>
            
            <script>
                // Fungsi untuk merender area tiket menjadi gambar dan mengunduhnya
                function simpanTiket() {
                    const area = document.getElementById('area-cetak-tiket');
                    html2canvas(area, {
                        scale: 3, // Skala 3x lipat agar gambar beresolusi tinggi (tidak pecah)
                        backgroundColor: "#ffffff" // Paksa background putih (penting jika pengunjung pakai Dark Mode)
                    }).then(canvas => {
                        const link = document.createElement('a');
                        link.download = 'Tiket_Antrian_<?= htmlspecialchars($nomor_baru); ?>.png';
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                    });
                }
            </script>
        <?php else: ?>

            <!-- CARD 1: DISPLAY INFO ANTRIAN -->
            <div class="card card-display">
                <h3>Sedang Dilayani Saat Ini: <span style="font-size: 0.90rem; color: #10b981; font-weight: normal; vertical-align: middle; margin-left: 5px;">(🟢 Live)</span></h3>
                <div class="nomor" id="nomor-aktif"><?= htmlspecialchars($current_served); ?></div>
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

    <!-- SCRIPT AJAX & TEXT-TO-SPEECH (INDONESIA + INGGRIS) -->
    <script>
        // =====================================================
        // PENGATURAN SUARA (silakan ubah sesuai kebutuhan)
        // =====================================================
        const KONFIG = {
            rateID: 0.85,        // Kecepatan bahasa Indonesia (1.0 = normal, makin kecil makin lambat)
            rateEN: 0.88,        // Kecepatan bahasa Inggris
            pitch: 1,
            volume: 1,
            jedaBahasa: 700,     // Jeda sebelum pindah dari Indonesia ke Inggris (ms)
            ulangiPanggilan: 1,  // Berapa kali panggilan diulang penuh (1 = sekali)
            bunyiTing: true      // Bunyi "ting-tong" sebelum pengumuman (khas bandara)
        };

        let suaraAktif = false;
        let lastAnnounced = '<?= htmlspecialchars($current_served, ENT_QUOTES); ?>';
        let idPanggilan = 0;       // Penanda agar panggilan lama berhenti jika ada panggilan baru
        let daftarSuara = [];

        // Muat daftar suara browser (di Chrome dimuat secara asinkron)
        function muatDaftarSuara() {
            if ('speechSynthesis' in window) {
                daftarSuara = window.speechSynthesis.getVoices();
            }
        }
        muatDaftarSuara();
        if ('speechSynthesis' in window) {
            window.speechSynthesis.onvoiceschanged = muatDaftarSuara;
        }

        // Pilih suara terbaik sesuai bahasa
        function pilihSuara(lang) {
            if (!daftarSuara.length) muatDaftarSuara();
            const prefix = lang.split('-')[0].toLowerCase();
            const cocok = daftarSuara.filter(v => v.lang.toLowerCase().replace('_', '-').startsWith(prefix));
            if (!cocok.length) return null;
            // Utamakan kode bahasa persis sama (id-ID / en-US), lalu suara Google/online
            return cocok.find(v => v.lang.replace('_', '-').toLowerCase() === lang.toLowerCase())
                || cocok.find(v => /google/i.test(v.name))
                || cocok[0];
        }

        // Trik 1: Paksa panggil suara kosong saat halaman pertama diload.
        setTimeout(() => {
            if (!suaraAktif && 'speechSynthesis' in window) {
                let silentMsg = new SpeechSynthesisUtterance('');
                silentMsg.volume = 0;
                window.speechSynthesis.speak(silentMsg);
                suaraAktif = true;
            }
        }, 1000);

        // Trik 2: Sadap klik/sentuhan apa saja di layar untuk melepas blokir browser
        function bukaBlokirSuara() {
            if (!suaraAktif && 'speechSynthesis' in window) {
                let silentMsg = new SpeechSynthesisUtterance('');
                silentMsg.volume = 0;
                window.speechSynthesis.speak(silentMsg);
                suaraAktif = true;
            }
        }
        document.body.addEventListener('click', bukaBlokirSuara, { once: true });
        document.body.addEventListener('touchstart', bukaBlokirSuara, { once: true });

        // =====================================================
        // BUNYI "TING-TONG" PEMBUKA
        // =====================================================
        function bunyiTing() {
            try {
                const AC = window.AudioContext || window.webkitAudioContext;
                if (!AC) return;
                const ctx = new AC();
                const nada = [[880, 0, 0.5], [660, 0.45, 0.8]]; // [frekuensi, mulai(s), durasi(s)]
                nada.forEach(([freq, mulai, durasi]) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.value = freq;
                    gain.gain.setValueAtTime(0.0001, ctx.currentTime + mulai);
                    gain.gain.exponentialRampToValueAtTime(0.35, ctx.currentTime + mulai + 0.05);
                    gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + mulai + durasi);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(ctx.currentTime + mulai);
                    osc.stop(ctx.currentTime + mulai + durasi + 0.05);
                });
                setTimeout(() => { try { ctx.close(); } catch (e) {} }, 1600);
            } catch (e) {}
        }

        // =====================================================
        // MENYUSUN KALIMAT PANGGILAN
        // =====================================================
        const ANGKA_ID = ['kosong', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan'];
        const ANGKA_EN = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];

        // Hasil: array segmen {teks, lang, rate, jeda}
        // "jeda" = lama diam (ms) setelah segmen tersebut selesai diucapkan
        function susunPanggilan(nomor) {
            const bagian = nomor.split('-');
            if (bagian.length !== 2) return [];

            const huruf = bagian[0];
            const digit = bagian[1].split('').map(d => parseInt(d, 10)).filter(d => !isNaN(d));
            const bacaID = digit.map(d => ANGKA_ID[d]).join(' ');
            const bacaEN = digit.map(d => ANGKA_EN[d]).join(' ');

            // Satu kalimat utuh per bahasa. Tanda koma memberi jeda singkat yang alami
            // antara nomor dan kalimat "silakan menuju", seperti panggilan loket pada umumnya.
            return [
                { teks: 'Nomor antrian ' + huruf + ' ' + bacaID + ', silakan menuju loket pelayanan Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin',
                  lang: 'id-ID', rate: KONFIG.rateID, jeda: KONFIG.jedaBahasa },
                { teks: 'Queue number ' + huruf + ' ' + bacaEN + ', please proceed to the service counter, Banjarmasin Class I Health Quarantine Office',
                  lang: 'en-US', rate: KONFIG.rateEN, jeda: 0 }
            ];
        }

        // =====================================================
        // PEMUTAR SUARA BERURUTAN (kalimat demi kalimat)
        // =====================================================
        function putarSegmen(daftar, index, id) {
            // Hentikan jika sudah ada panggilan yang lebih baru
            if (id !== idPanggilan) return;
            if (index >= daftar.length) return;

            const s = daftar[index];
            const msg = new SpeechSynthesisUtterance(s.teks);
            msg.lang = s.lang;
            msg.rate = s.rate;
            msg.pitch = KONFIG.pitch;
            msg.volume = KONFIG.volume;

            const voice = pilihSuara(s.lang);
            if (voice) msg.voice = voice;

            let sudahLanjut = false;
            const lanjut = () => {
                if (sudahLanjut) return;
                sudahLanjut = true;
                setTimeout(() => putarSegmen(daftar, index + 1, id), s.jeda);
            };

            msg.onend = lanjut;
            msg.onerror = lanjut;

            // Pengaman: jika onend tidak terpanggil (bug di beberapa browser)
            const batasWaktu = 2500 + (s.teks.length * 250);
            setTimeout(lanjut, batasWaktu);

            window.speechSynthesis.speak(msg);
        }

        function panggilNomor(nomor) {
            if (!('speechSynthesis' in window)) return;

            const satuKali = susunPanggilan(nomor);
            if (!satuKali.length) return;

            // Ulangi sesuai pengaturan
            let semua = [];
            for (let i = 0; i < KONFIG.ulangiPanggilan; i++) {
                if (i > 0 && semua.length) semua[semua.length - 1].jeda = KONFIG.jedaBahasa;
                semua = semua.concat(satuKali.map(x => Object.assign({}, x)));
            }

            idPanggilan++;
            const id = idPanggilan;

            window.speechSynthesis.cancel();
            if (KONFIG.bunyiTing) bunyiTing();

            // Beri waktu bunyi "ting-tong" selesai sebelum suara mulai
            setTimeout(() => putarSegmen(semua, 0, id), KONFIG.bunyiTing ? 1400 : 200);
        }

        // =====================================================
        // AUTO-REFRESH (POLLING)
        // =====================================================
        function muatDataAntrian() {
            var el = document.getElementById('nomor-aktif');
            if (!el) return;

            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'index.php?ajax=1', true);
            xhr.onload = function () {
                if (this.status == 200) {
                    let nomorBaru = this.responseText.trim();
                    el.textContent = nomorBaru;

                    // Deteksi perubahan nomor
                    if (nomorBaru !== lastAnnounced) {
                        // Hanya umumkan jika ada nomor baru yang dipanggil
                        if (nomorBaru !== '---') {
                            panggilNomor(nomorBaru);
                        }
                        lastAnnounced = nomorBaru;
                    }
                }
            };
            xhr.send();
        }

        // Polling setiap 2 detik
        setInterval(muatDataAntrian, 2000);
    </script>
</body>
</html>