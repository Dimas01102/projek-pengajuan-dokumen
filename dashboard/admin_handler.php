<?php
require_login();
check_role(['admin']);

$user_id = $_SESSION['user_id'];
$user_data = get_user_data($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = '';
    $type = 'success';
    $section = '';

    // ============ PETUGAS HANDLERS ============
    
    if (isset($_POST['add_petugas'])) {
        $section = 'petugas';
        $username = clean_input($_POST['username']);
        $nik = clean_input($_POST['nik']);
        $email = clean_input($_POST['email']);

        if (mysqli_num_rows(mysqli_query($conn, "SELECT id_pengguna FROM t_pengguna WHERE username = '$username'")) > 0) {
            $type = 'error';
            $message = 'Username sudah digunakan';
        } elseif (mysqli_num_rows(mysqli_query($conn, "SELECT id_pengguna FROM t_pengguna WHERE nik = '$nik'")) > 0) {
            $type = 'error';
            $message = 'NIK sudah terdaftar';
        } elseif (!empty($email) && mysqli_num_rows(mysqli_query($conn, "SELECT id_pengguna FROM t_pengguna WHERE email = '$email'")) > 0) {
            $type = 'error';
            $message = 'Email sudah terdaftar';
        } else {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $nama_lengkap = clean_input($_POST['nama_lengkap']);
            $no_hp = clean_input($_POST['no_hp']);
            $query = "INSERT INTO t_pengguna (username, password, nama_lengkap, nik, email, no_hp, role, status) 
                      VALUES ('$username', '$password', '$nama_lengkap', '$nik', '$email', '$no_hp', 'petugas', 'aktif')";
            if (mysqli_query($conn, $query)) {
                $message = 'Petugas berhasil ditambahkan';
            } else {
                $type = 'error';
                $message = 'Terjadi kesalahan sistem';
            }
        }
    }

    if (isset($_POST['edit_petugas'])) {
        $section = 'petugas';
        $id = clean_input($_POST['id_pengguna']);
        $username = clean_input($_POST['username']);
        $nik = clean_input($_POST['nik']);
        $email = clean_input($_POST['email']);

        if (mysqli_num_rows(mysqli_query($conn, "SELECT id_pengguna FROM t_pengguna WHERE username = '$username' AND id_pengguna != '$id'")) > 0) {
            $type = 'error';
            $message = 'Username sudah digunakan';
        } elseif (mysqli_num_rows(mysqli_query($conn, "SELECT id_pengguna FROM t_pengguna WHERE nik = '$nik' AND id_pengguna != '$id'")) > 0) {
            $type = 'error';
            $message = 'NIK sudah terdaftar';
        } elseif (!empty($email) && mysqli_num_rows(mysqli_query($conn, "SELECT id_pengguna FROM t_pengguna WHERE email = '$email' AND id_pengguna != '$id'")) > 0) {
            $type = 'error';
            $message = 'Email sudah terdaftar';
        } else {
            $nama_lengkap = clean_input($_POST['nama_lengkap']);
            $no_hp = clean_input($_POST['no_hp']);
            $query = "UPDATE t_pengguna SET username = '$username', nama_lengkap = '$nama_lengkap', 
                      nik = '$nik', email = '$email', no_hp = '$no_hp' WHERE id_pengguna = '$id' AND role = 'petugas'";
            if (mysqli_query($conn, $query)) {
                $message = 'Data petugas berhasil diupdate';
            } else {
                $type = 'error';
                $message = 'Terjadi kesalahan sistem';
            }
        }
    }

    if (isset($_POST['toggle_petugas_status'])) {
        $section = 'petugas';
        $id = clean_input($_POST['id_pengguna']);
        $current_status = clean_input($_POST['current_status']);
        $new_status = ($current_status == 'aktif') ? 'nonaktif' : 'aktif';

        if (mysqli_query($conn, "UPDATE t_pengguna SET status = '$new_status' WHERE id_pengguna = '$id' AND role = 'petugas'")) {
            $message = $new_status == 'aktif' ? 'Petugas berhasil diaktifkan' : 'Petugas berhasil dinonaktifkan';
        } else {
            $type = 'error';
            $message = 'Gagal mengubah status petugas';
        }
    }

    if (isset($_POST['reset_password'])) {
        $section = 'petugas';
        $id = clean_input($_POST['id_pengguna']);
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        if (mysqli_query($conn, "UPDATE t_pengguna SET password = '$new_password' WHERE id_pengguna = '$id'")) {
            $message = 'Password berhasil direset';
        } else {
            $type = 'error';
            $message = 'Gagal reset password';
        }
    }

    if (isset($_POST['delete_petugas'])) {
        $section = 'petugas';
        $id = clean_input($_POST['id_pengguna']);
        $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM t_pengajuan WHERE validasi_oleh = '$id'"))['total'];
        
        if ($check > 0) {
            $type = 'error';
            $message = 'Tidak bisa hapus! Petugas ini sudah pernah memvalidasi pengajuan.';
        } else {
            if (mysqli_query($conn, "DELETE FROM t_pengguna WHERE id_pengguna = '$id' AND role = 'petugas'")) {
                $message = 'Petugas berhasil dihapus';
            } else {
                $type = 'error';
                $message = 'Gagal menghapus petugas';
            }
        }
    }

    // ============ WARGA HANDLERS ============

    if (isset($_POST['delete_warga'])) {
        $section = 'warga';
        $id = clean_input($_POST['id_pengguna']);
        
        // Nonaktifkan foreign key checks sementara
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0");
        
        // Hapus data terkait terlebih dahulu
        // 1. Hapus berkas dokumen terkait pengajuan warga
        $berkas_query = mysqli_query($conn, "SELECT b.path_file FROM t_berkas_dokumen b 
                                            JOIN t_pengajuan p ON b.id_pengajuan = p.id_pengajuan 
                                            WHERE p.id_pengguna = '$id'");
        while ($berkas = mysqli_fetch_assoc($berkas_query)) {
            // Hapus file fisik jika ada
            $file_path = UPLOAD_PATH . $berkas['path_file'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // 2. Hapus berkas dokumen dari database
        mysqli_query($conn, "DELETE FROM t_berkas_dokumen WHERE id_pengajuan IN 
                            (SELECT id_pengajuan FROM t_pengajuan WHERE id_pengguna = '$id')");
        
        // 3. Hapus pengajuan warga
        mysqli_query($conn, "DELETE FROM t_pengajuan WHERE id_pengguna = '$id'");
        
        // 4. Hapus data warga
        if (mysqli_query($conn, "DELETE FROM t_pengguna WHERE id_pengguna = '$id' AND role = 'warga'")) {
            $message = 'Warga dan semua data terkait berhasil dihapus';
        } else {
            $type = 'error';
            $message = 'Gagal menghapus warga: ' . mysqli_error($conn);
        }
        
        // Aktifkan kembali foreign key checks
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1");
    }

    // ============ JENIS DOKUMEN HANDLERS ============

    if (isset($_POST['add_jenis'])) {
        $section = 'jenis-dokumen';
        $nama_dokumen = clean_input($_POST['nama_dokumen']);
        $deskripsi_text = clean_input($_POST['deskripsi']);

        $field_config = array();
        if (isset($_POST['field_labels']) && is_array($_POST['field_labels'])) {
            foreach ($_POST['field_labels'] as $index => $label) {
                if (!empty($label)) {
                    $field_config[] = array(
                        'label' => clean_input($label),
                        'type' => clean_input($_POST['field_types'][$index]),
                        'required' => isset($_POST['field_required'][$index]) ? 1 : 0,
                        'placeholder' => clean_input($_POST['field_placeholders'][$index] ?? ''),
                        'options' => isset($_POST['field_options'][$index]) && !empty($_POST['field_options'][$index]) ?
                            array_map('trim', explode(',', clean_input($_POST['field_options'][$index]))) : array()
                    );
                }
            }
        }

        $deskripsi_gabungan = encode_deskripsi_with_config($deskripsi_text, $field_config);
        $deskripsi_escaped = mysqli_real_escape_string($conn, $deskripsi_gabungan);

        $query = "INSERT INTO t_jenis_dokumen (nama_dokumen, deskripsi, status) VALUES ('$nama_dokumen', '$deskripsi_escaped', 'aktif')";
        if (mysqli_query($conn, $query)) {
            $message = 'Jenis dokumen berhasil ditambahkan';
        } else {
            $type = 'error';
            $message = 'Gagal menambahkan jenis dokumen';
        }
    }

    if (isset($_POST['edit_jenis'])) {
        $section = 'jenis-dokumen';
        $id = clean_input($_POST['id_jenis']);
        $nama_dokumen = clean_input($_POST['nama_dokumen']);
        $deskripsi_text = clean_input($_POST['deskripsi']);

        $field_config = array();
        if (isset($_POST['field_labels']) && is_array($_POST['field_labels'])) {
            foreach ($_POST['field_labels'] as $index => $label) {
                if (!empty($label)) {
                    $field_config[] = array(
                        'label' => clean_input($label),
                        'type' => clean_input($_POST['field_types'][$index]),
                        'required' => isset($_POST['field_required'][$index]) ? 1 : 0,
                        'placeholder' => clean_input($_POST['field_placeholders'][$index] ?? ''),
                        'options' => isset($_POST['field_options'][$index]) && !empty($_POST['field_options'][$index]) ?
                            array_map('trim', explode(',', clean_input($_POST['field_options'][$index]))) : array()
                    );
                }
            }
        }

        $deskripsi_gabungan = encode_deskripsi_with_config($deskripsi_text, $field_config);
        $deskripsi_escaped = mysqli_real_escape_string($conn, $deskripsi_gabungan);

        $query = "UPDATE t_jenis_dokumen SET nama_dokumen = '$nama_dokumen', deskripsi = '$deskripsi_escaped' WHERE id_jenis = '$id'";
        if (mysqli_query($conn, $query)) {
            $message = 'Jenis dokumen berhasil diupdate';
        } else {
            $type = 'error';
            $message = 'Gagal mengupdate jenis dokumen';
        }
    }

    if (isset($_POST['toggle_jenis_status'])) {
        $section = 'jenis-dokumen';
        $id = clean_input($_POST['id_jenis']);
        $current_status = clean_input($_POST['status']);
        $new_status = ($current_status == 'aktif') ? 'nonaktif' : 'aktif';
        
        if (mysqli_query($conn, "UPDATE t_jenis_dokumen SET status = '$new_status' WHERE id_jenis = '$id'")) {
            $message = 'Status jenis dokumen berhasil diubah menjadi ' . $new_status;
        } else {
            $type = 'error';
            $message = 'Gagal mengubah status jenis dokumen';
        }
    }

    if (isset($_POST['delete_jenis'])) {
        $section = 'jenis-dokumen';
        $id = clean_input($_POST['id_jenis']);
        $status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM t_jenis_dokumen WHERE id_jenis = '$id'"))['status'];
        
        if ($status === 'aktif') {
            $type = 'error';
            $message = 'Tidak bisa hapus! Jenis dokumen harus dinonaktifkan terlebih dahulu.';
        } else {
            mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0");
            if (mysqli_query($conn, "DELETE FROM t_jenis_dokumen WHERE id_jenis = '$id'")) {
                $message = 'Jenis dokumen berhasil dihapus';
            } else {
                $type = 'error';
                $message = 'Gagal menghapus jenis dokumen';
            }
            mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1");
        }
    }

    // PRG Pattern: Redirect to prevent form resubmission
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_section'] = $section;
    header("Location: " . $_SERVER['PHP_SELF'] . "?section=" . $section);
    exit();
}

// Flash message display
$flash_message = isset($_SESSION['flash_message']) ? $_SESSION['flash_message'] : '';
$flash_type = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'success';
$flash_section = isset($_SESSION['flash_section']) ? $_SESSION['flash_section'] : '';
unset($_SESSION['flash_message'], $_SESSION['flash_type'], $_SESSION['flash_section']);

// Report filter
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');
$active_section = isset($_GET['section']) ? $_GET['section'] : 'statistik';

// Statistics data
$stats = [
    'warga' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM t_pengguna WHERE role = 'warga'"))['total'],
    'warga_aktif' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM t_pengguna WHERE role = 'warga' AND status = 'aktif'"))['total'],
    'petugas' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM t_pengguna WHERE role = 'petugas'"))['total'],
    'petugas_aktif' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM t_pengguna WHERE role = 'petugas' AND status = 'aktif'"))['total'],
    'pengajuan' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM t_pengajuan"))['total'],
    'pending' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM t_pengajuan WHERE id_status = 1"))['total'],
    'disetujui' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM t_pengajuan WHERE id_status = 2"))['total'],
    'ditolak' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM t_pengajuan WHERE id_status = 3"))['total']
];

// Chart data
$chart_jenis = mysqli_query($conn, "SELECT j.nama_dokumen, COUNT(p.id_pengajuan) as total FROM t_jenis_dokumen j LEFT JOIN t_pengajuan p ON j.id_jenis = p.id_jenis GROUP BY j.id_jenis");
$chart_labels = [];
$chart_data = [];
while ($cj = mysqli_fetch_assoc($chart_jenis)) {
    $chart_labels[] = $cj['nama_dokumen'];
    $chart_data[] = $cj['total'];
}

// ============ PAGINATION SETUP ============
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Pagination untuk Pengajuan
$total_pengajuan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM t_pengajuan"))['total'];
$total_pages_pengajuan = ceil($total_pengajuan / $limit);

// Pagination untuk Petugas
$total_petugas_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM t_pengguna WHERE role = 'petugas'"))['total'];
$total_pages_petugas = ceil($total_petugas_count / $limit);

// Pagination untuk Warga
$total_warga_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM t_pengguna WHERE role = 'warga'"))['total'];
$total_pages_warga = ceil($total_warga_count / $limit);

// Pagination untuk Jenis Dokumen
$total_jenis_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM t_jenis_dokumen"))['total'];
$total_pages_jenis = ceil($total_jenis_count / $limit);

// Query results dengan pagination
$result_petugas = mysqli_query($conn, "SELECT * FROM t_pengguna WHERE role = 'petugas' ORDER BY tanggal_daftar DESC LIMIT $limit OFFSET $offset");
$result_warga = mysqli_query($conn, "SELECT * FROM t_pengguna WHERE role = 'warga' ORDER BY tanggal_daftar DESC LIMIT $limit OFFSET $offset");
$result_jenis = mysqli_query($conn, "SELECT * FROM t_jenis_dokumen ORDER BY tanggal_dibuat DESC LIMIT $limit OFFSET $offset");
$result_pengajuan = mysqli_query($conn, "SELECT p.*, j.nama_dokumen, s.nama_status, s.warna_badge, u.nama_lengkap as nama_warga FROM t_pengajuan p JOIN t_jenis_dokumen j ON p.id_jenis = j.id_jenis JOIN t_status_pengajuan s ON p.id_status = s.id_status JOIN t_pengguna u ON p.id_pengguna = u.id_pengguna ORDER BY p.tanggal_pengajuan DESC LIMIT $limit OFFSET $offset");
$aktivitas_petugas = mysqli_query($conn, "SELECT u.nama_lengkap, COUNT(*) as total, SUM(CASE WHEN p.id_status = 2 THEN 1 ELSE 0 END) as disetujui, SUM(CASE WHEN p.id_status = 3 THEN 1 ELSE 0 END) as ditolak FROM t_pengajuan p JOIN t_pengguna u ON p.validasi_oleh = u.id_pengguna WHERE DATE(p.tanggal_validasi) BETWEEN '$tgl_mulai' AND '$tgl_akhir' GROUP BY u.id_pengguna");
?>