<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../handler/warga_handler.php';


?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Warga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- link css eksteral  -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="brand">
                <i class="fas fa-file-alt"></i> DOKUMEN WARGA
            </div>
            <ul class="nav flex-column mt-3">
                <li class="nav-item">
                    <a class="nav-link active" href="warga.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item dropdown-menu-item">
                    <a class="nav-link dropdown-toggle" href="#"
                        id="pengajuanDropdown"
                        data-bs-toggle="collapse"
                        data-bs-target="#pengajuanSubmenu">

                        <i class="fas fa-file-alt"></i> Pengajuan
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </a>
                    <div class="collapse submenu" id="pengajuanSubmenu">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link submenu-link" href="../pengajuan/form_pengajuan.php">
                                    <i class="fas fa-plus-circle"></i> Ajukan Dokumen
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link submenu-link" href="../pengajuan/lihat_status.php">
                                    <i class="fas fa-list"></i> Status Pengajuan
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item mt-3">
                    <a class="nav-link" href="?logout=true">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <div class="top-navbar d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Dashboard Warga</h5>
                <div>
                    <span class="me-3"><i class="fas fa-user"></i> <?= $user_data['nama_lengkap'] ?></span>
                    <span class="badge bg-success">Warga</span>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Welcome Card -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <h4>Selamat Datang, <?= $user_data['nama_lengkap'] ?>!</h4>
                        <p class="text-muted mb-0">Anda dapat mengajukan dokumen dan memantau status pengajuan Anda di sini.</p>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?= $total_pengajuan ?></h3>
                                        <small class="text-muted">Total Pengajuan</small>
                                    </div>
                                    <div class="icon-box bg-primary text-white">
                                        <i class="fas fa-file"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?= $total_pending ?></h3>
                                        <small class="text-muted">Pending</small>
                                    </div>
                                    <div class="icon-box bg-warning text-white">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?= $total_disetujui ?></h3>
                                        <small class="text-muted">Disetujui</small>
                                    </div>
                                    <div class="icon-box bg-success text-white">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?= $total_ditolak ?></h3>
                                        <small class="text-muted">Ditolak</small>
                                    </div>
                                    <div class="icon-box bg-danger text-white">
                                        <i class="fas fa-times"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Aksi Cepat</h5>
                        <a href="../pengajuan/form_pengajuan.php" class="btn btn-primary me-2">
                            <i class="fas fa-plus"></i> Ajukan Dokumen Baru
                        </a>
                        <a href="../pengajuan/lihat_status.php" class="btn btn-outline-primary">
                            <i class="fas fa-eye"></i> Lihat Status Pengajuan
                        </a>
                    </div>
                </div>

                <!-- Daftar Pengajuan Terbaru -->
                <div class="table-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Pengajuan Terbaru</h5>
                        <small class="text-muted">Halaman <?= $page ?> dari <?= $total_pages ?> (Total: <?= $total_records ?> data)</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No. Pengajuan</th>
                                    <th>Jenis Dokumen</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result_pengajuan) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($result_pengajuan)): ?>
                                        <tr>
                                            <td><?= $row['nomor_pengajuan'] ?></td>
                                            <td><?= $row['nama_dokumen'] ?></td>
                                            <td><?= format_tanggal($row['tanggal_pengajuan']) ?></td>
                                            <td><?= get_status_badge($row['nama_status'], $row['warna_badge']) ?></td>
                                            <td>
                                                <a href="../pengajuan/lihat_status.php?id=<?= $row['id_pengajuan'] ?>" class="btn btn-sm btn-info ms-3">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                <?php if ($row['id_status'] == 2): ?>
                                                    <a href="../pengajuan/unduh_surat.php?id=<?= $row['id_pengajuan'] ?>" class="btn btn-sm btn-success ms-3">
                                                        <i class="fas fa-download"></i> Unduh
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada pengajuan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Handle logout
    if (isset($_GET['logout'])) {
        echo "<script>
            Swal.fire({
                title: 'Logout?',
                text: 'Anda yakin ingin keluar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/includes/logout.php';
                }
            });
        </script>";
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>

    <?php if (isset($_GET['logout'])): ?>
        <script>
           <?php if (isset($_GET['logout'])): ?>
        confirmLogout('Anda yakin ingin keluar?').then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../includes/logout.php';
            } else {
                // Jika dibatalkan, hapus parameter ?logout dari URL  
                window.history.replaceState({}, document.title, 'warga.php');
            }
        });
    <?php endif; ?>

    // Dropdown submenu persistence
    document.addEventListener('DOMContentLoaded', function() {
        const submenu = document.getElementById('pengajuanSubmenu');
        const toggle = document.getElementById('pengajuanDropdown');

        // Saat halaman dibuka, cek status terakhir
        if (localStorage.getItem('pengajuan_open') === 'true') {
            submenu.classList.add('show');
        }

        // Saat dropdown dibuka
        submenu.addEventListener('shown.bs.collapse', function() {
            localStorage.setItem('pengajuan_open', 'true');
        });

        // Saat dropdown ditutup
        submenu.addEventListener('hidden.bs.collapse', function() {
            localStorage.setItem('pengajuan_open', 'false');
        });
    });
        </script>
    <?php endif; ?>

</body>

</html>