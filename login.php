<?php
// Force PHP to show all errors on screen
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check DB connection file
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Check for empty fields
    if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['role'])) {
        die("ERROR: All fields are required.");
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role_selected = strtolower(trim($_POST['role']));

    // Prepare and execute query
    $stmt = $conn->prepare("SELECT id, username, password, role, is_suspended FROM users WHERE username = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        die("Query failed: " . $stmt->error);
    }

    if ($result->num_rows !== 1) {
        die("Invalid username or user does not exist.");
    }

    $user = $result->fetch_assoc();

    // Check password
    if (!password_verify($password, $user['password'])) {
        die("Password is incorrect.");
    }

    // Check if suspended
    if ($user['is_suspended']) {
        die("Account is suspended.");
    }

    // Role match
    if (strtolower(trim($user['role'])) !== $role_selected) {
        die("Incorrect role selected. Role in DB: " . $user['role']);
    }

    // Success - start session
    session_regenerate_id(true);
    $_SESSION['loggedin'] = true;
    $_SESSION['id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    echo "LOGIN SUCCESSFUL. Redirecting...";

    if ($role_selected === 'admin') {
        header("refresh:2; url=admin_dashboard.php");
    } else {
        header("refresh:2; url=index.html");
    }
    exit;
} else {
    die("Invalid request method.");
}
