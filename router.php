<?php

if (php_sapi_name() === 'cli-server') {
    $url  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $url;

    // kalau file asli ada (css, js, img, pdf)
    if (is_file($file)) {
        return false;
    }
}

// arahkan semua request ke index
require_once __DIR__ . '/index.php';
