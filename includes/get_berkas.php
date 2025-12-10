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
    
    if (!$check_result || mysqli_num_rows($check_result) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Pengajuan tidak ditemukan']);
        exit;
    }
    
    $check_data = mysqli_fetch_assoc($check_result);
    
    if ($check_data['id_pengguna'] != $_SESSION['user_id']) {
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
        exit;
    }
}

// Query SEMUA berkas
$query = "SELECT * FROM t_berkas_dokumen WHERE id_pengajuan = '$id_pengajuan' ORDER BY id_berkas ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Query error: ' . mysqli_error($conn)
    ]);
    exit;
}

$berkas_list = [];

// Loop semua hasil
while ($row = mysqli_fetch_assoc($result)) {
    // Parse label dari nama_file
    if (strpos($row['nama_file'], '|||') !== false) {
        $parts = explode('|||', $row['nama_file'], 2);
        $row['label'] = trim($parts[0]);
        $row['filename_only'] = trim($parts[1]);
    } else {
        $row['label'] = 'Dokumen Pendukung';
        $row['filename_only'] = $row['nama_file'];
    }
    
    $berkas_list[] = $row;
}

if (count($berkas_list) > 0) {
    echo json_encode([
        'status' => 'success',
        'data' => $berkas_list, // Array of all files
        'count' => count($berkas_list)
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Berkas tidak ditemukan'
    ]);
}
?>