<?php
// update_password.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Basic validation
    if (empty($token) || empty($password) || empty($password_confirm)) {
        die("Please fill all fields.");
    }
    if ($password !== $password_confirm) {
        die("Passwords do not match.");
    }
    if (strlen($password) < 6) {
        die("Password must be at least 6 characters long.");
    }

    // --- Database Connection ---
    $servername = "localhost";
    $username_db = "root";
    $password_db = "YourActualPassword"; // Your password
    $dbname = "my_shop_db";
    $port = 3307; // Your port
    $conn = new mysqli($servername, $username_db, $password_db, $dbname, $port);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Verify the token again
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update the password and clear the reset token
        $stmt_update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE reset_token = ?");
        $stmt_update->bind_param("ss", $hashed_password, $token);
        
        if ($stmt_update->execute()) {
            echo "Your password has been updated successfully. You can now <a href='login.html' style='color: cyan;'>log in</a> with your new password.";
        } else {
            echo "Error updating password.";
        }
        $stmt_update->close();
    } else {
        echo "Invalid or expired token.";
    }

    $stmt->close();
    $conn->close();
}
?>
