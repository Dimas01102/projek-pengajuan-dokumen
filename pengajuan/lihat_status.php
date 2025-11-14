<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
check_role(['warga']);

$user_id = $_SESSION['user_id'];
$user_data = get_user_data($user_id);

// Get detail jika ada parameter id
$detail_data = null;
if (isset($_GET['id'])) {
    $id_pengajuan = clean_input($_GET['id']);
    $query_detail = "SELECT p.*, j.nama_dokumen, s.nama_status, s.warna_badge, u.nama_lengkap as validasi_nama
                     FROM t_pengajuan p
                     JOIN t_jenis_dokumen j ON p.id_jenis = j.id_jenis
                     JOIN t_status_pengajuan s ON p.id_status = s.id_status
                     LEFT JOIN t_pengguna u ON p.validasi_oleh = u.id_pengguna
                     WHERE p.id_pengajuan = '$id_pengajuan' AND p.id_pengguna = '$user_id'";
    $result_detail = mysqli_query($conn, $query_detail);
    $detail_data = mysqli_fetch_assoc($result_detail);

    // Get berkas
    if ($detail_data) {
        $query_berkas = "SELECT * FROM t_berkas_dokumen WHERE id_pengajuan = '$id_pengajuan'";
        $result_berkas = mysqli_query($conn, $query_berkas);
        $berkas_data = mysqli_fetch_assoc($result_berkas);

        // Get riwayat
        $query_riwayat = "SELECT r.*, s.nama_status, u.nama_lengkap
                          FROM t_riwayat_status r
                          JOIN t_status_pengajuan s ON r.id_status = s.id_status
                          LEFT JOIN t_pengguna u ON r.diubah_oleh = u.id_pengguna
                          WHERE r.id_pengajuan = '$id_pengajuan'
                          ORDER BY r.tanggal_perubahan DESC";
        $result_riwayat = mysqli_query($conn, $query_riwayat);
    }
}

// Pagination untuk daftar pengajuan
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total records untuk user ini
$query_count = "SELECT COUNT(*) as total FROM t_pengajuan WHERE id_pengguna = '$user_id'";
$total_records = mysqli_fetch_assoc(mysqli_query($conn, $query_count))['total'];
$total_pages = ceil($total_records / $limit);

// Get semua pengajuan user dengan pagination
$query_pengajuan = "SELECT p.*, j.nama_dokumen, s.nama_status, s.warna_badge
                    FROM t_pengajuan p
                    JOIN t_jenis_dokumen j ON p.id_jenis = j.id_jenis
                    JOIN t_status_pengajuan s ON p.id_status = s.id_status
                    WHERE p.id_pengguna = '$user_id'
                    ORDER BY p.tanggal_pengajuan DESC
                    LIMIT $limit OFFSET $offset";
