<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Get pengajuan ID
if (!isset($_GET['id'])) {
    header("Location: " . ($user_role == 'warga' ? '../dashboard/warga.php' : '../dashboard/petugas.php'));
    exit();
}

$id_pengajuan = clean_input($_GET['id']);

// Get detail pengajuan
$query = "SELECT p.*, j.nama_dokumen, s.nama_status, s.warna_badge, 
          u.nama_lengkap, u.nik, u.email, u.no_hp, u.alamat,
          v.nama_lengkap as nama_validator
          FROM t_pengajuan p
          JOIN t_jenis_dokumen j ON p.id_jenis = j.id_jenis
          JOIN t_status_pengajuan s ON p.id_status = s.id_status
          JOIN t_pengguna u ON p.id_pengguna = u.id_pengguna
          LEFT JOIN t_pengguna v ON p.validasi_oleh = v.id_pengguna
          WHERE p.id_pengajuan = '$id_pengajuan'";

if ($user_role == 'warga') {
    $query .= " AND p.id_pengguna = '$user_id'";
}

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: " . ($user_role == 'warga' ? '../dashboard/warga.php' : '../dashboard/petugas.php'));
    exit();
}

$pengajuan = mysqli_fetch_assoc($result);

// Parse keterangan JSON
$keterangan_data = json_decode($pengajuan['keterangan'], true) ?: [];

// Get berkas dokumen
$query_berkas = "SELECT * FROM t_berkas_dokumen WHERE id_pengajuan = '$id_pengajuan'";
$result_berkas = mysqli_query($conn, $query_berkas);
$berkas = mysqli_num_rows($result_berkas) > 0 ? mysqli_fetch_assoc($result_berkas) : null;

// Handle validasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($user_role, ['petugas', 'admin'])) {
    if (isset($_POST['validasi'])) {
        $status = clean_input($_POST['status']);
        $catatan = clean_input($_POST['catatan']);
        $nomor_surat = '';

        if ($status == '2') {
            $nomor_surat = generate_nomor_surat($pengajuan['nama_dokumen']);
        }

        $query_update = "UPDATE t_pengajuan 
                        SET id_status = '$status', 
                            validasi_oleh = '$user_id',
                            tanggal_validasi = NOW(),
                            catatan_validasi = '$catatan'
                        WHERE id_pengajuan = '$id_pengajuan'";

        if (mysqli_query($conn, $query_update)) {
            $_SESSION['flash_message'] = 'Validasi berhasil disimpan';
            $_SESSION['flash_type'] = 'success';
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id_pengajuan);
            exit();
        }
    }
}

