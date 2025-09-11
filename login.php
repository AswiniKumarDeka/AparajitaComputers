<?php
session_start();
require 'db_connect.php'; // Ensure this is a PDO connection

header('Content-Type: application/json');

// Function to send a JSON response and exit
function json_response($data) {
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Invalid request method.']);
}

// 1. Validate inputs
$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;
$role = $_POST['role'] ?? null;

if (empty($email) || empty($password) || empty($role)) {
    json_response(['success' => false, 'error' => 'All fields are required.']);
}

// 2. Fetch user from database based on email AND role
try {
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ? AND role = ? LIMIT 1");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Verify password and proceed
    if ($user && password_verify($password, $user['password'])) {
        // --- LOGIN SUCCESS ---
        session_regenerate_id(true); // Prevent session fixation attacks

        // ✅ Store consistent session variables
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['name'];
        $_SESSION['user_role'] = $user['role']; // ✅ matches admin_auth_check.php

        // Redirect URLs
        $redirectUrl = ($user['role'] === 'admin') ? 'admin_dashboard.php' : 'user_dashboard.php';

        // Send the redirect URL back to the JavaScript
        json_response(['success' => true, 'redirect' => $redirectUrl]);

    } else {
        // --- LOGIN FAILED ---
        json_response(['success' => false, 'error' => 'Invalid email, password, or role.']);
    }

} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage()); // Log error for server admin
    json_response(['success' => false, 'error' => 'A server error occurred. Please try again later.']);
}

$conn = null;
?>




// <?php
// session_start();
// require 'db_connect.php'; // Your central PDO connection

// header('Content-Type: application/json');

// // Function to send a JSON response and exit
// function json_response($data) {
//     echo json_encode($data);
//     exit;
// }

// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//     json_response(['success' => false, 'error' => 'Invalid request method.']);
// }

// // 1. Validate inputs
// $email = $_POST['email'] ?? null;
// $password = $_POST['password'] ?? null;
// $role = $_POST['role'] ?? null;

// if (empty($email) || empty($password) || empty($role)) {
//     json_response(['success' => false, 'error' => 'All fields are required.']);
// }

// // 2. Fetch user from database based on email AND role
// try {
//     $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ? AND role = ?");
//     $stmt->execute([$email, $role]);
//     $user = $stmt->fetch(PDO::FETCH_ASSOC);

//     // 3. Verify password and proceed
//     if ($user && password_verify($password, $user['password'])) {
//         // --- LOGIN SUCCESS ---
//         session_regenerate_id(true); // Prevent session fixation attacks

//         // Set session variables
//         $_SESSION['user_id'] = $user['id'];
//         $_SESSION['username'] = $user['name'];
//         $_SESSION['role'] = $user['role'];

//         // Determine the correct redirect URL based on role
//         $redirectUrl = ($user['role'] === 'admin') ? 'admin_dashboard.php' : 'index.html';

//         // Send the redirect URL back to the JavaScript
//         json_response(['success' => true, 'redirect' => $redirectUrl]);

//     } else {
//         // --- LOGIN FAILED ---
//         json_response(['success' => false, 'error' => 'Invalid email, password, or role.']);
//     }

// } catch (PDOException $e) {
//     error_log("Login Error: " . $e->getMessage()); // Log error for server admin
//     json_response(['success' => false, 'error' => 'A server error occurred. Please try again later.']);
// }

// $conn = null;
// ?>


