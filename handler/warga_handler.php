<?php

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Cek login dan role
require_login();
check_role(['warga']);

$user_id = $_SESSION['user_id'];
$user_data = get_user_data($user_id);

// Get statistik
$query_total = "SELECT COUNT(*) as total FROM t_pengajuan WHERE id_pengguna = '$user_id'";
$result_total = mysqli_query($conn, $query_total);
$total_pengajuan = mysqli_fetch_assoc($result_total)['total'];

$query_pending = "SELECT COUNT(*) as total FROM t_pengajuan WHERE id_pengguna = '$user_id' AND id_status = 1";
$result_pending = mysqli_query($conn, $query_pending);
$total_pending = mysqli_fetch_assoc($result_pending)['total'];

$query_disetujui = "SELECT COUNT(*) as total FROM t_pengajuan WHERE id_pengguna = '$user_id' AND id_status = 2";
$result_disetujui = mysqli_query($conn, $query_disetujui);
$total_disetujui = mysqli_fetch_assoc($result_disetujui)['total'];

$query_ditolak = "SELECT COUNT(*) as total FROM t_pengajuan WHERE id_pengguna = '$user_id' AND id_status = 3";
$result_ditolak = mysqli_query($conn, $query_ditolak);
$total_ditolak = mysqli_fetch_assoc($result_ditolak)['total'];

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total records untuk user ini
$query_count = "SELECT COUNT(*) as total FROM t_pengajuan WHERE id_pengguna = '$user_id'";
$total_records = mysqli_fetch_assoc(mysqli_query($conn, $query_count))['total'];
$total_pages = ceil($total_records / $limit);

// Get daftar pengajuan dengan pagination
$query_pengajuan = "SELECT p.*, j.nama_dokumen, s.nama_status, s.warna_badge 
                    FROM t_pengajuan p
                    JOIN t_jenis_dokumen j ON p.id_jenis = j.id_jenis
                    JOIN t_status_pengajuan s ON p.id_status = s.id_status
                    WHERE p.id_pengguna = '$user_id'
                    ORDER BY p.tanggal_pengajuan DESC
                    LIMIT $limit OFFSET $offset";
$result_pengajuan = mysqli_query($conn, $query_pengajuan);
?>