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
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="brand">
                <i class="fas fa-file-alt"></i> DOKUMEN WARGA
            </div>
            <ul class="nav flex-column mt-3">
                <li class="nav-item">
                    <a class="nav-link active" href="../dashboard/warga.php">
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

        <div class="main-content">
            <div class="top-navbar d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Form Pengajuan Dokumen</h5>
                <div>
                    <span class="me-3"><i class="fas fa-user"></i> <?= $user_data['nama_lengkap'] ?></span>
                    <span class="badge bg-success">Warga</span>
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
                                                <option value="<?= $jenis['id_jenis'] ?>"
                                                    data-nama="<?= $jenis['nama_dokumen'] ?>"
                                                    data-deskripsi="<?= htmlspecialchars($jenis['deskripsi']) ?>">
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

                                    <!-- Dynamic Upload Fields Container -->
                                    <div id="uploadFieldsContainer"></div>

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
        let uploadConfig = null;

        // Handle perubahan jenis dokumen - DYNAMIC FIELDS & UPLOADS
        document.getElementById('jenis_dokumen').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const deskripsiGabungan = selectedOption.getAttribute('data-deskripsi');
            const dynamicFieldsContainer = document.getElementById('dynamicFields');
            const uploadFieldsContainer = document.getElementById('uploadFieldsContainer');

            dynamicFieldsContainer.innerHTML = '';
            uploadFieldsContainer.innerHTML = '';

            if (deskripsiGabungan) {
                const decoded = decodeConfig(deskripsiGabungan);

                // Render form fields
                if (decoded.field_config && decoded.field_config.length > 0) {
                    let html = '<div class="alert alert-info"><strong><i class="fas fa-edit"></i> Field Tambahan</strong></div>';

                    decoded.field_config.forEach(field => {
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

                // Render upload fields
                if (decoded.upload_config) {
                    uploadConfig = decoded.upload_config;
                    renderUploadFields(decoded.upload_config);
                }
            }
        });

        // Render upload fields berdasarkan config
        function renderUploadFields(config) {
            const container = document.getElementById('uploadFieldsContainer');
            const jumlah = config.jumlah || 1;
            const labels = config.labels || ['Dokumen Pendukung'];

            let html = '<div class="card border-primary mb-3">';
            html += '<div class="card-header bg-primary text-white">';
            html += '<h6 class="mb-0"><i class="fas fa-file-upload"></i> Upload Dokumen Pendukung (PDF Only)</h6>';
            html += '</div>';
            html += '<div class="card-body">';
            html += '<div class="alert alert-warning">';
            html += '<i class="fas fa-info-circle"></i> <strong>Wajib upload ' + jumlah + ' file PDF</strong> (Maksimal 5MB per file)';
            html += '</div>';

            for (let i = 0; i < jumlah; i++) {
                const label = labels[i] || 'Dokumen ' + (i + 1);
                html += '<div class="mb-3">';
                html += '<label class="form-label fw-bold">' + label + ' <span class="text-danger">*</span></label>';
                html += '<input type="file" name="berkas[]" class="form-control file-upload" ';
                html += 'accept="application/pdf" required data-index="' + i + '" data-label="' + label + '">';
                html += '<small class="text-muted">Format: PDF | Max: 5MB</small>';
                html += '<div class="file-info-' + i + ' mt-2"></div>';
                html += '</div>';
            }

            html += '</div></div>';

            container.innerHTML = html;

            // Attach file validation
            document.querySelectorAll('.file-upload').forEach(input => {
                input.addEventListener('change', validateFile);
            });
        }

        // Validasi file upload
        function validateFile(e) {
            const file = e.target.files[0];
            const index = e.target.getAttribute('data-index');
            const label = e.target.getAttribute('data-label');
            const infoDiv = document.querySelector('.file-info-' + index);

            if (file) {
                const maxSize = 5 * 1024 * 1024; // 5MB
                const fileExtension = file.name.split('.').pop().toLowerCase();

                // Reset info
                infoDiv.innerHTML = '';

                // Validasi PDF only
                if (fileExtension !== 'pdf' && file.type !== 'application/pdf') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Format File Tidak Valid!',
                        html: `<p><strong>${label}</strong>: Hanya file PDF yang diperbolehkan!</p>
                               <p>File Anda: <strong>${fileExtension.toUpperCase()}</strong></p>`,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                    e.target.value = '';
                    return false;
                }

                // Validasi ukuran
                if (file.size > maxSize) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Terlalu Besar!',
                        html: `<p><strong>${label}</strong></p>
                               <p>Ukuran: <strong>${(file.size / (1024 * 1024)).toFixed(2)} MB</strong></p>
                               <p>Maksimal: <strong>5 MB</strong></p>`,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                    e.target.value = '';
                    return false;
                }

                // Toast sukses pojok kanan atas
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

                // Info di bawah input
                infoDiv.innerHTML = `
                    <div class="alert alert-success alert-sm mb-0">
                        <i class="fas fa-check-circle"></i> 
                        <strong>${file.name}</strong> 
                        (${(file.size / (1024 * 1024)).toFixed(2)} MB)
                    </div>
                `;
            }
        }

        // Decode config dari string
        function decodeConfig(deskripsiGabungan) {
            if (deskripsiGabungan.includes('###JSON_CONFIG###')) {
                const parts = deskripsiGabungan.split('###JSON_CONFIG###');
                if (parts[1]) {
                    try {
                        const config = JSON.parse(parts[1]);
                        // Handle format lama (langsung array) atau format baru (object)
                        if (Array.isArray(config)) {
                            return {
                                field_config: config,
                                upload_config: {
                                    jumlah: 1,
                                    labels: ['Dokumen Pendukung']
                                }
                            };
                        }
                        return config;
                    } catch (e) {
                        console.error('Error parsing config:', e);
                    }
                }
            }
            return {
                field_config: [],
                upload_config: {
                    jumlah: 1,
                    labels: ['Dokumen Pendukung']
                }
            };
        }

        // HANDLE FORM SUBMISSION
        document.getElementById('formPengajuan').addEventListener('submit', function(e) {
            e.preventDefault();

            console.log("Form submission started");

            const fileInputs = document.querySelectorAll('.file-upload');
            let allFilesValid = true;

            // Validasi semua file
            fileInputs.forEach(input => {
                const file = input.files[0];
                const label = input.getAttribute('data-label');

                if (!file) {
                    allFilesValid = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'File Belum Lengkap!',
                        html: `<p>File <strong>${label}</strong> belum diupload!</p>`,
                        confirmButtonColor: '#d33'
                    });
                    return false;
                }

                const maxSize = 5 * 1024 * 1024;
                const fileExtension = file.name.split('.').pop().toLowerCase();

                if (fileExtension !== 'pdf' && file.type !== 'application/pdf') {
                    allFilesValid = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Format File Tidak Valid!',
                        html: `<p><strong>${label}</strong>: Hanya PDF yang diperbolehkan!</p>`,
                        confirmButtonColor: '#d33'
                    });
                    return false;
                }

                if (file.size > maxSize) {
                    allFilesValid = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'File Terlalu Besar!',
                        html: `<p><strong>${label}</strong> melebihi 5MB!</p>`,
                        confirmButtonColor: '#d33'
                    });
                    return false;
                }
            });

            if (!allFilesValid) return false;

            // === Buat FormData dengan benar ===
            const formData = new FormData();

            // Tambahkan field biasa
            const formElements = this.elements;
            for (let i = 0; i < formElements.length; i++) {
                const element = formElements[i];

                // Skip file inputs dan buttons
                if (element.type === 'file' || element.type === 'submit' || element.type === 'button') {
                    continue;
                }

                // Tambahkan field dengan value-nya
                if (element.name && element.value) {
                    formData.append(element.name, element.value);
                    console.log(`Added field: ${element.name} = ${element.value}`);
                }
            }

            const filesArray = Array.from(fileInputs);
            console.log(`Total files to upload: ${filesArray.length}`);

            if (filesArray.length > 1) {
                // Multiple files - gunakan array notation
                filesArray.forEach((input, index) => {
                    const file = input.files[0];
                    if (file) {
                        formData.append('berkas[]', file);
                        console.log(`Added file ${index}: ${file.name}, size: ${file.size}`);
                    }
                });
            } else if (filesArray.length === 1) {
                // Single file
                const file = filesArray[0].files[0];
                if (file) {
                    formData.append('berkas', file);
                    console.log(`Added single file: ${file.name}, size: ${file.size}`);
                }
            }

            // Log semua FormData entries
            console.log("=== FormData Contents ===");
            for (let pair of formData.entries()) {
                if (pair[1] instanceof File) {
                    console.log(pair[0] + ': [File] ' + pair[1].name);
                } else {
                    console.log(pair[0] + ': ' + pair[1]);
                }
            }

            // Submit form
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
                .then(response => {
                    console.log("Response status:", response.status);
                    console.log("Response headers:", response.headers);

                    // Check if response is actually JSON
                    const contentType = response.headers.get("content-type");
                    if (!contentType || !contentType.includes("application/json")) {
                        // Response bukan JSON, ambil sebagai text untuk debugging
                        return response.text().then(text => {
                            console.error("Response is not JSON:", text);
                            throw new Error("Server mengembalikan response yang tidak valid. Response: " + text.substring(0, 200));
                        });
                    }

                    return response.json();
                })
                .then(data => {
                    console.log("Response data:", data);

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
                            html: data.message || 'Terjadi kesalahan sistem'
                        });
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: `<p>Terjadi kesalahan sistem</p><p><small>${error.message}</small></p>`,
                        footer: '<small>Silakan cek console browser (F12) untuk detail error</small>'
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