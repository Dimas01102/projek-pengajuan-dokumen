<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_login();
check_role(['warga']);

$user_id = $_SESSION['user_id'];
$user_data = get_user_data($user_id);

// Get jenis dokumen aktif
$query_jenis = "SELECT * FROM t_jenis_dokumen WHERE status = 'aktif' ORDER BY nama_dokumen";
$result_jenis = mysqli_query($conn, $query_jenis);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengajuan Dokumen</title>
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
                    <a class="nav-link active" href="form_pengajuan.php">
                        <i class="fas fa-plus-circle"></i> Ajukan Dokumen
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="lihat_status.php">
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
                <h5 class="mb-0">Form Pengajuan Dokumen</h5>
                <div>
                    <span class="me-3"><i class="fas fa-user"></i> <?= $user_data['nama_lengkap'] ?></span>
                </div>
            </div>

            <div class="content-area">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-file-alt"></i> Form Pengajuan Dokumen</h5>
                            </div>
                            <div class="card-body">
                                <form id="formPengajuan" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">Jenis Dokumen <span class="text-danger">*</span></label>
                                        <select name="id_jenis" id="jenis_dokumen" class="form-select" required>
                                            <option value="">-- Pilih Jenis Dokumen --</option>
                                            <?php while ($jenis = mysqli_fetch_assoc($result_jenis)): ?>
                                                <option value="<?= $jenis['id_jenis'] ?>" data-nama="<?= $jenis['nama_dokumen'] ?>" data-deskripsi="<?= htmlspecialchars($jenis['deskripsi']) ?>">
                                                    <?= $jenis['nama_dokumen'] ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <small class="text-muted">Pilih jenis dokumen yang akan diajukan</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Keperluan <span class="text-danger">*</span></label>
                                        <input type="text" name="keperluan" class="form-control" required placeholder="Contoh: Bantuan pendidikan">
                                    </div>

                                    <!-- Dynamic Fields Container -->
                                    <div id="dynamicFields"></div>

                                    <div class="mb-3">
                                        <label class="form-label">Upload Berkas Pendukung (PDF Only, Max 5MB)</label>
                                        <input type="file" name="berkas" id="berkas" class="form-control">
                                        <small class="text-muted">Upload KTP, KK, atau dokumen pendukung lainnya dalam format PDF</small>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-paper-plane"></i> Kirim Pengajuan
                                        </button>
                                        <a href="../dashboard/warga.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left"></i> Kembali
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle perubahan jenis dokumen - DYNAMIC FIELDS
        document.getElementById('jenis_dokumen').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const deskripsiGabungan = selectedOption.getAttribute('data-deskripsi');
            const dynamicFieldsContainer = document.getElementById('dynamicFields');

            dynamicFieldsContainer.innerHTML = '';

            if (deskripsiGabungan) {
                const fieldConfig = decodeFieldConfig(deskripsiGabungan);

                if (fieldConfig && fieldConfig.length > 0) {
                    let html = '<div class="alert alert-info"><strong>Field Tambahan</strong></div>';

                    fieldConfig.forEach(field => {
                        const fieldName = field.label.toLowerCase().replace(/\s+/g, '_').replace(/\./g, '');

                        html += '<div class="mb-3">';
                        html += '<label class="form-label">' + field.label;
                        if (field.required) html += ' <span class="text-danger">*</span>';
                        html += '</label>';

                        if (field.type === 'textarea') {
                            html += '<textarea name="' + fieldName + '" class="form-control" rows="3"';
                            if (field.required) html += ' required';
                            html += ' placeholder="' + (field.placeholder || '') + '"></textarea>';
                        } else if (field.type === 'select' || field.type === 'radio') {
                            if (field.type === 'select') {
                                html += '<select name="' + fieldName + '" class="form-select"';
                                if (field.required) html += ' required';
                                html += '><option value="">-- Pilih ' + field.label + ' --</option>';
                                if (field.options && field.options.length > 0) {
                                    field.options.forEach(opt => {
                                        html += '<option value="' + opt.trim() + '">' + opt.trim() + '</option>';
                                    });
                                }
                                html += '</select>';
                            } else {
                                if (field.options && field.options.length > 0) {
                                    field.options.forEach(opt => {
                                        html += '<div class="form-check">';
                                        html += '<input class="form-check-input" type="radio" name="' + fieldName + '" value="' + opt.trim() + '"';
                                        if (field.required) html += ' required';
                                        html += '>';
                                        html += '<label class="form-check-label">' + opt.trim() + '</label></div>';
                                    });
                                }
                            }
                        } else {
                            html += '<input type="' + field.type + '" name="' + fieldName + '" class="form-control"';
                            if (field.required) html += ' required';
                            if (field.placeholder) html += ' placeholder="' + field.placeholder + '"';
                            html += '>';
                        }

                        html += '</div>';
                    });

                    dynamicFieldsContainer.innerHTML = html;
                }
            }
        });

        // Decode field config dari string
        function decodeFieldConfig(deskripsiGabungan) {
            if (deskripsiGabungan.includes('###JSON_CONFIG###')) {
                const parts = deskripsiGabungan.split('###JSON_CONFIG###');
                if (parts[1]) {
                    try {
                        return JSON.parse(parts[1]);
                    } catch (e) {
                        console.error('Error parsing field config:', e);
                        return [];
                    }
                }
            }
            return [];
        }

        // VALIDASI FILE UPLOAD - HANYA PDF
        document.getElementById('berkas').addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                const maxSize = 5 * 1024 * 1024; // 5MB dalam bytes
                const fileExtension = file.name.split('.').pop().toLowerCase();

                // Validasi hanya PDF
                if (fileExtension !== 'pdf' && file.type !== 'application/pdf') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Format File Tidak Valid!',
                        html: `<p>Hanya file <strong>PDF</strong> yang diperbolehkan!</p>
                               <p>File yang Anda pilih: <strong>${file.name}</strong></p>
                               <p>Format: <strong>${fileExtension.toUpperCase()}</strong></p>`,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                    e.target.value = ''; // Reset input file
                    return false;
                }

                // Validasi ukuran file
                if (file.size > maxSize) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Terlalu Besar!',
                        html: `<p>Ukuran file: <strong>${(file.size / (1024 * 1024)).toFixed(2)} MB</strong></p>
                               <p>Maksimal ukuran file: <strong>5 MB</strong></p>
                               <p>Silakan pilih file yang lebih kecil.</p>`,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                    e.target.value = ''; // Reset input file
                    return false;
                }

                // Notifikasi sukses
                Swal.fire({
                    icon: 'success',
                    title: 'File Valid!',
                    html: `<p><strong>${file.name}</strong></p>
                           <p>Ukuran: ${(file.size / (1024 * 1024)).toFixed(2)} MB</p>`,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        });

        // HANDLE FORM SUBMISSION
        document.getElementById('formPengajuan').addEventListener('submit', function(e) {
            e.preventDefault();

            const fileInput = document.getElementById('berkas');
            const file = fileInput.files[0];

            // Validasi file jika ada file yang dipilih
            if (file) {
                const maxSize = 5 * 1024 * 1024; // 5MB
                const fileExtension = file.name.split('.').pop().toLowerCase();

                // Validasi hanya PDF
                if (fileExtension !== 'pdf' && file.type !== 'application/pdf') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Format File Tidak Valid!',
                        html: `<p>Hanya file <strong>PDF</strong> yang diperbolehkan!</p>
                               <p>File Anda: <strong>${fileExtension.toUpperCase()}</strong></p>`,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                    return false;
                }

                if (file.size > maxSize) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Terlalu Besar!',
                        html: `<p>Ukuran file: <strong>${(file.size / (1024 * 1024)).toFixed(2)} MB</strong></p>
                               <p>Maksimal: <strong>5 MB</strong></p>`,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                    return false;
                }
            }

            // Jika validasi lolos, lanjutkan submit
            const formData = new FormData(this);

            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('proses_pengajuan.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = 'lihat_status.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            html: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan sistem'
                    });
                });
        });

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