$flash_message = isset($_SESSION['flash_message']) ? $_SESSION['flash_message'] : '';
$flash_type = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'success';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengajuan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="dashboard-wrapper">
        <div class="sidebar">
            <div class="brand">
                <i class="fas fa-<?= $user_role == 'warga' ? 'file-alt' : ($user_role == 'petugas' ? 'user-check' : 'user-cog') ?>"></i>
                <?= strtoupper($user_role) ?> PANEL
            </div>
            <ul class="nav flex-column mt-3">
                <li class="nav-item">
                    <a class="nav-link" href="../dashboard/<?= $user_role ?>.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <?php if ($user_role == 'warga'): ?>
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
                <?php endif; ?>
                <li class="nav-item mt-3">
                    <a class="nav-link" href="?logout=true">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <div class="main-content">
            <div class="top-navbar d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Pengajuan</h5>
                <div>
                    <span class="me-3"><i class="fas fa-user"></i> <?= $_SESSION['nama_lengkap'] ?></span>
                    <span class="badge bg-<?= $user_role == 'admin' ? 'danger' : ($user_role == 'petugas' ? 'info' : 'success') ?>">
                        <?= ucfirst($user_role) ?>
                    </span>
                </div>
            </div>

            <div class="content-area">
                <?php if ($flash_message): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            <?php if ($flash_type === 'success'): ?>
                                showSuccess('<?= addslashes($flash_message) ?>');
                            <?php else: ?>
                                showError('<?= addslashes($flash_message) ?>');
                            <?php endif; ?>
                        });
                    </script>
                <?php endif; ?>

                <!-- Info Pengajuan -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Pengajuan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%"><strong>No. Pengajuan</strong></td>
                                        <td>: <?= $pengajuan['nomor_pengajuan'] ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jenis Dokumen</strong></td>
                                        <td>: <?= $pengajuan['nama_dokumen'] ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Pengajuan</strong></td>
                                        <td>: <?= format_tanggal($pengajuan['tanggal_pengajuan']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status</strong></td>
                                        <td>: <?= get_status_badge($pengajuan['nama_status'], $pengajuan['warna_badge']) ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%"><strong>Nama Pemohon</strong></td>
                                        <td>: <?= $pengajuan['nama_lengkap'] ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>NIK</strong></td>
                                        <td>: <?= $pengajuan['nik'] ?: '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email</strong></td>
                                        <td>: <?= $pengajuan['email'] ?: '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>No. HP</strong></td>
                                        <td>: <?= $pengajuan['no_hp'] ?: '-' ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Pengajuan -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-edit"></i> Data Pengajuan</h5>
                    </div>
                    <div class="card-body">
                        <?php if (is_array($keterangan_data) && count($keterangan_data) > 0): ?>
                            <div class="row">
                                <?php foreach ($keterangan_data as $key => $value):
                                    $label = ucwords(str_replace('_', ' ', $key));
                                ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 bg-light rounded">
                                            <small class="text-muted d-block mb-1"><?= $label ?></small>
                                            <strong><?= htmlspecialchars($value) ?: '-' ?></strong>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">Tidak ada data tambahan</p>
                        <?php endif; ?>

                        <hr>

                        <div class="mt-3">
                            <h6><strong>Keperluan:</strong></h6>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($pengajuan['keperluan'])) ?></p>
                        </div>

                        <?php if ($pengajuan['alamat']): ?>
                            <div class="mt-3">
                                <h6><strong>Alamat Pemohon:</strong></h6>
                                <p class="text-muted"><?= nl2br(htmlspecialchars($pengajuan['alamat'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Berkas -->
                <?php
                $query_berkas_all = "SELECT * FROM t_berkas_dokumen WHERE id_pengajuan = '$id_pengajuan' ORDER BY id_berkas";
                $result_berkas_all = mysqli_query($conn, $query_berkas_all);
                $total_berkas = mysqli_num_rows($result_berkas_all);
                ?>

                <?php if ($total_berkas > 0): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-paperclip"></i> Berkas Pendukung
                                <span class="badge bg-light text-dark"><?= $total_berkas ?> File</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if ($total_berkas == 1): ?>
                                <!-- Single file display -->
                                <?php
                                $berkas = mysqli_fetch_assoc($result_berkas_all);
                                $parsed = parse_file_label($berkas['nama_file']);
                                ?>
                                <div class="row align-items-center">
                                    <div class="col-md-2 text-center">
                                        <i class="fas fa-file-pdf text-danger" style="font-size: 3rem;"></i>
                                    </div>
                                    <div class="col-md-7">
                                        <?php if ($parsed['label'] != 'Dokumen Pendukung'): ?>
                                            <div class="mb-2">
                                                <span class="badge bg-info" style="font-size: 0.9rem;">
                                                    <i class="fas fa-tag"></i> <?= htmlspecialchars($parsed['label']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <h6 class="mb-1"><?= htmlspecialchars($parsed['filename']) ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-hdd"></i> <?= format_file_size($berkas['ukuran_file']) ?> |
                                            <i class="fas fa-file-alt"></i> <?= strtoupper($berkas['tipe_file']) ?>
                                        </small>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <a href="<?= UPLOAD_PATH . $berkas['path_file'] ?>"
                                            class="btn btn-primary"
                                            target="_blank">
                                            <i class="fas fa-download"></i> Unduh
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Multiple files display -->
                                <div class="row g-3">
                                    <?php
                                    mysqli_data_seek($result_berkas_all, 0);
                                    while ($berkas = mysqli_fetch_assoc($result_berkas_all)):
                                        $parsed = parse_file_label($berkas['nama_file']);
                                    ?>
                                        <div class="col-md-6">
                                            <div class="card h-100 border-primary">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-start">
                                                        <div class="flex-shrink-0 me-3">
                                                            <i class="fas fa-file-pdf text-danger" style="font-size: 2.5rem;"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="mb-2">
                                                                <span class="badge bg-info">
                                                                    <i class="fas fa-tag"></i> <?= htmlspecialchars($parsed['label']) ?>
                                                                </span>
                                                            </div>
                                                            <h6 class="card-title mb-1" style="font-size: 0.95rem;">
                                                                <?= htmlspecialchars($parsed['filename']) ?>
                                                            </h6>
                                                            <p class="card-text text-muted mb-2" style="font-size: 0.85rem;">
                                                                <i class="fas fa-hdd"></i> <?= format_file_size($berkas['ukuran_file']) ?>
                                                            </p>
                                                            <a href="<?= UPLOAD_PATH . $berkas['path_file'] ?>"
                                                                class="btn btn-sm btn-primary"
                                                                target="_blank"
                                                                title="Unduh <?= htmlspecialchars($parsed['label']) ?>">
                                                                <i class="fas fa-download"></i> Unduh File
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Tidak ada berkas pendukung yang diupload
                    </div>
                <?php endif; ?>

                <!-- Status Validasi -->
                <?php if ($pengajuan['id_status'] != 1): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fas fa-check-circle"></i> Informasi Validasi</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> <?= get_status_badge($pengajuan['nama_status'], $pengajuan['warna_badge']) ?></p>
                                    <?php if ($pengajuan['tanggal_validasi']): ?>
                                        <p><strong>Tanggal Validasi:</strong> <?= format_tanggal($pengajuan['tanggal_validasi']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($pengajuan['nama_validator']): ?>
                                        <p><strong>Divalidasi oleh:</strong> <?= $pengajuan['nama_validator'] ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <?php if ($pengajuan['catatan_validasi']): ?>
                                        <p><strong>Catatan:</strong></p>
                                        <div class="alert alert-info"><?= nl2br(htmlspecialchars($pengajuan['catatan_validasi'])) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form Validasi -->
                <?php if (in_array($user_role, ['petugas', 'admin']) && $pengajuan['id_status'] == 1): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fas fa-check-double"></i> Form Validasi</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status Validasi *</label>
                                    <select name="status" class="form-select" required>
                                        <option value="">-- Pilih Status --</option>
                                        <option value="2">Disetujui</option>
                                        <option value="3">Ditolak</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Catatan Validasi</label>
                                    <textarea name="catatan" class="form-control" rows="4"
                                        placeholder="Berikan catatan untuk pemohon (opsional)"></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="submit" name="validasi" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> Simpan Validasi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tombol Kembali -->
                <div class="text-center">
                    <a href="<?= $user_role == 'warga' ? 'lihat_status.php' : '../dashboard/' . $user_role . '.php' ?>"
                        class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>

    <?php if (isset($_GET['logout'])): ?>
        <script>
            confirmDelete('Yakin ingin logout?').then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../includes/logout.php';
                }
            });
        </script>
    <?php endif; ?>
</body>

</html>