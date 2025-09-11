<?php
session_start();
require 'db_connect.php';

// --- Security Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// --- Fetch User Data ---
$userId = $_SESSION['user_id'];
$user = null;
$uploads = [];
$orders = [];

try {
    // --- CORRECTED: Fetch user profile using mysqli syntax ---
    $stmt = $conn->prepare("SELECT name, email, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // --- CORRECTED: Fetch uploads using mysqli syntax ---
    $stmt = $conn->prepare("SELECT original_file_name, uploaded_at FROM uploads WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 5");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $uploads = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // --- CORRECTED: Fetch orders using mysqli syntax ---
    $stmt = $conn->prepare("SELECT service_name, payment_status, payment_date FROM payments WHERE user_id = ? ORDER BY payment_date DESC LIMIT 5");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

} catch (Exception $e) {
    // A generic Exception is better here than PDOException for mysqli
    error_log("Dashboard Error: " . $e->getMessage());
    die("Error: Could not fetch user data. Please contact support.");
}

$conn->close(); // It's good practice to close the connection when done.
?>
