<?php
// return JSON
header('Content-Type: application/json');

require 'db_connect.php';   // <- must create a PDO $conn (or mysqli) connection

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    // Accept either raw JSON or classic form POST
    $input = [];
    if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
    } else {
        $input = $_POST;
    }

    $username = trim($input['username'] ?? '');
    $email    = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    if (!$username || !$email || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Please fill in all required fields.']);
        exit;
    }

    // hash password securely (bcrypt)
    $hash = password_hash($password, PASSWORD_BCRYPT);

    // prepare insert — change the table/column names to match your schema
    $sql = "INSERT INTO users (username, email, password, role, is_suspended)
            VALUES (:u, :e, :p, 'user', 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':u', $username);
    $stmt->bindParam(':e', $email);
    $stmt->bindParam(':p', $hash);

    $stmt->execute();

    echo json_encode(['message' => 'Registered successfully!']);
} catch (PDOException $e) {
    // 23505 = unique_violation in PostgreSQL
    if ($e->getCode() === '23505') {
        echo json_encode(['error' => 'Username or Email already exists.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: '.$e->getMessage()]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: '.$e->getMessage()]);
}
