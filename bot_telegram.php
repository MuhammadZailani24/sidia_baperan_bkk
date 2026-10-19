<?php
// =================================================================
// SCRIPT BOT TELEGRAM INTERAKTIF - SIDIA BAPERAN (FINAL VERSION)
// =================================================================

require_once 'config/database.php';

$bot_token = '8952711378:AAGvYOK5dudZwEsOHn4dxeicxJOxI0kSvBU';$admin_chat_id = '7279608699'; 

echo "Memulai Bot SIDIA BAPERAN...\nMenunggu perintah dari Telegram...\n\n";

$last_update_id = 0;

function kirimEmailDenganPDF($to, $subject,$htmlMessage, $filePath,$fileName) {
    $boundary = md5(time());$headers = "From: SIDIA BAPERAN <admin@sidiabaperan.com>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($htmlMessage));

    if (file_exists($filePath)) {
        $file_content = chunk_split(base64_encode(file_get_contents($filePath)));
        $body .= "--$boundary\r\n";
        $body .= "Content-Type: application/pdf; name=\"$fileName\"\r\n";
        $body .= "Content-Disposition: attachment; filename=\"$fileName\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .=$file_content;
    }
    $body .= "--$boundary--";

    return @mail($to,$subject, $body,$headers);
}

function teleRequest($method,$data) {
    global $bot_token;
    $url = "https://api.telegram.org/bot" . $bot_token . "/" . $method;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}

