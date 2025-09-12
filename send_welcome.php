<?php
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendWelcomeEmail($customer_email, $customer_name) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'aparajitacomputers.shop@gmail.com';
        $mail->Password   = 'Aparajita$$1993';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('aparajitacomputers.shop@gmail.com', 'Aparajita Computers');
        $mail->addAddress($customer_email, $customer_name);

        $mail->isHTML(true);
        $mail->Subject = "🎉 Welcome to Aparajita Computers!";
        $mail->Body    = "
            <h2>Welcome, {$customer_name}!</h2>
            <p>We’re excited to have you on board.</p>
            <p>As a new member, you’ll get early access to offers and services.</p>
            <p><strong>Stay tuned!</strong> We’ll keep you updated with the latest deals.</p>
            <br>
            <p>— Aparajita Computers Team</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Welcome email failed: {$mail->ErrorInfo}");
    }
}
?>
