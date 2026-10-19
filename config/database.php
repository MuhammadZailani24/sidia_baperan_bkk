<?php
// ==========================================
// KODE KONFIGURASI DATABASE & NOTIFIKASI
// SIDIA BAPERAN
// ==========================================

$host = 'localhost';
$dbname = 'sidia_baperan';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

/**
 * Fungsi untuk mengirim notifikasi instan ke Telegram Admin (Mendukung Tombol Interaktif)
 */
function kirimNotifikasiTelegram($pesan, $reply_markup = null) {
    $token = '8952711378:AAGvYOK5dudZwEsOHn4dxeicxJOxI0kSvBU';   
    $chat_id = '7279608699'; 

    if (empty($chat_id)) {
        return false; 
    }

    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $pesan,
        'parse_mode' => 'HTML'
    ];

    if ($reply_markup) {
        $data['reply_markup'] = json_encode($reply_markup);
    }

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'timeout' => 5
        ]
    ];
    
    $context  = stream_context_create($options);
    @file_get_contents($url, false, $context);
}

/**
 * Fungsi untuk mengirim notifikasi ke Gmail Admin
 */
function kirimNotifikasiEmail($subjek, $pesan_html) {
    $admin_email = 'email_admin@gmail.com'; // <-- Ganti dengan email Anda jika diperlukan

    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: SIDIA BAPERAN <noreply@sidiabaperan.com>" . "\r\n";

    @mail($admin_email, $subjek, $pesan_html, $headers);
}