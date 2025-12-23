<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../handler/petugas_handler.php';

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas</title>
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
                <i class="fas fa-user-shield"></i> PETUGAS
            </div>
            <ul class="nav flex-column mt-3">
                <li class="nav-item">
                    <a class="nav-link active" href="petugas.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?logout=true">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-navbar d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Dashboard Petugas</h5>
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        <i class="fas fa-clock text-primary"></i>
                        <span id="realtime-clock" class="fw-bold"></span>
                    </div>
                    <span class="me-3"><i class="fas fa-user"></i> <?= $user_data['nama_lengkap'] ?></span>
                    <span class="badge bg-info">Petugas</span>
                </div>
            </div>

            <div class="content-area">
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
                                        <small class="text-muted">Perlu Validasi</small>
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

                <!-- Daftar Pengajuan -->
                <div class="table-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Daftar Pengajuan Dokumen</h5>
                        <small class="text-muted">Halaman <?= $page ?> dari <?= $total_pages ?> (Total: <?= $total_records ?> data)</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No. Pengajuan</th>
                                    <th>Nama Warga</th>
                                    <th>Jenis Dokumen</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result_pengajuan)): ?>
                                    <tr>
                                        <td><?= $row['nomor_pengajuan'] ?></td>
                                        <td><?= $row['nama_warga'] ?></td>
                                        <td><?= $row['nama_dokumen'] ?></td>
                                        <td><?= format_tanggal($row['tanggal_pengajuan']) ?></td>
                                        <td><?= get_status_badge($row['nama_status'], $row['warna_badge']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info mb-2" onclick="showDetail(<?= htmlspecialchars(json_encode($row)) ?>)">
                                                <i class="fas fa-eye"></i> Detail
                                            </button>
                                            <?php if ($row['id_status'] == 1): ?>
                                                <button class="btn btn-sm btn-primary mb-2" onclick="showValidasi(<?= $row['id_pengajuan'] ?>)">
                                                    <i class="fas fa-check"></i> Validasi
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
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

    <!-- Modal Detail -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pengajuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent"></div>
            </div>
        </div>
    </div>

    <!-- Modal Validasi -->
    <div class="modal fade" id="validasiModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="validasiForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Validasi Pengajuan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_pengajuan" id="validasi_id_pengajuan">

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="id_status" class="form-select" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="2">Disetujui</option>
                                <option value="3">Ditolak</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan Validasi</label>
                            <textarea name="catatan" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_status" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Menampilkan notifikasi saat halaman di load
        window.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['success_message'])): ?>
                showSuccess('<?= $_SESSION['success_message'] ?>');
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                showError('<?= $_SESSION['error_message'] ?>');
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
        });

        // Fungsi showDetail khusus untuk petugas
        function showDetail(data) {
            viewDetail(data); 
        }

        // Fungsi showValidasi
        function showValidasi(id) {
            document.getElementById('validasi_id_pengajuan').value = id;
            new bootstrap.Modal(document.getElementById('validasiModal')).show();
        }

       <?php if (isset($_GET['logout'])): ?>
        confirmLogout('Anda yakin ingin keluar?').then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../includes/logout.php';
            } else {
                // Jika dibatalkan, hapus parameter ?logout dari URL
                window.history.replaceState({}, document.title, 'petugas.php');
            }
        });
    <?php endif; ?>
    </script>
</body>

</html>