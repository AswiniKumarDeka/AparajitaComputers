<?php
// FILE: login.php
session_start();
require 'db_connect.php';

// Debug mode (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        header("Location: login.html?error=Email and password required");
        exit;
    }

    // Fetch user by email
    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        // ✅ Store session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['name'];
        $_SESSION['user_role'] = $user['role']; // VERY IMPORTANT

        // ✅ Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
            exit;
        } else {
            header("Location: user_dashboard.php");
            exit;
        }
    } else {
        header("Location: login.html?error=Invalid email or password");
        exit;
    }
} else {
    header("Location: login.html?error=Invalid request");
    exit;
}




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


