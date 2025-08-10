<?php
// register.php

// Step 1: Include your database connection file.
// Make sure the path is correct. This is the most important step.
require_once 'database.php'; // Or 'db.php', 'config.php', etc.

// Initialize a variable to hold messages to the user.
$message = '';

// Step 2: Check if the form was submitted using the POST method.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Step 3: Check if all required fields are filled.
    if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])) {
        $message = 'Please fill out all fields.';
    } else {
        // Sanitize user input to prevent XSS attacks (basic sanitation).
        $username = htmlspecialchars($_POST['username']);
        $email = htmlspecialchars($_POST['email']);
        $password = $_POST['password'];

        // --- DATABASE INTERACTION ---
        try {
            // Step 4: Check if the username or email already exists in the database.
            $sql_check = "SELECT * FROM users WHERE username = ? OR email = ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$username, $email]);
            
            if ($stmt_check->rowCount() > 0) {
                // User already exists.
                $message = 'Username or email already taken.';
            } else {
                // Step 5: Hash the password for security.
                // Never store plain text passwords!
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Step 6: Insert the new user into the database.
                $sql_insert = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                $stmt_insert = $pdo->prepare($sql_insert);
                
                // Execute the statement.
                if ($stmt_insert->execute([$username, $email, $hashed_password])) {
                    $message = 'Registration successful! You can now <a href="login.html">sign in</a>.';
                } else {
                    $message = 'An unexpected error occurred during registration.';
                }
            }
        } catch (PDOException $e) {
            // This will catch any database-related errors.
            // In production, you might want to log this error instead of showing it to the user.
            // error_log("Registration Error: " . $e->getMessage());
            $message = 'Database error. Please try again later.';
        }
    }
}

// Display the message to the user if it's not empty.
if (!empty($message)) {
    echo "<div class='message'>{$message}</div>";
}
?>
