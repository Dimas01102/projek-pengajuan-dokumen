<?php
ini_set('session.save_path', sys_get_temp_dir());
session_start();

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Jika sudah login, redirect ke dashboard sesuai role
if (is_logged_in()) {
    $role = $_SESSION['role'];
    redirect("dashboard/$role.php");
}

// Ambil jenis dokumen aktif untuk ditampilkan
$jenis_dokumen = mysqli_query($conn, "SELECT * FROM t_jenis_dokumen WHERE status = 'aktif' LIMIT 6");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Dokumen Warga Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- link css eksternal  -->
    <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>
   <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <i class="fas fa-file-alt me-2"></i>Dokumen Warga Digital
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Fitur</a></li>
                    <li class="nav-item"><a class="nav-link" href="#documents">Dokumen</a></li>
                    <li class="nav-item"><a class="nav-link" href="#steps">Cara Pengajuan</a></li>
                </ul>
                <div class="d-flex gap-2">
                    <a href="login.php" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                    <a href="register.php" class="btn btn-register">
                        <i class="fas fa-user-plus me-1"></i> Daftar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
   <section id="home" class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content">
                <h1 data-aos="fade-up" data-aos-duration="800">
                    Ajukan Dokumen <span class="highlight">Lebih Mudah</span>, Cepat, dan Terintegrasi
                </h1>
                <p data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">
                    Selamat datang di <strong>Dokumen Warga Digital</strong> platform layanan pengajuan dokumen kependudukan secara daring 
                    yang dirancang untuk memudahkan masyarakat dalam mengurus berbagai kebutuhan administrasi. 
                    Melalui sistem ini, Anda dapat mengajukan berkas penting seperti KTP, KK, surat keterangan, dan dokumen lainnya 
                    tanpa harus datang ke kantor kelurahan. Prosesnya lebih efisien, transparan, serta dapat dipantau secara real-time.
                </p>
            </div>
            <div class="col-lg-6 hero-image text-center mt-5 mt-lg-0">
                <img src="assets/img/img.png" alt="Pengajuan Dokumen Digital" data-aos="fade-left" data-aos-duration="800" data-aos-delay="300">
            </div>
        </div>
    </div>
</section>

    <!-- FEATURES SECTION -->
    <section id="features" class="features-section">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Kenapa Memilih Kami?</h2>
                <div class="underline"></div>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #0d6efd, #0a58ca);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h5>Proses Cepat</h5>
                        <p>Pengajuan dokumen diproses dalam waktu singkat dengan sistem yang efisien dan terorganisir</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #198754, #157347);">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5>Aman & Terpercaya</h5>
                        <p>Data Anda tersimpan dengan aman menggunakan sistem keamanan terenkripsi dan terpercaya</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #0dcaf0, #0aa2c0);">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h5>Akses Online 24/7</h5>
                        <p>Ajukan dokumen kapan saja dan dimana saja melalui perangkat mobile atau komputer Anda</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- DOCUMENTS SECTION -->
<section id="documents" class="documents-section">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Jenis Dokumen yang Tersedia</h2>
            <div class="underline"></div>
        </div>
        <div class="row g-4">
            <?php 
            $colors = ['#0d6efd', '#198754', '#0dcaf0', '#ffc107', '#dc3545', '#6f42c1'];
            $icons = ['file-invoice', 'store', 'home', 'id-card', 'certificate', 'file-contract'];
            $i = 0;
            $delay = 100; // delay awal untuk animasi
            while ($dok = mysqli_fetch_assoc($jenis_dokumen)): 
                $decoded = decode_deskripsi_with_config($dok['deskripsi']);
                $deskripsi_text = $decoded['deskripsi'] ?: 'Dokumen kependudukan untuk berbagai keperluan administrasi';
            ?>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="<?= $delay ?>">
                <div class="document-card">
                    <div class="document-icon" style="background: <?= $colors[$i % 6] ?>;">
                        <i class="fas fa-<?= $icons[$i % 6] ?>"></i>
                    </div>
                    <h5><?= $dok['nama_dokumen'] ?></h5>
                    <p><?= $deskripsi_text ?></p>
                </div>
            </div>
            <?php 
            $i++;
            $delay += 100; // tambah delay untuk setiap card
            if ($delay > 300) $delay = 100; // reset delay setelah 3 card
            endwhile; 
            ?>
        </div>
    </div>
</section>

    <!-- STEPS SECTION -->
    <section id="steps" class="steps-section">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Cara Mengajukan Dokumen</h2>
                <div class="underline"></div>
            </div>
            <div class="row g-4">
                <div class="col-md-3" data-aos="flip-left" data-aos-delay="100">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <h5>Daftar Akun</h5>
                        <p>Buat akun dengan mengisi data diri lengkap dan valid</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="flip-left" data-aos-delay="200">
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <h5>Login Sistem</h5>
                        <p>Masuk ke sistem menggunakan username dan password</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="flip-left" data-aos-delay="300">
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <h5>Isi Formulir</h5>
                        <p>Lengkapi formulir pengajuan dan upload berkas</p>
                    </div>
                </div>
                <div class="col-md-3" data-aos="flip-left" data-aos-delay="400">
                    <div class="step-item">
                        <div class="step-number">4</div>
                        <h5>Unduh Dokumen</h5>
                        <p>Download dokumen yang sudah disetujui</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100,
            easing: 'ease-in-out'
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll untuk navigasi
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>

</html>