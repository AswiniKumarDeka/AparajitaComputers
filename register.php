<?php
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password, role, is_suspended) VALUES (?, ?, ?, 0)");
    $stmt->bind_param("sss", $username, $hashed_password, $role);

    if ($stmt->execute()) {
        header("Location: login.html?error=" . urlencode("Registered successfully! Please log in."));
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
