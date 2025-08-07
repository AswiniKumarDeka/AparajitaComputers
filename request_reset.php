<?php
// request_reset.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

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

    // Check if user with that email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate a unique, secure token
        $token = bin2hex(random_bytes(50));
        
        // Set token expiration to 1 hour from now
        $expires = new DateTime('NOW');
        $expires->add(new DateInterval('PT1H'));
        $expires_str = $expires->format('Y-m-d H:i:s');

        // Store the token in the database
        $stmt_update = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
        $stmt_update->bind_param("sss", $token, $expires_str, $email);
        $stmt_update->execute();
        
        // --- SIMULATE SENDING EMAIL ---
        // In a real application, you would use a library like PHPMailer to send an email.
        // For this example, we will just display the link on the screen.
        
        // Construct the reset link
        $reset_link = "http://localhost/my_shop/reset_password.php?token=" . $token;

        echo "If an account with that email exists, a password reset link has been generated.<br><br>";
        echo "<strong>For testing purposes, here is the link (in a real app, this would be emailed):</strong><br>";
        echo "<a href='" . $reset_link . "' style='color: cyan;'>" . $reset_link . "</a>";

    } else {
        echo "If an account with that email exists, a password reset link has been sent.";
    }

    $stmt->close();
    $conn->close();
}
?>
