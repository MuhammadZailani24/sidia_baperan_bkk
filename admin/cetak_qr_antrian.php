<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header("Location: index.php");
    exit;
}

// Deteksi otomatis URL aplikasi Anda (Domain Cloudflare Tunnel atau IP Lokal)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

$uri = $_SERVER['REQUEST_URI'];
$base_path = substr($uri, 0, strpos($uri, '/admin/'));

// URL Tepat menuju halaman ambil antrian
$url_antrian = $protocol . "://" . $host . $base_path . "/modules/antrian/index.php";

// Gunakan API Gratis untuk generate QR Code resolusi sangat tinggi (1000x1000) dengan margin
$qr_image_url = "https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&margin=20&data=" . urlencode($url_antrian);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poster QR Code Antrean - SIDIA BAPERAN</title>
    <!-- Import Font Standard SIDIA BAPERAN -->
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --brand: #0a6b67;
            --brand-dark: #06464a;
            --brand-light: #e6f4f1;
            --gold: #f5b722;
            --text-dark: #0f2a33;
            --text-muted: #52666e;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #cbd5e1;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Konfigurasi Kertas Saat Dicetak (A4) */
        @media print {
            @page { size: A4 portrait; margin: 0; }
            body { background-color: white; padding: 0; }
            .no-print { display: none !important; }
            .poster-paper { 
                box-shadow: none !important; 
                border: none !important;
                width: 100vw !important;
                height: 100vh !important;
                border-radius: 0 !important;
                padding: 12mm !important;
            }
        }

        /* Kanvas Poster Utama */
        .poster-paper {
            background-color: white;
            width: 210mm; /* A4 Width */
            min-height: 297mm; /* A4 Height */
            padding: 15mm;
            box-sizing: border-box;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            border-radius: 2px;
            position: relative;
            overflow: hidden;
            border: 1px solid #94a3b8;
        }

        /* Ornamen Garis Border Dalam */
        .poster-frame {
            border: 6px solid var(--brand);
            border-radius: 20px;
            height: 100%;
            box-sizing: border-box;
            padding: 35px 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        /* Hiasan Sudut Frame */
        .poster-frame::before, .poster-frame::after {
            content: ''; position: absolute; width: 40px; height: 10px; background: white;
        }
        .poster-frame::before { top: -6px; left: 10%; }
        .poster-frame::after { bottom: -6px; right: 10%; }

        /* Header & Logo */
        .poster-header {
            text-align: center;
            margin-bottom: 25px;
            width: 100%;
        }
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        /* Styling Logo Anda */
        .hero-logo {
            height: 75px;
            object-fit: contain;
        }
        
        .instansi-title {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-size: 1.7rem;
            font-weight: 800;
            color: var(--brand);
            line-height: 1.2;
            margin: 0;
            text-transform: uppercase;
        }
        .instansi-subtitle {
            font-size: 1.05rem;
            color: var(--text-muted);
            font-weight: 600;
            margin-top: 5px;
            letter-spacing: 0.5px;
        }

        /* Judul Utama Scan */
        .scan-title-box {
            background: var(--brand);
            color: white;
            padding: 12px 35px;
            border-radius: 50px;
            font-family: 'Bricolage Grotesque', sans-serif;
            font-size: 1.6rem;
            font-weight: 800;
            margin: 15px 0 25px 0;
            box-shadow: 0 10px 20px rgba(10, 107, 103, 0.2);
            border-bottom: 4px solid var(--gold);
        }

        /* Area QR Code */
        .qr-wrapper {
            background: white;
            padding: 12px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 2px dashed var(--brand);
            margin-bottom: 30px;
            position: relative;
        }
        .qr-wrapper img {
            width: 300px;
            height: 300px;
            display: block;
            border-radius: 12px;
        }

        /* Panduan Langkah - Langkah */
        .steps-container {
            width: 100%;
            margin-top: auto;
        }
        .steps-title {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-size: 1.3rem;
            color: var(--text-dark);
            text-align: center;
            margin-bottom: 18px;
            position: relative;
        }
        .steps-title span {
            background: white;
            padding: 0 15px;
            position: relative;
            z-index: 2;
        }
        .steps-title::before {
            content: "";
            position: absolute;
            top: 50%; left: 0; right: 0;
            height: 2px;
            background: var(--brand-light);
            z-index: 1;
        }

        /* Grid Langkah (4 Kolom) */
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }
        .step-card {
            background: var(--brand-light);
            border-radius: 14px;
            padding: 18px 8px;
            text-align: center;
            position: relative;
            border-bottom: 4px solid var(--brand);
        }
        .step-number {
            background: var(--gold);
            color: var(--text-dark);
            width: 28px;
            height: 28px;
            line-height: 28px;
            border-radius: 50%;
            font-weight: 800;
            font-size: 0.9rem;
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            border: 3px solid white;
        }
        .step-icon {
            font-size: 2rem;
            margin: 8px 0;
        }
        .step-text {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--brand-dark);
            line-height: 1.3;
        }

        /* Tombol Cetak (Layar Saja) */
        .btn-print {
            background-color: var(--gold);
            color: var(--text-dark);
            border: none;
            padding: 15px 35px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            cursor: pointer;
            margin-bottom: 25px;
            box-shadow: 0 8px 20px rgba(245, 183, 34, 0.4);
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-print:hover { transform: scale(1.03); background-color: #e5a712; }
        
        .url-link {
            font-size: 0.8rem;
            color: #94a3b8;
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <!-- Tombol Cetak Layar -->
    <button onclick="window.print()" class="btn-print no-print">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Cetak Poster A4
    </button>

    <!-- Kertas Poster -->
    <div class="poster-paper">
        <div class="poster-frame">
            
            <!-- Header Poster -->
            <div class="poster-header">
                <div class="logo-container">
                    <!-- Logo Kustom Anda dengan penyesuaian folder ../ -->
                    <img src="../assets/img/logo_bkkside2.webp" alt="Logo Balai Kekarantinaan Kesehatan" class="hero-logo hero-logo-left">
                </div>
                <h1 class="instansi-title">SISTEM PELAYANAN DIGITAL</h1>
                <p class="instansi-subtitle">BALAI KEKARANTINAAN KESEHATAN KELAS 1 BANJARMASIN</p>
            </div>

            <!-- Area Scan QR Code -->
            <div class="scan-title-box">AMBIL TIKET ANTRIAN DI SINI</div>

            <div class="qr-wrapper">
                <img src="<?= $qr_image_url; ?>" alt="QR Code URL Antrian">
            </div>

            <!-- Grid Cara Penggunaan -->
            <div class="steps-container">
                <div class="steps-title"><span>CARA MUDAH AMBIL TIKET</span></div>
                <div class="steps-grid">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="step-icon">📱</div>
                        <div class="step-text">Buka Kamera<br>Ponsel Anda</div>
                    </div>
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="step-icon">🔍</div>
                        <div class="step-text">Arahkan Lensa<br>ke QR Code</div>
                    </div>
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="step-icon">👆</div>
                        <div class="step-text">Klik Tautan<br>Yang Muncul</div>
                    </div>
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <div class="step-icon">🎫</div>
                        <div class="step-text">Simpan Foto<br>Tiket Anda</div>
                    </div>
                </div>
            </div>

            <!-- Teks URL Cadangan (Tidak dicetak) -->
            <div class="url-link no-print"><?= htmlspecialchars($url_antrian); ?></div>

        </div>
    </div>

</body>
</html>