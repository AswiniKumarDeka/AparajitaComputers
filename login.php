<?php
// Set the content type to JSON
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
    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify the user exists and the password is correct
    if ($user && password_verify($password, $user['password'])) {
        // Login successful, set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role']; // Store user role in session
        
        // Determine redirect based on role
        $redirectUrl = ($user['role'] === 'admin') ? 'admin_dashboard.php' : 'dashboard.php';

        echo json_encode(['success' => true, 'redirect' => $redirectUrl]);
        exit;
    } else {
        // Invalid credentials
        echo json_encode(['error' => 'Invalid email or password.']);
        exit;
    }
} catch (PDOException $e) {
    // Database error
    echo json_encode(['error' => 'A database error occurred.']);
    exit;
}
?>
