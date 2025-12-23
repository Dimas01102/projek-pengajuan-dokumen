<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../handler/admin_handler.php';

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- link css eksteral  -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="dashboard-wrapper">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="brand"><i class="fas fa-user-cog"></i> ADMIN PANEL</div>
            <ul class="nav flex-column mt-3">
                <li class="nav-item"><a class="nav-link <?= $active_section == 'statistik' ? 'active' : '' ?>" href="?section=statistik" data-section="statistik"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?= $active_section == 'pengajuan' ? 'active' : '' ?>" href="?section=pengajuan" data-section="pengajuan"><i class="fas fa-folder-open"></i> Daftar Pengajuan</a></li>
                <li class="nav-item"><a class="nav-link <?= $active_section == 'petugas' ? 'active' : '' ?>" href="?section=petugas" data-section="petugas"><i class="fas fa-user-shield"></i> Kelola Petugas</a></li>
                <li class="nav-item"><a class="nav-link <?= $active_section == 'warga' ? 'active' : '' ?>" href="?section=warga" data-section="warga"><i class="fas fa-users"></i> Kelola Warga</a></li>
                <li class="nav-item"><a class="nav-link <?= $active_section == 'jenis-dokumen' ? 'active' : '' ?>" href="?section=jenis-dokumen" data-section="jenis-dokumen"><i class="fas fa-file-alt"></i> Kelola Jenis Dokumen</a></li>
                <li class="nav-item"><a class="nav-link <?= $active_section == 'laporan' ? 'active' : '' ?>" href="?section=laporan" data-section="laporan"><i class="fas fa-file-pdf"></i> Data Laporan</a></li>
                <li class="nav-item mt-3"><a class="nav-link" href="?logout=true"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <div class="top-navbar d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Dashboard Administrator</h5>
                <div class="d-flex align-items-center">
                    <div class="me-4"><i class="fas fa-clock text-primary"></i> <span id="realtime-clock" class="fw-bold"></span></div>
                    <span class="me-3"><i class="fas fa-user"></i> <?= $user_data['nama_lengkap'] ?></span>
                    <span class="badge bg-danger">Admin</span>
                </div>
            </div>

            <div class="content-area">
                <!-- STATISTIK -->
                <div id="statistik" class="content-section" style="display:<?= $active_section == 'statistik' ? 'block' : 'none' ?>">
                    <h4 class="mb-4">Statistik Aplikasi</h4>
                    <div class="row mb-4">
                        <?php
                        $stat_cards = [
                            ['label' => 'Total Warga', 'value' => $stats['warga'], 'color' => 'primary', 'icon' => 'users'],
                            ['label' => 'Warga Aktif', 'value' => $stats['warga_aktif'], 'color' => 'success', 'icon' => 'user-check'],
                            ['label' => 'Total Petugas', 'value' => $stats['petugas'], 'color' => 'info', 'icon' => 'user-shield'],
                            ['label' => 'Petugas Aktif', 'value' => $stats['petugas_aktif'], 'color' => 'danger', 'icon' => 'user-shield'],
                            ['label' => 'Pending', 'value' => $stats['pending'], 'color' => 'warning', 'icon' => 'clock'],
                            ['label' => 'Disetujui', 'value' => $stats['disetujui'], 'color' => 'success', 'icon' => 'check']
                        ];
                        foreach ($stat_cards as $card): ?>
                            <div class="col-md-2 mb-3">
                                <div class="card stat-card shadow-sm bg-<?= $card['color'] ?> text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-<?= $card['icon'] ?> fa-2x mb-2"></i>
                                        <h3 class="mb-0"><?= $card['value'] ?></h3>
                                        <small><?= $card['label'] ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- GRAFIK -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Distribusi Status Pengajuan</h6>
                                </div>
                                <div class="card-body"><canvas id="chartStatusPie"></canvas></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Pengajuan per Jenis Dokumen</h6>
                                </div>
                                <div class="card-body"><canvas id="chartJenisBar"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DAFTAR PENGAJUAN -->
                <div id="pengajuan" class="content-section" style="display:<?= $active_section == 'pengajuan' ? 'block' : 'none' ?>">
                    <div class="table-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fas fa-folder-open"></i> Daftar Semua Pengajuan</h5>
                            <small class="text-muted">Halaman <?= $page ?> dari <?= $total_pages_pengajuan ?> (Total: <?= $total_pengajuan ?> data)</small>
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
                                    <?php while ($p = mysqli_fetch_assoc($result_pengajuan)): ?>
                                        <tr>
                                            <td><?= $p['nomor_pengajuan'] ?></td>
                                            <td><?= $p['nama_warga'] ?></td>
                                            <td><?= $p['nama_dokumen'] ?></td>
                                            <td><?= format_tanggal($p['tanggal_pengajuan']) ?></td>
                                            <td><?= get_status_badge($p['nama_status'], $p['warna_badge']) ?></td>
                                            <td><button class="btn btn-sm btn-info" onclick='viewDetail(<?= json_encode($p) ?>)'><i class="fas fa-eye"></i> Detail</button></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages_pengajuan > 1): ?>
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?section=pengajuan&page=<?= $page - 1 ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages_pengajuan; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?section=pengajuan&page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $total_pages_pengajuan ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?section=pengajuan&page=<?= $page + 1 ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- KELOLA PETUGAS -->
                <div id="petugas" class="content-section" style="display:<?= $active_section == 'petugas' ? 'block' : 'none' ?>">
                    <div class="table-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fas fa-user-shield"></i> Kelola Data Petugas</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPetugasModal"><i class="fas fa-plus"></i> Tambah Petugas</button>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> <strong>Info:</strong> Petugas yang dinonaktifkan tidak dapat login ke sistem. Gunakan fitur nonaktifkan sebagai alternatif aman daripada menghapus data.
                        </div>
                        <div class="d-flex justify-content-end mb-2">
                            <small class="text-muted">Halaman <?= $page ?> dari <?= $total_pages_petugas ?> (Total: <?= $total_petugas_count ?> data)</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>NIK</th>
                                        <th>Email</th>
                                        <th>No. HP</th>
                                        <th>Status</th>
                                        <th>Tanggal Daftar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($pet = mysqli_fetch_assoc($result_petugas)): ?>
                                        <tr class="<?= $pet['status'] == 'nonaktif' ? 'table-secondary' : '' ?>">
                                            <td><?= $pet['username'] ?></td>
                                            <td><?= $pet['nama_lengkap'] ?></td>
                                            <td><?= $pet['nik'] ?: '-' ?></td>
                                            <td><?= $pet['email'] ?: '-' ?></td>
                                            <td><?= $pet['no_hp'] ?: '-' ?></td>
                                            <td>
                                                <span class="badge bg-<?= $pet['status'] == 'aktif' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($pet['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= format_tanggal($pet['tanggal_daftar']) ?></td>
                                            <td class="d-inline-flex align-items-center gap-1">
                                                <button class="btn btn-sm btn-info" onclick='editPetugas(<?= json_encode($pet) ?>)' title="Edit"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-warning" onclick="resetPassword(<?= $pet['id_pengguna'] ?>, '<?= $pet['nama_lengkap'] ?>')" title="Reset Password"><i class="fas fa-key"></i></button>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="id_pengguna" value="<?= $pet['id_pengguna'] ?>">
                                                    <input type="hidden" name="current_status" value="<?= $pet['status'] ?>">
                                                    <button type="submit" name="toggle_petugas_status"
                                                        class="btn btn-sm <?= $pet['status'] == 'aktif' ? 'btn-success' : 'btn-secondary' ?>"
                                                        title="<?= $pet['status'] == 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                                        <i class="fas fa-<?= $pet['status'] == 'aktif' ? 'toggle-on' : 'toggle-off' ?>"></i>
                                                    </button>
                                                </form>
                                                <button class="btn btn-sm btn-danger" onclick="hapusPetugas(<?= $pet['id_pengguna'] ?>, '<?= $pet['nama_lengkap'] ?>')" title="Hapus"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages_petugas > 1): ?>
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?section=petugas&page=<?= $page - 1 ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages_petugas; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?section=petugas&page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $total_pages_petugas ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?section=petugas&page=<?= $page + 1 ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- KELOLA WARGA -->
                <div id="warga" class="content-section" style="display:<?= $active_section == 'warga' ? 'block' : 'none' ?>">
                    <div class="table-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fas fa-users"></i> Kelola Data Warga</h5>
                            <small class="text-muted">Halaman <?= $page ?> dari <?= $total_pages_warga ?> (Total: <?= $total_warga_count ?> data)</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>NIK</th>
                                        <th>Email</th>
                                        <th>No. HP</th>
                                        <th>Tanggal Daftar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($w = mysqli_fetch_assoc($result_warga)): ?>
                                        <tr>
                                            <td><?= $w['username'] ?></td>
                                            <td><?= $w['nama_lengkap'] ?></td>
                                            <td><?= $w['nik'] ?: '-' ?></td>
                                            <td><?= $w['email'] ?: '-' ?></td>
                                            <td><?= $w['no_hp'] ?: '-' ?></td>
                                            <td><?= format_tanggal($w['tanggal_daftar']) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info mb-1" onclick='viewWargaDetail(<?= json_encode($w) ?>)' title="Lihat Detail"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-danger mb-1" onclick="hapusWarga(<?= $w['id_pengguna'] ?>, '<?= $w['nama_lengkap'] ?>')" title="Hapus"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages_warga > 1): ?>
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?section=warga&page=<?= $page - 1 ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages_warga; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?section=warga&page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $total_pages_warga ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?section=warga&page=<?= $page + 1 ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- KELOLA JENIS DOKUMEN -->
                <div id="jenis-dokumen" class="content-section" style="display:<?= $active_section == 'jenis-dokumen' ? 'block' : 'none' ?>">
                    <div class="table-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fas fa-file-alt"></i> Kelola Jenis Dokumen</h5>
                            <button type="button" class="btn btn-primary" onclick="openAddJenisModal()">
                                <i class="fas fa-plus"></i> Tambah Jenis Dokumen
                            </button>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> <strong>Catatan:</strong> Jenis dokumen yang dinonaktifkan tidak akan muncul di form pengajuan warga, tetapi data historisnya tetap tersimpan.
                        </div>
                        <div class="d-flex justify-content-end mb-2">
                            <small class="text-muted">Halaman <?= $page ?> dari <?= $total_pages_jenis ?> (Total: <?= $total_jenis_count ?> data)</small>
                        </div>
                        <!--TABEL JENIS DOKUMEN -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Dokumen</th>
                                        <th>Deskripsi</th>
                                        <th>Jumlah Field</th>
                                        <th>Upload Required</th>
                                        <th>Status</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($j = mysqli_fetch_assoc($result_jenis)):
                                        $decoded = decode_deskripsi_with_config($j['deskripsi']);
                                        $field_count = count($decoded['field_config']);
                                        $upload_count = $decoded['upload_config']['jumlah'];
                                    ?>
                                        <tr>
                                            <td><strong><?= $j['nama_dokumen'] ?></strong></td>
                                            <td><?= substr($decoded['deskripsi'], 0, 50) . (strlen($decoded['deskripsi']) > 50 ? '...' : '') ?></td>
                                            <td><span class="badge bg-info"><?= $field_count ?> Field</span></td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-file-upload"></i> <?= $upload_count ?> File
                                                </span>
                                            </td>
                                            <td><span class="badge bg-<?= $j['status'] == 'aktif' ? 'success' : 'secondary' ?>"><?= ucfirst($j['status']) ?></span></td>
                                            <td><?= format_tanggal($j['tanggal_dibuat']) ?></td>
                                            <td class="d-inline-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-info"
                                                    onclick='editJenis(<?= json_encode(["id" => $j["id_jenis"], "nama" => $j["nama_dokumen"], "data" => $decoded]) ?>)'
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <button type="button"
                                                    class="btn btn-sm btn-<?= $j['status'] == 'aktif' ? 'success' : 'secondary' ?>"
                                                    onclick="toggleJenisStatus(<?= $j['id_jenis'] ?>, '<?= $j['status'] ?>', '<?= addslashes($j['nama_dokumen']) ?>')"
                                                    title="<?= $j['status'] == 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                                    <i class="fas fa-<?= $j['status'] == 'aktif' ? 'toggle-on' : 'toggle-off' ?>"></i>
                                                </button>

                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="hapusJenis(<?= $j['id_jenis'] ?>, '<?= addslashes($j['nama_dokumen']) ?>')"
                                                    title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages_jenis > 1): ?>
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?section=jenis-dokumen&page=<?= $page - 1 ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages_jenis; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?section=jenis-dokumen&page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $total_pages_jenis ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?section=jenis-dokumen&page=<?= $page + 1 ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- LAPORAN SISTEM -->
                <div id="laporan" class="content-section" style="display:<?= $active_section == 'laporan' ? 'block' : 'none' ?>">
                    <!-- FILTER LAPORAN -->
                    <div class="card shadow-sm mb-4 no-print">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-filter"></i> Filter Laporan</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <input type="hidden" name="section" value="laporan">
                                <div class="col-md-5">
                                    <label class="form-label">Tanggal Mulai</label>
                                    <input type="date" name="tgl_mulai" class="form-control" value="<?= $tgl_mulai ?>" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Tanggal Akhir</label>
                                    <input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir ?>" required>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-file-pdf"></i> Laporan Sistem Pengajuan Dokumen</h5>
                            <button class="btn btn-light btn-sm no-print" onclick="window.print()"><i class="fas fa-print"></i> Cetak Laporan</button>
                        </div>
                        <div class="card-body" id="printArea">
                            <div class="text-center mb-4">
                                <h4>LAPORAN SISTEM PENGAJUAN DOKUMEN WARGA DIGITAL</h4>
                                <p class="mb-1">Periode: <?= date('d F Y', strtotime($tgl_mulai)) ?> - <?= date('d F Y', strtotime($tgl_akhir)) ?></p>
                                <p class="mb-0">Tanggal Cetak: <?= date('d F Y, H:i:s') ?></p>
                            </div>

                            <h6 class="mt-4 mb-3"><i class="fas fa-chart-bar"></i> Ringkasan Statistik</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr class="table-primary">
                                            <th colspan="2">Data Pengguna</th>
                                        </tr>
                                        <tr>
                                            <td>Total Warga Terdaftar</td>
                                            <td><strong><?= $stats['warga'] ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td>Total Petugas Aktif</td>
                                            <td><strong><?= $stats['petugas'] ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td>Total Administrator</td>
                                            <td><strong>1</strong></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr class="table-success">
                                            <th colspan="2">Data Pengajuan</th>
                                        </tr>
                                        <tr>
                                            <td>Total Pengajuan</td>
                                            <td><strong><?= $stats['pengajuan'] ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td>Pengajuan Pending</td>
                                            <td><strong><?= $stats['pending'] ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td>Pengajuan Disetujui</td>
                                            <td><strong><?= $stats['disetujui'] ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td>Pengajuan Ditolak</td>
                                            <td><strong><?= $stats['ditolak'] ?></strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <h6 class="mt-4 mb-3"><i class="fas fa-file-alt"></i> Statistik Per Jenis Dokumen</h6>
                            <table class="table table-bordered table-hover">
                                <thead class="table-info">
                                    <tr>
                                        <th>Jenis Dokumen</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Pending</th>
                                        <th class="text-center">Disetujui</th>
                                        <th class="text-center">Ditolak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Query ulang untuk laporan (tidak pakai pagination)
                                    $result_jenis_laporan = mysqli_query($conn, "SELECT * FROM t_jenis_dokumen ORDER BY tanggal_dibuat DESC");
                                    while ($jenis = mysqli_fetch_assoc($result_jenis_laporan)):
                                        $id = $jenis['id_jenis'];
                                        $tj = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM t_pengajuan WHERE id_jenis='$id'"))['t'];
                                        $pj = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM t_pengajuan WHERE id_jenis='$id' AND id_status=1"))['t'];
                                        $dj = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM t_pengajuan WHERE id_jenis='$id' AND id_status=2"))['t'];
                                        $rj = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM t_pengajuan WHERE id_jenis='$id' AND id_status=3"))['t'];
                                    ?>
                                        <tr>
                                            <td><?= $jenis['nama_dokumen'] ?></td>
                                            <td class="text-center"><strong><?= $tj ?></strong></td>
                                            <td class="text-center"><?= $pj ?></td>
                                            <td class="text-center"><?= $dj ?></td>
                                            <td class="text-center"><?= $rj ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>

                            <h6 class="mt-4 mb-3"><i class="fas fa-user-check"></i> Aktivitas Petugas (Periode: <?= date('d/m/Y', strtotime($tgl_mulai)) ?> - <?= date('d/m/Y', strtotime($tgl_akhir)) ?>)</h6>
                            <?php if (mysqli_num_rows($aktivitas_petugas) > 0): ?>
                                <table class="table table-bordered table-hover">
                                    <thead class="table-warning">
                                        <tr>
                                            <th>Nama Petugas</th>
                                            <th class="text-center">Total Validasi</th>
                                            <th class="text-center">Disetujui</th>
                                            <th class="text-center">Ditolak</th>
                                            <th class="text-center">% Approval</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($a = mysqli_fetch_assoc($aktivitas_petugas)):
                                            $pct_approve = $a['total'] > 0 ? round(($a['disetujui'] / $a['total']) * 100, 1) : 0;
                                        ?>
                                            <tr>
                                                <td><?= $a['nama_lengkap'] ?></td>
                                                <td class="text-center"><strong><?= $a['total'] ?></strong></td>
                                                <td class="text-center"><?= $a['disetujui'] ?></td>
                                                <td class="text-center"><?= $a['ditolak'] ?></td>
                                                <td class="text-center">
                                                    <div class="progress" style="height:20px;">
                                                        <div class="progress-bar bg-success" style="width:<?= $pct_approve ?>%"><?= $pct_approve ?>%</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="alert alert-warning"><i class="fas fa-info-circle"></i> Tidak ada aktivitas petugas pada periode ini.</div>
                            <?php endif; ?>

                            <h6 class="mt-4 mb-3"><i class="fas fa-chart-pie"></i> Persentase Status Pengajuan</h6>
                            <?php
                            $pct = [
                                'pending' => $stats['pengajuan'] > 0 ? round(($stats['pending'] / $stats['pengajuan']) * 100, 1) : 0,
                                'disetujui' => $stats['pengajuan'] > 0 ? round(($stats['disetujui'] / $stats['pengajuan']) * 100, 1) : 0,
                                'ditolak' => $stats['pengajuan'] > 0 ? round(($stats['ditolak'] / $stats['pengajuan']) * 100, 1) : 0
                            ];
                            ?>
                            <div class="row">
                                <?php foreach ([['Pending', 'warning', $pct['pending']], ['Disetujui', 'success', $pct['disetujui']], ['Ditolak', 'danger', $pct['ditolak']]] as $p): ?>
                                    <div class="col-md-4">
                                        <div class="card text-center bg-<?= $p[1] ?> text-white">
                                            <div class="card-body">
                                                <h2><?= $p[2] ?>%</h2>
                                                <p class="mb-0"><?= $p[0] ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <h6 class="mt-4 mb-3"><i class="fas fa-history"></i> Pengajuan Terbaru (5 Terakhir)</h6>
                            <table class="table table-bordered table-sm">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>No. Pengajuan</th>
                                        <th>Nama Warga</th>
                                        <th>Jenis Dokumen</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $recent = mysqli_query($conn, "SELECT p.nomor_pengajuan, u.nama_lengkap, j.nama_dokumen, p.tanggal_pengajuan, s.nama_status, s.warna_badge FROM t_pengajuan p JOIN t_pengguna u ON p.id_pengguna = u.id_pengguna JOIN t_jenis_dokumen j ON p.id_jenis = j.id_jenis JOIN t_status_pengajuan s ON p.id_status = s.id_status ORDER BY p.tanggal_pengajuan DESC LIMIT 5");
                                    while ($r = mysqli_fetch_assoc($recent)):
                                    ?>
                                        <tr>
                                            <td><?= $r['nomor_pengajuan'] ?></td>
                                            <td><?= $r['nama_lengkap'] ?></td>
                                            <td><?= $r['nama_dokumen'] ?></td>
                                            <td><?= format_tanggal($r['tanggal_pengajuan']) ?></td>
                                            <td><?= get_status_badge($r['nama_status'], $r['warna_badge']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODALS -->
    <div class="modal fade" id="addPetugasModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Petugas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label>Username *</label><input type="text" name="username" class="form-control" required></div>
                        <div class="mb-3"><label>Password *</label><input type="password" name="password" class="form-control" required></div>
                        <div class="mb-3"><label>Nama Lengkap *</label><input type="text" name="nama_lengkap" class="form-control" required></div>
                        <div class="mb-3"><label>NIK *</label><input type="text" name="nik" class="form-control" pattern="[0-9]{16}" required></div>
                        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control"></div>
                        <div class="mb-3"><label>No. HP</label><input type="tel" name="no_hp" class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="submit" name="add_petugas" class="btn btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editPetugasModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5>Edit Petugas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_pengguna" id="edit_id_pengguna">
                        <div class="mb-3"><label>Username</label><input type="text" name="username" id="edit_username" class="form-control" required></div>
                        <div class="mb-3"><label>Nama Lengkap</label><input type="text" name="nama_lengkap" id="edit_nama_lengkap" class="form-control" required></div>
                        <div class="mb-3"><label>NIK</label><input type="text" name="nik" id="edit_nik" class="form-control" required></div>
                        <div class="mb-3"><label>Email</label><input type="email" name="email" id="edit_email" class="form-control"></div>
                        <div class="mb-3"><label>No. HP</label><input type="text" name="no_hp" id="edit_no_hp" class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="submit" name="edit_petugas" class="btn btn-primary">Update</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resetPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5>Reset Password</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_pengguna" id="reset_id_pengguna">
                        <p>Reset password untuk: <strong id="reset_nama"></strong></p>
                        <div class="mb-3"><label>Password Baru</label><input type="password" name="new_password" class="form-control" required></div>
                    </div>
                    <div class="modal-footer"><button type="submit" name="reset_password" class="btn btn-warning">Reset</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL ADD/EDIT JENIS DOKUMEN -->
    <div class="modal fade" id="jenisModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" id="formJenis">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="jenisModalTitle">Tambah Jenis Dokumen</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_jenis" id="jenis_id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nama Dokumen *</label>
                                <input type="text" name="nama_dokumen" id="jenis_nama" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Deskripsi</label>
                                <textarea name="deskripsi" id="jenis_deskripsi" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <!-- KONFIGURASI FILE UPLOAD -->
                        <div class="card mb-3 border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-file-upload"></i> Upload Dokumen</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Jumlah File yang Harus Diupload *</label>
                                        <select name="jumlah_upload" id="jumlah_upload" class="form-select" onchange="updateUploadFields()" required>
                                            <option value="1">1 File</option>
                                            <option value="2">2 File</option>
                                            <option value="3">3 File</option>
                                            <option value="4">4 File</option>
                                            <option value="5">5 File</option>
                                        </select>
                                        <small class="text-muted">Warga akan diminta mengupload sejumlah file sesuai konfigurasi ini</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Format File</label>
                                        <input type="text" class="form-control" value="PDF (Maksimal 5MB per file)" readonly>
                                    </div>
                                </div>

                                <div id="uploadFieldsContainer" class="mt-3">
                                    <!-- Label untuk setiap field upload akan ditambahkan di sini -->
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="fas fa-sliders-h"></i> Field Input Form</h6>
                            <button type="button" class="btn btn-sm btn-success" onclick="addFieldRow()">
                                <i class="fas fa-plus"></i> Tambah Field
                            </button>
                        </div>

                        <div class="alert alert-warning alert-sm">
                            <small><i class="fas fa-info-circle"></i> Field yang ditambahkan akan muncul di form pengajuan warga</small>
                        </div>

                        <div id="fieldContainer" class="field-container" style="max-height: 400px; overflow-y: auto;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_jenis" id="btnSubmitJenis" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Load external JavaScript files -->
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        <?php if (isset($_GET['logout'])): ?>
        confirmLogout('Anda yakin ingin keluar?').then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../includes/logout.php';
            } else {
                // Jika dibatalkan, hapus parameter ?logout dari URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    <?php endif; ?>

        // Flash message configuration
        window.flashMessage = {
            message: <?= json_encode($flash_message ?? '') ?>,
            type: <?= json_encode($flash_type ?? '') ?>
        };

        // Chart data configuration
        window.adminChartData = {
            stats: {
                pending: <?= $stats['pending'] ?>,
                disetujui: <?= $stats['disetujui'] ?>,
                ditolak: <?= $stats['ditolak'] ?>
            },
            labels: <?= json_encode($chart_labels) ?>,
            data: <?= json_encode($chart_data) ?>
        };
    </script>


</body>

</html>