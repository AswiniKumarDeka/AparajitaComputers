<?php
session_start();
require 'db_connect.php';

// Always return JSON
header('Content-Type: application/json');

// Security: only logged-in users can submit reviews
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "You must be logged in to submit a review."
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$rating  = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : "";

// Validate input
if ($rating < 1 || $rating > 5) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid rating value."
    ]);
    exit;
}

if (empty($comment)) {
    echo json_encode([
        "status" => "error",
        "message" => "Review comment cannot be empty."
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, rating, comment) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iis", $user_id, $rating, $comment);
    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Thank you! Your review has been submitted."
        ]);
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log("Review Submit Error: " . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => "An unexpected error occurred. Please try again later."
    ]);
}
