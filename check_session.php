<?php
// Set the content type to JSON so JavaScript can understand it
header('Content-Type: application/json');
require 'db_connect.php'; // We need this to get user details

session_start();

$response = ['loggedin' => false];

if (isset($_SESSION['user_id'])) {
    try {
        // Fetch user details to get username and role
        $stmt = $conn->prepare("SELECT username, role FROM users WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $response['loggedin'] = true;
            $response['username'] = $user['username'];
            $response['role'] = $user['role'];
        }
    } catch (PDOException $e) {
        // If there's a DB error, treat as logged out
        $response['loggedin'] = false;
    }
}

echo json_encode($response);
?>
