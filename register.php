<?php
session_start();

// Include the database connection file
require 'db_connect.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check for empty fields
    if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['role'])) {
        die("Error: All fields are required.");
    }

    // Get form data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = strtolower(trim($_POST['role']));

    // --- IMPORTANT: Hash the password for security ---
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Prepare the SQL INSERT statement
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        // Execute the statement with the form data
        // The order of variables in the array MUST match the order of columns in the SQL
        $stmt->execute([$username, $email, $hashed_password, $role]);

        // If successful, redirect to the login page
        echo "Registration successful! You can now log in.";
        header("Location: login.php");
        exit();

    } catch (PDOException $e) {
        // Handle potential errors, like a duplicate email
        if ($e->getCode() == 23505) { // 23505 is the SQLSTATE for unique violation
            die("Error: This email address is already registered.");
        } else {
            die("Database query failed: " . $e->getMessage());
        }
    }
}
?>
