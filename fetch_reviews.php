<?php
// fetch_reviews.php (with Enhanced Error Reporting)

// These lines force PHP to display any errors, which helps with debugging.
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$reviews = [];
$error = null;

try {
    require 'db_connect.php';

    // Fetch only APPROVED reviews, newest first
    $sql = "SELECT username, rating, review_text, created_at FROM reviews WHERE is_approved = 1 ORDER BY created_at DESC";
    $result = $conn->query($sql);

    // Check if the query itself failed
    if ($result === false) {
        throw new Exception("SQL Query Failed: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $row['rating'] = intval($row['rating']); 
            $reviews[] = $row;
        }
    }
    
    $conn->close();

} catch (Exception $e) {
    // If any error happens, we will catch it and prepare to display it.
    $error = $e->getMessage();
}

// Send back a response. If there was an error, the 'reviews' array will be empty
// and the 'error' field will contain the specific problem.
echo json_encode(['reviews' => $reviews, 'error' => $error]);
?>
