<?php
/* ========================================
   API untuk mengambil data berkas pengajuan
========================================= */
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json');

// Cek user harus login
if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Cek role: admin, petugas, atau warga (hanya bisa lihat milik sendiri)
$allowed_roles = ['admin', 'petugas'];
$user_role = $_SESSION['role'];

if (!isset($_GET['id_pengajuan'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID pengajuan tidak ditemukan']);
    exit;
}

$id_pengajuan = clean_input($_GET['id_pengajuan']);

// Jika warga, cek apakah pengajuan milik dia sendiri
if ($user_role == 'warga') {
    $check_query = "SELECT id_pengguna FROM t_pengajuan WHERE id_pengajuan = '$id_pengajuan'";
    $check_result = mysqli_query($conn, $check_query);
    $check_data = mysqli_fetch_assoc($check_result);
    
    if ($check_data['id_pengguna'] != $_SESSION['user_id']) {
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
        exit;
    }
}

// Query berkas
$query = "SELECT * FROM t_berkas_dokumen WHERE id_pengajuan = '$id_pengajuan' LIMIT 1";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $berkas = mysqli_fetch_assoc($result);
    echo json_encode([
        'status' => 'success',
        'data' => $berkas
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Berkas tidak ditemukan'
    ]);
}
?>