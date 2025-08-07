<?php
// submit_review.php
header('Content-Type: application/json');
session_start();
require 'db_connect.php';

$response = [];

// Security: Only logged-in users can submit reviews
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $response['error'] = "You must be logged in to submit a review.";
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id'];
    $username = $_SESSION['username'];
    $rating = intval($_POST['rating']);
    $review_text = trim($_POST['review_text']);

    // --- NEW: Check if user has already reviewed ---
    $stmt_check = $conn->prepare("SELECT id FROM reviews WHERE user_id = ?");
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $response['error'] = "You have already submitted a review.";
        echo json_encode($response);
        $stmt_check->close();
        $conn->close();
        exit;
    }
    $stmt_check->close();
    // --- End of new check ---

    // Validation
    if (empty($rating) || $rating < 1 || $rating > 5) {
        $response['error'] = "Please select a valid rating between 1 and 5.";
        echo json_encode($response);
        exit;
    }
    if (empty($review_text)) {
        $response['error'] = "Please write a review.";
        echo json_encode($response);
        exit;
    }

    // Insert the new review into the database with is_approved defaulting to 1
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, username, rating, review_text) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $user_id, $username, $rating, $review_text);

    if ($stmt->execute()) {
        $response['success'] = "Thank you! Your review has been submitted.";
    } else {
        $response['error'] = "Error: Could not submit your review at this time.";
    }
    
    $stmt->close();
    $conn->close();
} else {
    $response['error'] = "Invalid request method.";
}

echo json_encode($response);
?>