$result_pengajuan = mysqli_query($conn, $query_pengajuan);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pengajuan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="dashboard-wrapper">
        <div class="sidebar">
            <div class="brand">
                <i class="fas fa-file-alt"></i> DOKUMEN WARGA
            </div>
            <ul class="nav flex-column mt-3">
                <li class="nav-item">
                    <a class="nav-link" href="../dashboard/warga.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="form_pengajuan.php">
                        <i class="fas fa-plus-circle"></i> Ajukan Dokumen
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="lihat_status.php">
                        <i class="fas fa-list"></i> Status Pengajuan
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a class="nav-link" href="?logout=true">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <div class="main-content">
            <div class="top-navbar d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Status Pengajuan Dokumen</h5>
                <div>
                    <span class="me-3"><i class="fas fa-user"></i> <?= $user_data['nama_lengkap'] ?></span>
                </div>
            </div>

            <div class="content-area">
                <?php if ($detail_data): ?>
                    <!-- Detail Pengajuan -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Detail Pengajuan</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="200"><strong>Nomor Pengajuan</strong></td>
                                            <td><?= $detail_data['nomor_pengajuan'] ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jenis Dokumen</strong></td>
                                            <td><?= $detail_data['nama_dokumen'] ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Keperluan</strong></td>
                                            <td><?= $detail_data['keperluan'] ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal Pengajuan</strong></td>
                                            <td><?= format_tanggal($detail_data['tanggal_pengajuan']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status</strong></td>
                                            <td><?= get_status_badge($detail_data['nama_status'], $detail_data['warna_badge']) ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-3">Keterangan Tambahan:</h6>
                                    <?php
                                    $keterangan = json_decode($detail_data['keterangan'], true);
                                    if ($keterangan):
                                        foreach ($keterangan as $key => $value):
                                    ?>
                                            <p><strong><?= ucwords(str_replace('_', ' ', $key)) ?>:</strong> <?= $value ?></p>
                                    <?php
                                        endforeach;
                                    endif;
                                    ?>

                                    <?php if ($berkas_data): ?>
                                        <hr>
                                        <h6>Berkas Terupload:</h6>
                                        <p>
                                            <i class="fas fa-file"></i> <?= $berkas_data['nama_file'] ?>
                                            (<?= format_file_size($berkas_data['ukuran_file']) ?>)
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($detail_data['catatan_validasi']): ?>
                                <div class="alert alert-info mt-3">
                                    <strong><i class="fas fa-comment"></i> Catatan Validasi:</strong><br>
                                    <?= $detail_data['catatan_validasi'] ?><br>
                                    <small>Oleh: <?= $detail_data['validasi_nama'] ?> pada <?= format_tanggal($detail_data['tanggal_validasi']) ?></small>
                                </div>
                            <?php endif; ?>

                            <?php if ($detail_data['id_status'] == 2): ?>
                                <div class="mt-3">
                                    <a href="unduh_surat.php?id=<?= $detail_data['id_pengajuan'] ?>" class="btn btn-success">
                                        <i class="fas fa-download"></i> Unduh Surat
                                    </a>
                                </div>
                            <?php endif; ?>

                            <!-- Riwayat Status -->
                            <hr>
                            <h6>Riwayat Status:</h6>
                            <div class="timeline">
                                <?php while ($riwayat = mysqli_fetch_assoc($result_riwayat)): ?>
                                    <div class="mb-3">
                                        <span class="badge bg-secondary"><?= format_tanggal($riwayat['tanggal_perubahan']) ?></span>
                                        <strong><?= $riwayat['nama_status'] ?></strong>
                                        <?php if ($riwayat['catatan']): ?>
                                            - <?= $riwayat['catatan'] ?>
                                        <?php endif; ?>
                                        <?php if ($riwayat['nama_lengkap']): ?>
                                            <br><small class="text-muted">Oleh: <?= $riwayat['nama_lengkap'] ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            </div>

                            <a href="lihat_status.php" class="btn btn-danger mt-3">
                                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Daftar Pengajuan -->
                    <div class="table-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Daftar Pengajuan Anda</h5>
                            <small class="text-muted">Halaman <?= $page ?> dari <?= $total_pages ?> (Total: <?= $total_records ?> data)</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No. Pengajuan</th>
                                        <th>Jenis Dokumen</th>
                                        <th>Keperluan</th>
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
                                                <td><?= $row['keperluan'] ?></td>
                                                <td><?= format_tanggal($row['tanggal_pengajuan']) ?></td>
                                                <td><?= get_status_badge($row['nama_status'], $row['warna_badge']) ?></td>
                                                <td>
                                                    <a href="lihat_status.php?id=<?= $row['id_pengajuan'] ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> Detail
                                                    </a>
                                                    <?php if ($row['id_status'] == 2): ?>
                                                        <a href="unduh_surat.php?id=<?= $row['id_pengajuan'] ?>" class="btn btn-sm btn-success">
                                                            <i class="fas fa-download"></i> Unduh
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                Belum ada pengajuan. <a href="form_pengajuan.php">Ajukan dokumen sekarang</a>
                                            </td>
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
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if (isset($_GET['logout'])): ?>
            Swal.fire({
                title: 'Logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Logout'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../includes/logout.php';
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>