<?php
session_start();
require 'db_connect.php';

// Set the correct content type for JSON responses
header('Content-Type: application/json');

// --- Security Check: Ensure user is logged in ---
if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Access Denied: You must be logged in to upload files.']);
    exit;
}

// --- Check if it's a POST request with a file ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
    echo json_encode(['error' => 'Invalid request method or no file uploaded.']);
    exit;
}

// --- File Upload Handling ---
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$file = $_FILES['file'];
$userId = $_SESSION['user_id'];
$customerName = trim($_POST['customerName'] ?? 'N/A');
$customerEmail = filter_var(trim($_POST['customerEmail'] ?? ''), FILTER_VALIDATE_EMAIL);
$instructions = trim($_POST['instructions'] ?? '');

// File validation
$maxFileSize = 50 * 1024 * 1024; // 50 MB
$allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'File upload error. Please try again.']);
    exit;
}
if ($file['size'] > $maxFileSize) {
    echo json_encode(['error' => 'File is too large. Maximum size is 50 MB.']);
    exit;
}
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, PDF, and Word documents are allowed.']);
    exit;
}

// Create a unique stored filename to prevent overwrites
$originalFileName = basename($file['name']);
$fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
$storedFileName = uniqid('file_', true) . '.' . $fileExtension;
$destination = $uploadDir . $storedFileName;

// Move the uploaded file
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['error' => 'Failed to save the uploaded file.']);
    exit;
}

// --- Order Creation ---
try {
    // Generate a unique Order ID for the upload
    $order_id = 'AC-UP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), 7, 6));
    $service_name = "File Upload: " . $originalFileName;
    
    // Save the upload information as an order in the database
    $sql = "INSERT INTO payments 
            (user_id, order_id, service_name, instructions, amount, payment_method, payment_status) 
            VALUES (?, ?, ?, ?, 0.00, 'upload', 'pending')";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId, $order_id, $service_name, $instructions]);

    // Also save a record to the uploads table for the "My Files" page
    $uploadSql = "INSERT INTO uploads (user_id, original_file_name, stored_file_name) VALUES (?, ?, ?)";
    $uploadStmt = $conn->prepare($uploadSql);
    $uploadStmt->execute([$userId, $originalFileName, $storedFileName]);

    // --- Success ---
    echo json_encode([
        'success' => 'File uploaded successfully! We will contact you shortly.',
        'order_id' => $order_id 
    ]);

} catch (PDOException $e) {
    // Log the error and send a generic failure message
    error_log("Database error during file upload: " . $e->getMessage());
    echo json_encode(['error' => 'A database error occurred. Could not create an order for the upload.']);
} finally {
    // Close the connection
    $conn = null;
}
?>
