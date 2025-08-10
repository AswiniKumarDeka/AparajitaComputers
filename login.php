<?php

// Add this right at the beginning
var_dump($_POST);
die(); 
// ... rest of your code
session_start();

// Include the database connection file
require 'db_connect.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check for empty fields
    if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['role'])) {
        die("Error: All fields are required.");
    }

    // Get form data
    $email = trim($_POST['username']); // The form uses 'username' but the query uses email
    $password = $_POST['password'];
    $role_selected = strtolower(trim($_POST['role']));

    try {
        // Prepare and execute the query using correct PDO syntax
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);

        // Fetch the user record
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if a user was found and if the password is correct
        if ($user && password_verify($password, $user['password'])) {
            
            // Check if the role matches
            if (strtolower($user['role']) == $role_selected) {
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($role_selected == 'admin') {
                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    header("Location: index.php"); // Or user dashboard
                    exit();
                }

            } else {
                die("Invalid role selected for this user.");
            }

        } else {
            die("Invalid email or password.");
        }

    } catch (PDOException $e) {
        // Handle database errors
        die("Database query failed: " . $e->getMessage());
    }
}
?>
