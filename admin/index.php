<?php
session_start();
require_once '../config/database.php';

// Jika sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['admin_logged'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_nama'] = $admin['nama_lengkap'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SIDIA BAPERAN</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container" style="max-width: 400px; margin-top: 80px;">
        <div class="card">
            <h2>Login Admin</h2>
            <p style="font-size: 0.85rem; color: #666; margin-bottom: 20px;">Masukkan akun administrator SIDIA BAPERAN.</p>

            <?php if ($error): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; font-size: 0.85rem; margin-bottom: 15px; text-align: center;">
                    <?= $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="admin">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="password">
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Masuk Dashboard</button>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <a href="../index.php" style="font-size: 0.85rem; color: #666; text-decoration: none;">&larr; Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>