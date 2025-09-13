<?php
// upload.php
session_start();
require 'db_connect.php';

// Ensure user logged in
if (empty($_SESSION['user_id'])) {
    header("Location: login.html?error=Login required.");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['file'])) {
    $user_id   = $_SESSION['user_id'];
    $order_id  = $_POST['order_id'] ?? null;
    $uploadDir = "uploads/";

    if (!$order_id) {
        die("Missing order ID.");
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $original_file_name = basename($_FILES['file']['name']);
    $file_path = $uploadDir . time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $original_file_name);

    if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
        try {
            $sql = "INSERT INTO uploads (user_id, order_id, original_file_name, file_path, uploaded_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id, $order_id, $original_file_name, $file_path]);

            header("Location: success.php?order_id={$order_id}&upload=success");
            exit;
        } catch (PDOException $e) {
            error_log("Upload DB error: ".$e->getMessage());
            die("Failed to save upload.");
        }
    } else {
        die("Failed to upload file.");
    }
} else {
    header("Location: index.html");
    exit;
}
