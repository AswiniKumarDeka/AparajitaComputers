<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    ob_end_clean();
    header("Location: login.html?error=" . urlencode("You must be logged in to access this page."));
    die("Redirecting...");
}
ob_end_flush();
?>
