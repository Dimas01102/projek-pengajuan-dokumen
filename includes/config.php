<?php
/* ========================================
   KONFIGURASI DATABASE & SESSION
   Support .env file untuk Production
========================================= */

// ============================================
// LOAD .ENV FILE
// ============================================
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Remove quotes
        $value = trim($value, '"\'');
        
        // Set as environment variable
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
    
    return true;
}

// Load .env file (jika ada)
$envPath = __DIR__ . '/.env';
loadEnv($envPath);

// ============================================
// MODE CONFIGURATION
// ============================================
// Ambil dari .env, fallback ke hardcode
$appEnv = getenv('APP_ENV') ?: 'development';
$debugMode = filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOLEAN);

define('APP_ENV', $appEnv);
define('DEBUG_MODE', $debugMode);

// ============================================
// ERROR HANDLING
// ============================================
if (DEBUG_MODE) {
    // DEVELOPMENT MODE - Tampilkan semua error
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
    
    // Tampilkan error di browser untuk debugging
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        echo "<div style='background:#f8d7da;border:1px solid #f5c6cb;padding:10px;margin:10px;border-radius:5px;'>";
        echo "<strong>Error [$errno]:</strong> $errstr<br>";
        echo "<strong>File:</strong> $errfile<br>";
        echo "<strong>Line:</strong> $errline";
        echo "</div>";
        return true;
    });
    
} else {
    // PRODUCTION MODE - Sembunyikan error, log saja
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    ini_set('error_log', $logDir . '/error.log');
    error_reporting(E_ALL);
    
    // Exception handler untuk production
    set_exception_handler(function ($e) {
        error_log('Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        
        // Tampilkan halaman error generik
        if (file_exists(__DIR__ . '/500.php')) {
            require __DIR__ . '/500.php';
        } else {
            echo '<!DOCTYPE html>
            <html><head><title>Error</title></head>
            <body style="font-family:Arial;text-align:center;padding:50px;">
                <h1>Terjadi Kesalahan</h1>
                <p>Mohon maaf, terjadi kesalahan server. Silakan coba lagi nanti.</p>
            </body></html>';
        }
        exit;
    });
    
    // Fatal error handler untuk production
    register_shutdown_function(function () {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            error_log('Fatal Error: ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line']);
            http_response_code(500);
            
            if (file_exists(__DIR__ . '/500.php')) {
                require __DIR__ . '/500.php';
            } else {
                echo '<!DOCTYPE html>
                <html><head><title>Error</title></head>
                <body style="font-family:Arial;text-align:center;padding:50px;">
                    <h1>Terjadi Kesalahan</h1>
                    <p>Mohon maaf, terjadi kesalahan server. Silakan coba lagi nanti.</p>
                </body></html>';
            }
            exit;
        }
    });
}

// ============================================
// SESSION CONFIGURATION
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    // Session security settings
    $sessionSecure = filter_var(getenv('SESSION_SECURE') ?: '0', FILTER_VALIDATE_BOOLEAN);
    $sessionLifetime = (int)(getenv('SESSION_LIFETIME') ?: 7200);
    
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', $sessionSecure ? 1 : 0);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', $sessionLifetime);
    
    // Start session
    session_start();
}

// ============================================
// DATABASE CONFIGURATION
// ============================================
// Ambil dari .env, fallback ke hardcode
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'db_pengajuan_dokumen');

// Connect ke database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$conn) {
    if (DEBUG_MODE) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    } else {
        error_log('Database connection failed: ' . mysqli_connect_error());
        throw new Exception("Database connection failed");
    }
}

// Set charset UTF-8
mysqli_set_charset($conn, "utf8");

// ============================================
// PATH CONFIGURATION
// ============================================
$baseUrl = getenv('APP_URL') ?: 'http://localhost/project_pengajuan_dokumen/';
define('BASE_URL', rtrim($baseUrl, '/') . '/');

$project_root = dirname(__DIR__); 

define('UPLOAD_PATH', $project_root . '/uploads/');
define('SURAT_PATH', $project_root . '/surat/');

// Ensure upload directories exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
if (!file_exists(SURAT_PATH)) {
    mkdir(SURAT_PATH, 0755, true);
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Redirect ke URL tertentu
 */
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

/**
 * Bersihkan input untuk mencegah SQL Injection & XSS
 */
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

/**
 * Cek apakah user sudah login
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Cek role user
 */
function check_role($allowed_roles) {
    if (!is_logged_in()) {
        redirect('auth/login.php');
    }
    
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        redirect('index.php');
    }
}

/**
 * Get data user dari database
 */
function get_user_data($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    $query = "SELECT * FROM t_pengguna WHERE id_pengguna = $user_id LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

/**
 * Generate CSRF Token untuk form security
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validasi CSRF Token
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ============================================
// DISPLAY CURRENT MODE (Development only)
// ============================================
if (DEBUG_MODE) {
    echo "<!-- Running in " . strtoupper(APP_ENV) . " mode -->\n";
}
?>