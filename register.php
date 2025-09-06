<?php
header('Content-Type: application/json');
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

// Get and sanitize
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $email === '' || $password === '') {
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

try {
    // Hash password securely
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Insert user (role defaults to 'user', suspended = 0)
    $stmt = $conn->prepare(
        "INSERT INTO users (username, email, password, role, is_suspended)
         VALUES (:username, :email, :password, 'user', FALSE)"
    );
    $stmt->execute([
        ':username' => $username,
        ':email'    => $email,
        ':password' => $hashed,
    ]);

    echo json_encode(['message' => 'Registered successfully!']);
} catch (Throwable $e) {
    // Likely unique constraint or SQL error
    echo json_encode(['error' => $e->getMessage()]);
}
