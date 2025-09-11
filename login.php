<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json'); // important for AJAX

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user'; // 👈 take role from dropdown

    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Email and password required"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_role'] = $row['role'];

            echo json_encode([
                "status" => "success",
                "role" => $row['role'],
                "redirect" => ($row['role'] === 'admin') ? "admin_dashboard.php" : "user_dashboard.php"
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid password"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Account not found or wrong role"]);
    }

    $stmt->close();
    $conn->close();
}