while (true) {
    $url_updates = "https://api.telegram.org/bot" . $bot_token . "/getUpdates?offset=" . ($last_update_id + 1) . "&timeout=5";
    
    $ch2 = curl_init();
    curl_setopt($ch2, CURLOPT_URL,$url_updates);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch2);
    curl_close($ch2);
    
    if ($response) {
        $updates = json_decode($response, true);
        
        if (isset($updates['result']) && is_array($updates['result'])) {
            foreach ($updates['result'] as$update) {
                $last_update_id =$update['update_id'];

                if (isset($update['message']['text'])) {
                    $chat_id =$update['message']['chat']['id'];
                    $text = strtolower(trim($update['message']['text']));

                    if ($chat_id !=$admin_chat_id) { continue; } 

                    if ($text === 'cek clearance' or$text === 'cek permohonan clearance in') {
                        $stmt =$pdo->query("SELECT id_permohonan, nama_kapal, status FROM permohonan_clearance WHERE status != 'Selesai / Diterbitkan' ORDER BY id DESC LIMIT 10");
                        $data_clearance =$stmt->fetchAll();

                        if (!$data_clearance) {
                            $pesan = array('chat_id' =>$chat_id, 'text' => "[INFO] Tidak ada permohonan Clearance In yang tertunda.");
                            teleRequest('sendMessage', $pesan);
                            continue;
                        }

                        $keyboard = array();
                        foreach ($data_clearance as$row) {
                            $btn_text = "[ID: " . $row['id_permohonan'] . "] - " . $row['nama_kapal'] . " (" . $row['status'] . ")";
                            $btn_cb = "dtl_clr_" . $row['id_permohonan'];$btn = array();
                            $btn['text'] =$btn_text;
                            $btn['callback_data'] =$btn_cb;
                            
                            $row_keyboard = array($btn);
                            $keyboard[] =$row_keyboard;
                        }
                        
                        $reply_markup = array('inline_keyboard' => $keyboard);$msg_data = array(
                            'chat_id' => $chat_id,
                            'text' => "*Daftar Permohonan Clearance In (Belum Selesai)*\nPilih ID untuk melihat detail:",
                            'parse_mode' => 'Markdown',
                            'reply_markup' => $reply_markup
                        );
                        teleRequest('sendMessage', $msg_data);
                    }

                    if ($text === 'cek jenazah' or$text === 'cek permohonan surat izin angkut jenazah') {
                        $stmt =$pdo->query("SELECT id_permohonan, nama_pemohon_cargo, status FROM permohonan_jenazah WHERE status != 'Selesai / Diterbitkan' ORDER BY id DESC LIMIT 10");
                        $data_jenazah =$stmt->fetchAll();

                        if (!$data_jenazah) {
                            $pesan = array('chat_id' =>$chat_id, 'text' => "[INFO] Tidak ada permohonan Angkut Jenazah yang tertunda.");
                            teleRequest('sendMessage', $pesan);
                            continue;
                        }

                        $keyboard = array();
                        foreach ($data_jenazah as$row) {
                            $btn_text = "[ID: " . $row['id_permohonan'] . "] - " . $row['nama_pemohon_cargo'] . " (" . $row['status'] . ")";
                            $btn_cb = "dtl_jnz_" . $row['id_permohonan'];$btn = array();
                            $btn['text'] =$btn_text;
                            $btn['callback_data'] =$btn_cb;
                            
                            $row_keyboard = array($btn);
                            $keyboard[] =$row_keyboard;
                        }
                        
                        $reply_markup = array('inline_keyboard' => $keyboard);$msg_data = array(
                            'chat_id' => $chat_id,
                            'text' => "*Daftar Permohonan Izin Angkut Jenazah (Belum Selesai)*\nPilih ID untuk melihat detail:",
                            'parse_mode' => 'Markdown',
                            'reply_markup' => $reply_markup
                        );
                        teleRequest('sendMessage', $msg_data);
                    }
                }

                if (isset($update['message']['document']) && isset($update['message']['caption'])) {
                    $chat_id =$update['message']['chat']['id'];
                    $caption = strtoupper(trim($update['message']['caption']));
                    
                    if (preg_match('/^(CIN-\d+|SIAJ-\d+)$/',$caption)) {
                        $file_id =$update['message']['document']['file_id'];
                        
                        $fileInfoJson = file_get_contents("https://api.telegram.org/bot" . $bot_token . "/getFile?file_id=" . $file_id);$fileInfo = json_decode($fileInfoJson, true);$file_path = $fileInfo['result']['file_path'];$file_url = "https://api.telegram.org/file/bot" . $bot_token . "/" . $file_path;
                        $file_content = file_get_contents($file_url);
                        $new_filename = time() . "_" . $caption . ".pdf";
                        $save_path = "assets/uploads/" . $new_filename;
                        
                        file_put_contents($save_path,$file_content);

                        if (strpos($caption, 'CIN-') !== false) {
                            $stmt =$pdo->prepare("UPDATE permohonan_clearance SET file_pdf = ?, status = 'Selesai / Diterbitkan' WHERE id_permohonan = ?");
                            $stmt->execute(array($new_filename,$caption));
                            
                            $stmt_email =$pdo->prepare("SELECT email_pemohon FROM permohonan_clearance WHERE id_permohonan = ?");
                        } else {
                            $stmt =$pdo->prepare("UPDATE permohonan_jenazah SET file_pdf = ?, status = 'Selesai / Diterbitkan' WHERE id_permohonan = ?");
                            $stmt->execute(array($new_filename,$caption));
                            
                            $stmt_email =$pdo->prepare("SELECT email_pemohon FROM permohonan_jenazah WHERE id_permohonan = ?");
                        }

                        $stmt_email->execute(array($caption));
                        $pemohon =$stmt_email->fetch();

                        $pesan_tambahan = "";
                        if ($pemohon && !empty($pemohon['email_pemohon'])) {$msg_html = "<h3>Dokumen Anda Telah Diterbitkan</h3>
                                         <p>Halo, Surat persetujuan untuk permohonan <b>" . $caption . "</b> telah selesai diproses.</p>
                                         <p>Silakan unduh dokumen resmi (PDF) pada lampiran email ini.</p>";
                            kirimEmailDenganPDF($pemohon['email_pemohon'], "Surat Diterbitkan - " . $caption, $msg_html,$save_path, $new_filename);$pesan_tambahan = "📧 Surat PDF resmi telah dikirim ke Email pemohon: *" . $pemohon['email_pemohon'] . "*";
                        } else {
                            $pesan_tambahan = "⚠️ Email pemohon kosong.";
                        }

                        $msg_data = array(
                            'chat_id' => $chat_id,
                            'text' => "[SUKSES]\nFile PDF berhasil disimpan.\nStatus `" . $caption . "` otomatis menjadi Selesai.\n\n" . $pesan_tambahan,
                            'parse_mode' => 'Markdown'
                        );
                        teleRequest('sendMessage', $msg_data);
                    }
                }

                if (isset($update['callback_query'])) {
                    $cb_id =$update['callback_query']['id'];
                    $chat_id =$update['callback_query']['message']['chat']['id'];
                    $data_cb =$update['callback_query']['data'];
                    $msg_id =$update['callback_query']['message']['message_id'];

                    if (strpos($data_cb, 'dtl_clr_') === 0) {
                        $id_perm = str_replace('dtl_clr_', '',$data_cb);
                        $stmt =$pdo->prepare("SELECT * FROM permohonan_clearance WHERE id_permohonan = ?");
                        $stmt->execute(array($id_perm));
                        $row =$stmt->fetch();

                        $teks = "*DETAIL CLEARANCE IN*\n\n" .
                                "ID Permohonan: " . $row['id_permohonan'] . "\n" .
                                "Kode Unik: `" . $row['kode_unik'] . "`\n" .
                                "Email: " . $row['email_pemohon'] . "\n" .
                                "Agen: " . $row['keagenan_pelayaran'] . "\n" .
                                "Kapal: " . $row['nama_kapal'] . "\n" .
                                "Tiba: " . $row['tanggal_kedatangan'] . " (" . $row['jam_kedatangan'] . ")\n" .
                                "Status: " . $row['status'] . "\n\n" .
                                "Pilih tindakan untuk permohonan ini:";

                        $btn = array();$btn['text'] = '[Ubah ke Sedang Ditinjau]';
                        $btn['callback_data'] = "upd_clr_Tinjau_" . $id_perm;
                        
                        $keyboard = array(array($btn));
                        $reply_markup = array('inline_keyboard' =>$keyboard);

                        $msg_data = array(
                            'chat_id' => $chat_id, 'message_id' =>$msg_id,
                            'text' => $teks, 'parse_mode' => 'Markdown', 'reply_markup' =>$reply_markup
                        );
                        teleRequest('editMessageText', $msg_data);
                    }

                    if (strpos($data_cb, 'dtl_jnz_') === 0) {
                        $id_perm = str_replace('dtl_jnz_', '',$data_cb);
                        $stmt =$pdo->prepare("SELECT * FROM permohonan_jenazah WHERE id_permohonan = ?");
                        $stmt->execute(array($id_perm));
                        $row =$stmt->fetch();

                        $teks = "*DETAIL ANGKUT JENAZAH*\n\n" .
                                "ID Permohonan: " . $row['id_permohonan'] . "\n" .
                                "Kode Unik: `" . $row['kode_unik'] . "`\n" .
                                "Email: " . $row['email_pemohon'] . "\n" .
                                "Pemohon: " . $row['nama_pemohon_cargo'] . "\n" .
                                "Perusahaan: " . $row['perusahaan_cargo'] . "\n" .
                                "Status: " . $row['status'] . "\n\n" .
                                "Pilih tindakan untuk permohonan ini:";

                        $btn = array();$btn['text'] = '[Ubah ke Sedang Ditinjau]';
                        $btn['callback_data'] = "upd_jnz_Tinjau_" . $id_perm;
                        
                        $keyboard = array(array($btn));
                        $reply_markup = array('inline_keyboard' =>$keyboard);

                        $msg_data = array(
                            'chat_id' => $chat_id, 'message_id' =>$msg_id,
                            'text' => $teks, 'parse_mode' => 'Markdown', 'reply_markup' =>$reply_markup
                        );
                        teleRequest('editMessageText', $msg_data);
                    }

                    if (strpos($data_cb, 'upd_clr_Tinjau_') === 0 or strpos($data_cb, 'upd_jnz_Tinjau_') === 0) {
                        $jenis = (strpos($data_cb, 'upd_clr_') === 0) ? 'clearance' : 'jenazah';
                        $id_perm = str_replace(array("upd_clr_Tinjau_", "upd_jnz_Tinjau_"), '', $data_cb);
                        $table = ($jenis == 'clearance') ? 'permohonan_clearance' : 'permohonan_jenazah';

                        $stmt = $pdo->prepare("UPDATE " . $table . " SET status = 'Sedang Ditinjau' WHERE id_permohonan = ?");
                        $stmt->execute(array($id_perm));

                        $stmt2 =$pdo->prepare("SELECT email_pemohon FROM " . $table . " WHERE id_permohonan = ?");
                        $stmt2->execute(array($id_perm));
                        $email =$stmt2->fetchColumn();

                        if ($email && trim($email) !== '') {$sub = "Status Diperbarui - " . $id_perm;
                            $msg = "<h3>Dokumen Sedang Ditinjau</h3><p>Permohonan dokumen Anda dengan ID <b>" . $id_perm . "</b> saat ini sedang dalam tahap peninjauan oleh petugas.</p>";
                            $headers  = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: SIDIA BAPERAN <admin@sidiabaperan.com>\r\n";
                            @mail($email,$sub, $msg,$headers);
                        }

                        $msg_data = array(
                            'chat_id' => $chat_id,
                            'text' => "[BERHASIL] Status *" . $id_perm . "* diubah menjadi Sedang Ditinjau.\n\nJika surat sudah diterbitkan, kirimkan file *PDF Surat* ke bot ini dengan Caption ID Permohonan (Contoh Caption: `" . $id_perm . "`).",
                            'parse_mode' => 'Markdown'
                        );
                        teleRequest('sendMessage', $msg_data);$del_data = array('chat_id' => $chat_id, 'message_id' =>$msg_id);
                        teleRequest('deleteMessage', $del_data);
                    }

                    $cb_data = array('callback_query_id' =>$cb_id);
                    teleRequest('answerCallbackQuery', $cb_data);
                }
            }
        }
    }
    sleep(2); 
}
?>