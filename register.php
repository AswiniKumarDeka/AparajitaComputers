<?php
header('Content-Type: application/json');
require 'db_connect.php';

// Include PHPMailer
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

// Get and sanitize
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $email === '' || $password === '') {
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

try {
    // Hash password securely
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Insert user (role defaults to 'user', suspended = 0)
    $stmt = $conn->prepare(
        "INSERT INTO users (name, username, email, password, role, is_suspended)
         VALUES (:name, :username, :email, :password, 'user', FALSE)"
    );
    $stmt->execute([
        ':name'     => $username,
        ':username' => $username,
        ':email'    => $email,
        ':password' => $hashed,
    ]);

    // ✅ Send Welcome Email
    $mail = new PHPMailer(true);
    try {
        // SMTP Settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'aparajitacomputers.shop@gmail.com';    // 🔹 Replace with your email
        $mail->Password   = 'Aparajita$$1993';       // 🔹 Use Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('aparajitacomputers.shop@gmail.com', 'Aparajita Computers');
        $mail->addAddress($email, $username);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = "🎉 Welcome to Aparajita Computers!";
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; line-height:1.6;'>
                <h2>Hello, {$username} 👋</h2>
                <p>Welcome to <strong>Aparajita Computers</strong>! 🎉</p>
                <p>Your account has been created successfully.</p>
                <h3>Here’s what you can do:</h3>
                <ul>
                    <li>✔️ Browse and order our services</li>
                    <li>✔️ Track your payments and orders</li>
                    <li>✔️ Get exclusive offers by email</li>
                </ul>
                <p>Stay tuned for exciting offers 💻✨</p>
                <p><strong>- Aparajita Computers Team</strong></p>
            </div>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Welcome email failed: {$mail->ErrorInfo}");
    }

    echo json_encode(['message' => 'Registered successfully! Welcome email sent.']);

} catch (Throwable $e) {
    // Likely unique constraint or SQL error
    echo json_encode(['error' => $e->getMessage()]);
}




// header('Content-Type: application/json');
// require 'db_connect.php';

// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//     echo json_encode(['error' => 'Invalid request.']);
//     exit;
// }

// // Get and sanitize
// $username = trim($_POST['username'] ?? '');
// $email    = trim($_POST['email'] ?? '');
// $password = $_POST['password'] ?? '';

// if ($username === '' || $email === '' || $password === '') {
//     echo json_encode(['error' => 'All fields are required.']);
//     exit;
// }

// try {
//     // Hash password securely
//     $hashed = password_hash($password, PASSWORD_DEFAULT);

//     // Insert user (role defaults to 'user', suspended = 0)
//     $stmt = $conn->prepare(
//         "INSERT INTO users (name, username, email, password, role, is_suspended)
//          VALUES (:name, :username, :email, :password, 'user', FALSE)"
//     );
//     $stmt->execute([
//         ':name' => $username,      
//         ':username' => $username,
//         ':email'    => $email,
//         ':password' => $hashed,
//     ]);
//     echo json_encode(['message' => 'Registered successfully!']);
// } catch (Throwable $e) {
//     // Likely unique constraint or SQL error
//     echo json_encode(['error' => $e->getMessage()]);
// }
