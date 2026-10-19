<?php
/**
 * Halaman Landing Page SIDIA BAPERAN
 * Tampilan modern, responsif dengan mode gelap/terang.
 * Warna mengikuti identitas Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIDIA BAPERAN - Sistem Digitalisasi Alat, Bahan Pemeriksaan Kekarantinaan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="assets/css/landing.css?v=<?= time(); ?>">
</head>
<body>

    <!-- Bar navigasi -->
    <nav class="topbar" aria-label="Navigasi utama">
        <div class="topbar-inner">
            <a href="admin/index.php" class="nav-link">Admin Panel</a>
            <button class="icon-btn" id="themeToggle" title="Ganti tema" aria-label="Ganti tema terang atau gelap">🌙</button>
        </div>
    </nav>

    <!-- Hero -->
    <header class="hero">
        <div class="hero-inner">
            <img src="assets/img/logo_bkkside2.webp" alt="Logo Balai Kekarantinaan Kesehatan" class="hero-logo hero-logo-left">

            <div class="hero-text">
                <p class="hero-org">
                    <svg width="18" height="20" viewBox="0 0 20 22" aria-hidden="true">
                        <rect x="2" y="1" width="2" height="20" rx="1" fill="#fff"/>
                        <path d="M4 2h13l-3.5 4.5L17 11H4z" fill="#f5b722"/>
                    </svg>
                    Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin
                </p>
                <h1>SIDIA BAPERAN</h1>
                <p class="hero-desc">Sistem Digitalisasi Alat, Bahan Pemeriksaan Kekarantinaan</p>
            </div>

            <img src="assets/img/logo_karantina.webp" alt="Logo Karantina Kesehatan" class="hero-logo hero-logo-right">
        </div>

        <svg class="hero-wave" viewBox="0 0 1440 70" preserveAspectRatio="none" aria-hidden="true">
            <path class="w-back" d="M0 30c120-24 240-24 360 0s240 24 360 0 240-24 360 0 240 24 360 0v40H0z"/>
            <path class="w-front" d="M0 46c120-20 240-20 360 0s240 20 360 0 240-20 360 0 240 20 360 0v24H0z"/>
        </svg>
    </header>

    <main class="container">

        <!-- Pita status surat -->
        <section class="track-strip" aria-label="Lacak surat">
            <div class="track-icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </div>
            <div class="track-text">
                <strong>Sudah mengajukan surat?</strong>
                <span>Cek status clearance atau izin angkut jenazah Anda.</span>
            </div>
            <a href="tracking/index.php" class="btn btn-primary">Lacak surat</a>
        </section>

        <div class="section-head">
            <h2 class="section-title">Pilih layanan</h2>
            <p class="section-sub">Pilih sesuai kebutuhan Anda, lalu isi formulirnya.</p>
        </div>

        <div class="menu-grid">

            <!-- Menu 1: Clearance In -->
            <a href="modules/clearance/form.php" class="menu-card">
                <div class="menu-icon" aria-hidden="true">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1 .6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.76"/><path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/><path d="M12 10v4"/><path d="M12 2v3"/></svg>
                </div>
                <div class="menu-body">
                    <h3>Clearance In</h3>
                    <p>Permohonan dokumen clearance kedatangan sarana pengangkut.</p>
                    <span class="badge-wilker">Khusus Wilker Induk</span>
                </div>
                <span class="go" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </span>
            </a>

            <!-- Menu 2: Surat Izin Angkut Jenazah -->
            <a href="modules/jenazah/pilih_wilker.php" class="menu-card">
                <div class="menu-icon" aria-hidden="true">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                </div>
                <div class="menu-body">
                    <h3>Izin Angkut Jenazah</h3>
                    <p>Permohonan penerbitan surat izin pengangkutan jenazah ke berbagai wilayah.</p>
                    <span class="badge-wilker">BSN, Kotabaru, Batulicin</span>
                </div>
                <span class="go" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </span>
            </a>

            <!-- Menu 3: Antrian Layanan Dokumen Kesehatan -->
            <a href="modules/antrian/index.php" class="menu-card">
                <div class="menu-icon" aria-hidden="true">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="menu-body">
                    <h3>Antrian Dokumen</h3>
                    <p>Ambil nomor antrian pelayanan dokumen kesehatan di loket.</p>
                    <span class="badge-wilker">Khusus Wilker Induk</span>
                </div>
                <span class="go" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </span>
            </a>

            <!-- Menu 4: Voting Petugas Terbaik -->
            <a href="modules/voting/index.php" class="menu-card">
                <div class="menu-icon" aria-hidden="true">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <div class="menu-body">
                    <h3>Voting Petugas</h3>
                    <p>Beri penilaian untuk petugas dengan pelayanan terbaik bulan ini.</p>
                    <span class="badge-wilker">Khusus Wilker Induk</span>
                </div>
                <span class="go" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </span>
            </a>

        </div>
    </main>

    <!-- Footer -->
    <footer>
        &copy; 2026 SIDIA BAPERAN - Balai Kekarantinaan Kesehatan Kelas 1 Banjarmasin <br>
        Dibuat oleh Timker 2 <br>
        Code by: Muhammad Zailani
    </footer>

    <script>
        // Mode gelap / terang
        const themeToggleBtn = document.getElementById('themeToggle');
        const body = document.body;

        if (localStorage.getItem('sidia_theme') === 'dark') {
            body.classList.add('dark-mode');
            themeToggleBtn.innerHTML = '☀️';
        }

        themeToggleBtn.addEventListener('click', () => {
            const dark = body.classList.toggle('dark-mode');
            localStorage.setItem('sidia_theme', dark ? 'dark' : 'light');
            themeToggleBtn.innerHTML = dark ? '☀️' : '🌙';
        });
    </script>

</body>
</html>