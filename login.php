<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Jika sudah login, redirect ke dashboard
if (is_logged_in()) {
    $role = $_SESSION['role'];
    redirect("dashboard/$role.php");
}

$error = '';
$success = '';

// Cek jika ada notifikasi registrasi berhasil
if (isset($_GET['registered']) && $_GET['registered'] == 'success') {
    $success = 'Registrasi berhasil! Silakan login dengan akun Anda.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = login($username, $password);

    if ($result['status']) {
        redirect("dashboard/" . $result['role'] . ".php");
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
    <title>Login - Aplikasi Pengajuan Dokumen</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .toggle-password:hover {
            color: #0d6efd;
        }

        .alert-success {
            background: rgba(209, 250, 229, 0.95) !important;
            border: 1px solid rgba(34, 197, 94, 0.4) !important;
            color: #15803d !important;
        }

        .alert-danger {
            background: rgba(254, 226, 226, 0.95) !important;
            border: 1px solid rgba(239, 68, 68, 0.4) !important;
            color: #b91c1c !important;
        }
    </style>

</head>

<body class="auth-page">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="login-card">
                    <button type="button" class="btn-close-custom" onclick="window.location.href='/index.php'">
                        X
                    </button>

                    <div class="card-body">
                        <div class="text-center">
                            <h3 class="login-title">Selamat datang</h3>
                        </div>

                        <!-- NOTIFIKASI SUCCESS (HIJAU) DITAMPILKAN DULU -->
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?= $success ?>
                            </div>
                        <?php endif; ?>

                        <!-- NOTIFIKASI ERROR (MERAH) -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="form-group">
                                <input type="text" class="form-input" id="username" name="username" placeholder=" " required>
                                <label for="username" class="form-label">Username</label>
                                <span class="input-icon"><i class="fa-solid fa-image-portrait"></i></span>
                            </div>
                            
                            <div class="form-group">
                                <input type="password" class="form-input" id="password" name="password" placeholder=" " required>
                                <label for="password" class="form-label">Password</label>

                                <!-- Icon show/hide password -->
                                <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
                            </div>

                            <button type="submit" class="btn-login">
                                Masuk
                            </button>

                            <div class="text-center">
                                <p class="register-text">Belum punya akun? <a href="register.php" class="register-link">Daftar di sini</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', () => {
            const isText = passwordInput.getAttribute('type') === 'text';
            passwordInput.setAttribute('type', isText ? 'password' : 'text');
            togglePassword.classList.toggle('fa-eye');
            togglePassword.classList.toggle('fa-eye-slash');
        });
    </script>

</body>

</html>