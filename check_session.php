<?php
// check_session.php
// This script's only job is to check if a user is logged in
// and return a simple JSON response for the JavaScript to read.

// The session must be started to access session variables.
session_start();

// Prepare the response array
$response = [
    'loggedin' => false,
    'username' => null,
    'role' => null
];

// Check if the session variables are set from a successful login
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $response['loggedin'] = true;
    $response['username'] = $_SESSION['username'];
    $response['role'] = $_SESSION['role'];
}

// Set the content type header to application/json
header('Content-Type: application/json');

// Echo the response as a JSON string
echo json_encode($response);
?>
