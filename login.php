<?php
// Set the content type to JSON so JavaScript can understand it
header('Content-Type: application/json');
require 'db_connect.php';

// Start the session at the beginning
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

$email    = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['error' => 'Email and password are required.']);
    exit;
}

try {
    // Look up the user by email
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify the user exists and the password is correct
    if ($user && password_verify($password, $user['password'])) {
        // Login successful, set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Determine redirect based on role
        $redirectUrl = ($user['role'] === 'admin') ? 'admin_dashboard.php' : 'index.html';

        echo json_encode(['success' => true, 'redirect' => $redirectUrl]);
        exit;
    } else {
        // Invalid credentials
        echo json_encode(['error' => 'Invalid email or password.']);
        exit;
    }
} catch (PDOException $e) {
    // This is the missing block that fixes the error
    // It catches potential database errors
    echo json_encode(['error' => 'A database error occurred. Please try again.']);
    exit;
}
?>
