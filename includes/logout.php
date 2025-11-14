<?php
// includes/logout.php
// Proses Logout
session_start();
session_unset();
session_destroy();

// Redirect ke halaman login
header("Location: ../index.php");
exit();
?>