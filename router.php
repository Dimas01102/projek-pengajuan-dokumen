<?php

if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $file = __DIR__ . $path;

    // kalau file atau folder asli ada → tampilkan langsung
    if ($path !== '/' && file_exists($file)) {
        return false;
    }
}

// default buka index
require __DIR__ . '/index.php';
