<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Jika sudah login, redirect
if (is_logged_in()) {
    redirect("dashboard/" . $_SESSION['role'] . ".php");
}

$error = '';
$success = '';
$auto_redirect = false; // Flag untuk auto redirect

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $result = register($_POST);
    
    if ($result['status']) {
        $success = $result['message'];
        $auto_redirect = true; // Aktifkan auto redirect
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Aplikasi Pengajuan Dokumen</title>
    <link rel="stylesheet" href="assets/css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <?php if ($auto_redirect): ?>
    <script>
        // Auto redirect ke login setelah 3 detik
        setTimeout(function() {
            window.location.href = 'login.php?registered=success';
        }, 3000);
    </script>
    <?php endif; ?>
</head>
<body class="auth-page">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="register-card">
                    <button type="button" class="btn-close-custom" onclick="window.location.href='index.php'">
                        X
                    </button>
                    
                    <div class="card-body">
                        <div class="text-center">
                            <div class="icon-wrapper">
                                <i class="fa-solid fa-user-plus fa-4x gradient-icon"></i>
                            </div>
                            <h3 class="register-title">Daftar Akun Warga</h3>
                            <p class="register-subtitle">Buat akun baru untuk mengajukan dokumen</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?= $success ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="registerForm">
                            <div class="row-form">
                                <div class="col-half">
                                    <div class="form-group">
                                        <input type="text" class="form-input" id="username" name="username" placeholder=" " required>
                                        <label for="username" class="form-label">Username<span class="required">*</span></label>
                                    </div>
                                </div>
                                <div class="col-half">
                                    <div class="form-group">
                                        <input type="password" class="form-input" id="password" name="password" placeholder=" " required minlength="6">
                                        <label for="password" class="form-label">Password<span class="required">*</span></label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <input type="text" class="form-input" id="nama_lengkap" name="nama_lengkap" placeholder=" " required>
                                <label for="nama_lengkap" class="form-label">Nama Lengkap<span class="required">*</span></label>
                            </div>

                            <div class="form-group">
                                <input type="text" class="form-input" id="nik" name="nik" placeholder=" " required pattern="[0-9]{16}" maxlength="16">
                                <label for="nik" class="form-label">NIK(16digit)<span class="required">*</span></label>
                            </div>

                            <div class="row-form">
                                <div class="col-half">
                                    <div class="form-group">
                                        <input type="email" class="form-input" id="email" name="email" placeholder=" " required pattern="[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$">
                                        <label for="email" class="form-label">Email<span class="required">*</span></label>
                                    </div>
                                </div>
                                <div class="col-half">
                                    <div class="form-group">
                                        <input type="text" class="form-input" id="no_hp" name="no_hp" placeholder=" " pattern="[0-9]{10,13}">
                                        <label for="no_hp" class="form-label">No.Hp<span class="required">*</span></label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <textarea class="form-input form-textarea" id="alamat" name="alamat" placeholder=" " rows="3" required></textarea>
                                <label for="alamat" class="form-label">Alamat Lengkap<span class="required">*</span></label>
                            </div>

                            <button type="submit" class="btn-register">
                                Daftar
                            </button>

                            <div class="text-center">
                                <p class="login-text">Sudah punya akun? <a href="login.php" class="login-link">Masuk di sini</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>