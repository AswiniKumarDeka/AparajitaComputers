<?php
// login.php

// Step 1: Start the session.
// This must be at the very top of the script, before any HTML.
session_start();

// Step 2: Include your database connection file.
// Ensure the path is correct.
require_once 'database.php'; // Or 'db.php', 'config.php', etc.

// Step 3: Check if the form was submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Step 4: Check if username and password fields are filled.
    if (empty($_POST['username']) || empty($_POST['password'])) {
        // Redirect back to login page with an error message.
        header("Location: login.html?error=emptyfields");
        exit();
    }

    // --- DATABASE INTERACTION ---
    try {
        $username = $_POST['username'];
        $password_from_form = $_POST['password'];

        // Step 5: Prepare and execute the query to find the user by username.
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        
        // Fetch the user data.
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Step 6: Verify the user exists and the password is correct.
        // The password_verify() function securely checks the submitted password against the stored hash.
        if ($user && password_verify($password_from_form, $user['password'])) {
            
            // --- SUCCESS! ---
            // Regenerate session ID to prevent session fixation attacks.
            session_regenerate_id(true);

            // Store user data in the session.
            $_SESSION['user_id'] = $user['id']; // Assuming you have an 'id' column.
            $_SESSION['username'] = $user['username'];
            
            // Redirect the user to a protected page (e.g., a dashboard).
            header("Location: dashboard.php");
            exit(); // Important to stop the script after a redirect.

        } else {
            // --- FAILED! ---
            // Username not found or password was incorrect.
            // Redirect back to login page with a generic error message.
            header("Location: login.html?error=invalidcred");
            exit();
        }

    } catch (PDOException $e) {
        // Catch any database errors.
        // In a real application, you should log this error, not show it to the user.
        // error_log("Login Error: " . $e->getMessage());
        header("Location: login.html?error=dberror");
        exit();
    }
} else {
    // If someone tries to access this script directly without POSTing data.
    header("Location: login.html");
    exit();
}
?>
