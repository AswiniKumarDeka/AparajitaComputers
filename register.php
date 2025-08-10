<?php
// register.php

// --- SETUP ---
// This is the most common point of failure.
// Make sure this path is correct and points to your database connection file.
require_once 'database.php';

// This will hold any message we want to show the user.
$message = ''; 


// --- FORM SUBMISSION LOGIC ---
// Only run the code if the form was actually submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. VALIDATE INPUT: Check if any fields are empty.
    if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])) {
        $message = 'Error: Please fill out all fields.';
    } else {
        // --- DATABASE LOGIC ---
        // We use a try-catch block to handle any potential database errors gracefully.
        try {
            // 2. CHECK FOR DUPLICATES: See if username or email is already in use.
            $sql_check = "SELECT id FROM users WHERE username = ? OR email = ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$_POST['username'], $_POST['email']]);

            if ($stmt_check->fetch()) {
                // If fetch() finds a user, it means the username or email is taken.
                $message = 'Error: Username or email is already registered.';
            } else {
                // 3. HASH PASSWORD: This is critical for security.
                // Never, ever store plain text passwords.
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

                // 4. INSERT NEW USER: Add the new user to the database.
                $sql_insert = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                $stmt_insert = $pdo->prepare($sql_insert);
                
                // Execute the query. If it works, registration is successful.
                if ($stmt_insert->execute([$_POST['username'], $_POST['email'], $hashed_password])) {
                    $message = 'Success! Your account has been created. You can now <a href="login.html">sign in</a>.';
                } else {
                    $message = 'An unexpected error occurred. Please try again.';
                }
            }
        } catch (PDOException $e) {
            // If the database connection or query fails, this code will run.
            // For debugging, you can see the specific error.
            // error_log("Database Error: " . $e->getMessage());
            $message = 'Error: A database problem occurred. Please contact the administrator.';
        }
    }
}

// --- DISPLAY FEEDBACK ---
// This will display the final $message to the user on the registration page.
// You should place this PHP block where you want the message to appear in your HTML.
if (!empty($message)) {
    // We add some basic styling to make the message stand out.
    echo '<div style="padding: 15px; margin-bottom: 20px; border: 1px solid; border-radius: 5px;';
    if (strpos($message, 'Success') !== false) {
        echo 'color: #155724; background-color: #d4edda; border-color: #c3e6cb;">';
    } else {
        echo 'color: #721c24; background-color: #f8d7da; border-color: #f5c6cb;">';
    }
    echo $message;
    echo '</div>';
}
?>
