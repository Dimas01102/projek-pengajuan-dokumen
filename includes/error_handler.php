<?php
/**
 * ERROR HANDLER - Custom PHP Error Handler
 */

// Nonaktifkan tampilan error ke user
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Fungsi untuk redirect ke halaman 500
function redirect_to_500_page($error_details = '') {
    // Clear semua output buffer
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Set HTTP response code
    http_response_code(500);
    
    // Cek apakah request via AJAX
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    if ($is_ajax) {
        // Response JSON untuk AJAX
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Internal server error. Please try again later.',
            'error_code' => 500
        ]);
        exit;
    }
    
    // Untuk regular request, include halaman 500.php
    $error_page = __DIR__ . '/../500.php';
    
    if (file_exists($error_page)) {
        // Include langsung halaman 500.php (tidak redirect)
        include $error_page;
        exit;
    } else {
        // Fallback jika 500.php tidak ada
        echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Internal Server Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: white;
            text-align: center;
        }
        .error-box {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            max-width: 600px;
        }
        h1 { font-size: 80px; margin: 0; }
        h2 { font-size: 32px; margin: 20px 0; }
        p { font-size: 18px; line-height: 1.6; }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>500</h1>
        <h2>Internal Server Error</h2>
        <p>Maaf, terjadi kesalahan pada server. Silakan coba lagi nanti.</p>
        <a href="/project_pengajuan_dokumen/">Kembali ke Beranda</a>
    </div>
</body>
</html>';
        exit;
    }
}

// Fungsi untuk log error
function log_error($message) {
    $log_dir = __DIR__ . '/../logs';
    
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    
    $htaccess_file = $log_dir . '/.htaccess';
    if (!file_exists($htaccess_file)) {
        @file_put_contents($htaccess_file, "Order Deny,Allow\nDeny from all");
    }
    
    // Log error
    $log_file = $log_dir . '/php_errors.log';
    $timestamp = date('[Y-m-d H:i:s]');
    $log_message = "$timestamp $message\n";
    @error_log($log_message, 3, $log_file);
}

// Handler untuk fatal errors (shutdown)
function handle_shutdown() {
    $error = error_get_last();
    
    if ($error !== NULL) {
        $fatal_errors = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
        
        if (in_array($error['type'], $fatal_errors)) {
            // Log error
            $error_msg = "FATAL ERROR: {$error['message']} in {$error['file']} on line {$error['line']}";
            log_error($error_msg);
            
            // Redirect ke 500
            redirect_to_500_page($error_msg);
        }
    }
}

// Handler untuk exceptions yang tidak ter-catch
function handle_exception($exception) {
    // Log exception
    $error_msg = "UNCAUGHT EXCEPTION: {$exception->getMessage()} in {$exception->getFile()} on line {$exception->getLine()}";
    log_error($error_msg);
    
    // Redirect ke 500
    redirect_to_500_page($error_msg);
}

// Handler untuk errors biasa
function handle_error($errno, $errstr, $errfile, $errline) {
    // Hanya handle error serius
    $fatal_errors = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    
    if (in_array($errno, $fatal_errors)) {
        $error_msg = "ERROR [$errno]: $errstr in $errfile on line $errline";
        log_error($error_msg);
        
        // Redirect ke 500
        redirect_to_500_page($error_msg);
    }
    
    // Return false untuk error biasa (handled by PHP default)
    return false;
}

// Register error handlers
register_shutdown_function('handle_shutdown');
set_exception_handler('handle_exception');
set_error_handler('handle_error');

// Start output buffering untuk menangkap error
ob_start();
?>