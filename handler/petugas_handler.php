<?php

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Cek login dan role
require_login();
check_role(['petugas']);

$user_id = $_SESSION['user_id'];
$user_data = get_user_data($user_id);

// Handle update status
if (isset($_POST['update_status'])) {
    $id_pengajuan = clean_input($_POST['id_pengajuan']);
    $id_status = clean_input($_POST['id_status']);
    $catatan = clean_input($_POST['catatan']);

    $update_query = "UPDATE t_pengajuan SET 
                     id_status = '$id_status',
                     tanggal_validasi = NOW(),
                     validasi_oleh = '$user_id',
                     catatan_validasi = '$catatan'
                     WHERE id_pengajuan = '$id_pengajuan'";

    if (mysqli_query($conn, $update_query)) {
        // Insert riwayat
        $riwayat_query = "INSERT INTO t_riwayat_status (id_pengajuan, id_status, catatan, diubah_oleh)
                         VALUES ('$id_pengajuan', '$id_status', '$catatan', '$user_id')";
        mysqli_query($conn, $riwayat_query);

        // Jika disetujui, buat surat
        if ($id_status == 2) {
            $pengajuan_query = "SELECT p.*, j.nama_dokumen FROM t_pengajuan p
                               JOIN t_jenis_dokumen j ON p.id_jenis = j.id_jenis
                               WHERE p.id_pengajuan = '$id_pengajuan'";
            $pengajuan_result = mysqli_query($conn, $pengajuan_query);
            $pengajuan_data = mysqli_fetch_assoc($pengajuan_result);

            $nomor_surat = generate_nomor_surat($pengajuan_data['nama_dokumen']);
            $file_path = 'surat_' . $id_pengajuan . '_' . time() . '.pdf';

            $surat_query = "INSERT INTO t_surat_terbit (id_pengajuan, nomor_surat, file_path, diterbitkan_oleh)
                           VALUES ('$id_pengajuan', '$nomor_surat', '$file_path', '$user_id')";
            mysqli_query($conn, $surat_query);
        }
        
        $_SESSION['success_message'] = 'Status pengajuan berhasil diupdate!';
        header('Location: petugas.php');
        exit();
    } else {
        $_SESSION['error_message'] = 'Gagal mengupdate status pengajuan.';
        header('Location: petugas.php');
        exit();
    }
}

// Get statistik
$query_total = "SELECT COUNT(*) as total FROM t_pengajuan";
$total_pengajuan = mysqli_fetch_assoc(mysqli_query($conn, $query_total))['total'];

$query_pending = "SELECT COUNT(*) as total FROM t_pengajuan WHERE id_status = 1";
$total_pending = mysqli_fetch_assoc(mysqli_query($conn, $query_pending))['total'];

$query_disetujui = "SELECT COUNT(*) as total FROM t_pengajuan WHERE id_status = 2";
$total_disetujui = mysqli_fetch_assoc(mysqli_query($conn, $query_disetujui))['total'];

$query_ditolak = "SELECT COUNT(*) as total FROM t_pengajuan WHERE id_status = 3";
$total_ditolak = mysqli_fetch_assoc(mysqli_query($conn, $query_ditolak))['total'];

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total records
$query_count = "SELECT COUNT(*) as total FROM t_pengajuan";
$total_records = mysqli_fetch_assoc(mysqli_query($conn, $query_count))['total'];
$total_pages = ceil($total_records / $limit);

// Get daftar pengajuan dengan pagination
$query_pengajuan = "SELECT p.*, j.nama_dokumen, s.nama_status, s.warna_badge, u.nama_lengkap as nama_warga
                    FROM t_pengajuan p
                    JOIN t_jenis_dokumen j ON p.id_jenis = j.id_jenis
                    JOIN t_status_pengajuan s ON p.id_status = s.id_status
                    JOIN t_pengguna u ON p.id_pengguna = u.id_pengguna
                    ORDER BY p.tanggal_pengajuan DESC
                    LIMIT $limit OFFSET $offset";
$result_pengajuan = mysqli_query($conn, $query_pengajuan);
?>