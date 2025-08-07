<?php
// upload.php (Corrected Version)
header('Content-Type: application/json');

require 'db_connect.php';
session_start();

$response = [];

// --- Security Check: Ensure user is logged in ---
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $response['error'] = "You must be logged in to upload files.";
    echo json_encode($response);
    exit;
}

// --- Check if the form was submitted correctly ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- Get data from the form ---
    $userId = $_SESSION['id'];
    $customerName = trim($_POST['customerName']);
    $customerEmail = trim($_POST['customerEmail']);
    $instructions = trim($_POST['instructions']);

    // --- Basic Validation ---
    if (empty($customerName) || empty($customerEmail)) {
        $response['error'] = "Name and Email are required.";
        echo json_encode($response);
        exit;
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] != UPLOAD_ERR_OK) {
        $response['error'] = "File upload failed or no file was selected. Please try again.";
        echo json_encode($response);
        exit;
    }

    // --- File Handling ---
    $uploadDir = 'uploads/'; // Make sure this directory exists and is writable!
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            $response['error'] = "Failed to create upload directory. Please check server permissions.";
            echo json_encode($response);
            exit;
        }
    }

    $originalFileName = basename($_FILES['file']['name']);
    $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
    
    // --- Create a unique, safe filename ---
    // CORRECTED LINE: Using $fileExtension instead of the incorrect variable.
    $storedFileName = uniqid('file_', true) . '.' . $fileExtension;
    $filePath = $uploadDir . $storedFileName;

    // --- Move the file to the uploads directory ---
    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        
        // --- File moved successfully, now save details to the database ---
        $stmt = $conn->prepare(
            "INSERT INTO uploads (user_id, customer_name, customer_email, original_file_name, stored_file_name, file_path, instructions) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("issssss", $userId, $customerName, $customerEmail, $originalFileName, $storedFileName, $filePath, $instructions);

        if ($stmt->execute()) {
            $response['message'] = "File uploaded successfully! We will review your request shortly.";
        } else {
            // If DB insert fails, delete the uploaded file to avoid orphaned files
            unlink($filePath); 
            $response['error'] = "Database error: Could not save file details.";
        }
        $stmt->close();

    } else {
        $response['error'] = "Could not move the uploaded file. Check server permissions for the 'htdocs/my_shop' folder.";
    }

    $conn->close();

} else {
    $response['error'] = "Invalid request method.";
}

echo json_encode($response);
?>
