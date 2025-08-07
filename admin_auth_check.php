<?php
// FILE 4 of 9: admin_auth_check.php
// ===================================================================
// This is the security guard. It is the ONLY file that should start the session for admin pages.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html?error=Access Denied.");
    die("Redirecting...");
}
?>