<?php
/**
 * File: test_500.php
 * Fungsi: Mensimulasikan error server (HTTP 500)
 */

// Set header HTTP 500
http_response_code(500);

// Simulasikan error fatal PHP
// non_existing_function(); // fungsi ini tidak ada → fatal error

// Redirect ke halaman 500
require_once '500.php';
