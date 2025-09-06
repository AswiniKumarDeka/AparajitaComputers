<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $email === '' || $password === '') {
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare(
        "INSERT INTO users (username, email, password, role, is_suspended)
         VALUES (?, ?, ?, 'user', 0)"
    );
    $stmt->bind_param("sss", $username, $email, $hashed);
    $stmt->execute();

    echo json_encode(['message' => 'Registered successfully!']);
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
exit;
