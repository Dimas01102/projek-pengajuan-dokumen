<?php
http_response_code(500);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Internal Server Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
    <style>
        :root {
            --primary-blue: #2563eb;
            --secondary-blue: #3b82f6;
            --light-blue: #dbeafe;
            --dark-blue: #1e40af;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #60a5fa 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
        }

        .content-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            padding: 3rem;
            backdrop-filter: blur(10px);
        }

        .error-code {
            font-size: 8rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 1rem;
        }

        .error-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
        }

        .error-description {
            color: #64748b;
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .btn-custom {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            border: none;
            padding: 14px 35px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.4);
            color: white;
        }

        .illustration-box {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-radius: 25px;
            padding: 3rem;
            height: 450px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #lottie-animation {
            width: 100%;
            height: 100%;
            max-width: 500px;
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: float 8s ease-in-out infinite;
        }

        .shape-1 { 
            width: 300px; 
            height: 300px; 
            background: white; 
            top: -100px; 
            left: -100px; 
        }

        .shape-2 { 
            width: 200px; 
            height: 200px; 
            background: white; 
            bottom: -50px; 
            right: -50px; 
            animation-delay: 2s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-20px, -20px); }
        }

        .icon-feature {
            color: var(--primary-blue);
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .alert-warning-custom {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .error-code { font-size: 5rem; }
            .error-title { font-size: 1.8rem; }
            .illustration-box { height: 300px; padding: 2rem; }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        
        <div class="container">
            <div class="content-card">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="error-code">500</div>
                        <h1 class="error-title">Internal Server Error</h1>
                        <p class="error-description">
                            Maaf, terjadi kesalahan pada server kami. Tim teknis sedang bekerja untuk 
                            memperbaikinya. Silakan coba lagi dalam beberapa saat.
                        </p>
                        
                        <div class="alert-warning-custom">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-exclamation-triangle text-warning me-3 fs-4"></i>
                                <div>
                                    <h6 class="fw-bold mb-2">Apa yang bisa Anda lakukan?</h6>
                                    <ul class="mb-0 small">
                                        <li>Refresh halaman ini dalam beberapa menit</li>
                                        <li>Hapus cache browser Anda</li>
                                        <li>Coba akses dari browser lain</li>
                                        <li>Hubungi support jika masalah berlanjut</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 flex-wrap">
                            <button onclick="location.reload()" class="btn btn-custom">
                                <i class="bi bi-arrow-clockwise me-2"></i>Refresh Halaman
                            </button>
                            <a href="index.php" class="btn btn-outline-primary" style="border-radius: 50px; padding: 14px 35px;">
                                <i class="bi bi-house-door me-2"></i>Ke Beranda
                            </a>
                        </div>

                        <div class="row mt-5 g-4">
                            <div class="col-md-6">
                                <div class="icon-feature">
                                    <i class="bi bi-tools"></i>
                                </div>
                                <h6 class="fw-bold">Sedang Diperbaiki</h6>
                                <p class="text-muted small">Tim kami sudah diberitahu dan sedang menangani masalah ini</p>
                            </div>
                            <div class="col-md-6">
                                <div class="icon-feature">
                                    <i class="bi bi-chat-dots"></i>
                                </div>
                                <h6 class="fw-bold">Butuh Bantuan?</h6>
                                <p class="text-muted small">Hubungi support jika error terus berlanjut</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="illustration-box">
                            <div id="lottie-animation"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        lottie.loadAnimation({
            container: document.getElementById('lottie-animation'),
            renderer: 'svg',
            loop: true,
            autoplay: true,
            path: 'https://assets9.lottiefiles.com/packages/lf20_agnejizn.json'
        });
    </script>
</body>
</html>