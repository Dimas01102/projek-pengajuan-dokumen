<?php
// Konfigurasi Database dan Session
// Mulai session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_pengajuan_dokumen');

// Koneksi ke database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset UTF-8
mysqli_set_charset($conn, "utf8");

// Base URL 
define('BASE_URL', 'http://localhost/project_pengajuan_dokumen/');

// Path untuk upload
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('SURAT_PATH', __DIR__ . '/../surat/');

// Fungsi untuk redirect
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

// Fungsi untuk membersihkan input
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Fungsi untuk cek login
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Fungsi untuk cek role
function check_role($allowed_roles) {
    if (!is_logged_in()) {
        redirect('login.php');
    }
    
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        redirect('index.php');
    }
}

// Fungsi untuk get user data
function get_user_data($user_id) {
    global $conn;
    $query = "SELECT * FROM t_pengguna WHERE id_pengguna = '$user_id'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}
?>