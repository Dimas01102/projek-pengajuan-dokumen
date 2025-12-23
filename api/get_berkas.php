<?php
/* ========================================
   API untuk mengambil data berkas pengajuan
   FILE: api/get_berkas.php
========================7================= */

// Session 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load config untuk dapat path yang benar
require_once __DIR__ . '/../includes/config.php';

// Matikan error display untuk production
ini_set('display_errors', 0);
error_reporting(0);

// Clean output buffer jika ada
if (ob_get_level()) ob_end_clean();

// Set header JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Function untuk send JSON response (aman & konsisten)
function sendJSON($status, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    
    $response = [
        'status' => $status,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
        $response['count'] = is_array($data) ? count($data) : 1;
    }
    
    // Clean buffer sebelum output
    while (ob_get_level()) ob_end_clean();
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Validasi & proses request
try {
    // Cek apakah user sudah login
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        sendJSON('error', 'Sesi berakhir. Silakan login kembali.', null, 401);
    }

    $user_id = (int)$_SESSION['user_id'];
    $user_role = $_SESSION['role'];

    // Cek apakah ada parameter id_pengajuan?
    if (!isset($_GET['id_pengajuan']) || empty(trim($_GET['id_pengajuan']))) {
        sendJSON('error', 'Parameter id_pengajuan tidak ditemukan', null, 400);
    }

    // Connect ke database
    if (!$conn) {
        sendJSON('error', 'Koneksi database gagal', null, 500);
    }

    // Clean input
    $id_pengajuan = mysqli_real_escape_string($conn, trim($_GET['id_pengajuan']));

    // Validasi akses (jika user adalah warga, cek ownership)
    if ($user_role === 'warga') {
        $check_query = "SELECT id_pengguna FROM t_pengajuan WHERE id_pengajuan = '$id_pengajuan'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (!$check_result) {
            mysqli_close($conn);
            sendJSON('error', 'Query error: ' . mysqli_error($conn), null, 500);
        }
        
        if (mysqli_num_rows($check_result) == 0) {
            mysqli_close($conn);
            sendJSON('error', 'Pengajuan tidak ditemukan', null, 404);
        }
        
        $check_data = mysqli_fetch_assoc($check_result);
        
        // Pastikan pengajuan milik user yang login
        if ((int)$check_data['id_pengguna'] !== $user_id) {
            mysqli_close($conn);
            sendJSON('error', 'Akses ditolak. Anda tidak memiliki izin untuk melihat pengajuan ini.', null, 403);
        }
    }

    // Query berkas
    $query = "SELECT 
                id_berkas,
                id_pengajuan,
                nama_file,
                path_file,
                tipe_file,
                ukuran_file,
                tanggal_upload
              FROM t_berkas_dokumen 
              WHERE id_pengajuan = '$id_pengajuan' 
              ORDER BY id_berkas ASC";
    
    $result = mysqli_query($conn, $query);

    if (!$result) {
        $error = mysqli_error($conn);
        mysqli_close($conn);
        sendJSON('error', 'Query gagal: ' . $error, null, 500);
    }

    // Parse hasil dengan path yang benar
    $berkas_list = [];

    while ($row = mysqli_fetch_assoc($result)) {
        // Parse label dari format: "Label ||| filename.pdf"
        if (strpos($row['nama_file'], '|||') !== false) {
            $parts = explode('|||', $row['nama_file'], 2);
            $row['label'] = trim($parts[0]);
            $row['filename_only'] = trim($parts[1]);
        } else {
            $row['label'] = 'Dokumen Pendukung';
            $row['filename_only'] = $row['nama_file'];
        }
        
        // Gunakan path yang benar dari UPLOAD_PATH
        $row['file_url'] = BASE_URL . 'uploads/' . $row['path_file'];
        $row['ukuran_file'] = (int)$row['ukuran_file'];
        
        $berkas_list[] = $row;
    }

    mysqli_close($conn);

    // Return response
    if (count($berkas_list) > 0) {
        sendJSON('success', 'Berkas ditemukan', $berkas_list, 200);
    } else {
        sendJSON('error', 'Tidak ada berkas yang diupload untuk pengajuan ini', null, 404);
    }

} catch (Exception $e) {
    error_log('get_berkas.php error: ' . $e->getMessage());
    sendJSON('error', 'Terjadi kesalahan server', null, 500);
    
} catch (Error $e) {
    error_log('get_berkas.php fatal error: ' . $e->getMessage());
    sendJSON('error', 'Terjadi kesalahan fatal', null, 500);
}
?>