<?php
// FILE: admin_auth_check.php
// ===================================================================
// Security guard for all admin pages.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in AND has admin role
if (
    !isset($_SESSION['user_id']) || 
    !isset($_SESSION['user_role']) || 
    $_SESSION['user_role'] !== 'admin'
) {
    header("Location: login.html?error=Access Denied. Admins only.");
    exit;
}
?>
