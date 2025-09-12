<?php
require 'db_connect.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fetch all customer emails
$result = $conn->query("SELECT name, email FROM users WHERE role='user'");

while ($row = $result->fetch_assoc()) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '<?php
require 'db_connect.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fetch all customer emails
$result = $conn->query("SELECT name, email FROM users WHERE role='user'");

while ($row = $result->fetch_assoc()) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '<?php
require 'db_connect.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fetch all customer emails
$result = $conn->query("SELECT name, email FROM users WHERE role='user'");

while ($row = $result->fetch_assoc()) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'aparajitacomputers@gmail.com';
        $mail->Password   = 'Aparajita$$1993';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('aparajitacomputers@gmail.com', 'Aparajita Computers');
        $mail->addAddress($row['email'], $row['name']);

        $mail->isHTML(true);
        $mail->Subject = "🔥 Exclusive Offers from Aparajita Computers!";
        $mail->Body    = "
            <h2>Hi {$row['name']},</h2>
            <p>Here are this week's special offers just for you:</p>
            <ul>
                <li>💻 Laptop Servicing - 20% OFF</li>
                <li>🖨️ Printer Repair - Flat ₹199</li>
                <li>⚡ Software Installation - 30% OFF</li>
            </ul>
            <p>Hurry! These offers are valid till " . date('d M Y', strtotime('+7 days')) . ".</p>
            <p><a href='https://yourwebsite.com'>Book Now</a></p>
            <br>
            <p>— Aparajita Computers Team</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Offer email failed: {$mail->ErrorInfo}");
    }
}
?>
';
        $mail->Password   = 'YOUR_APP_PASSWORD';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('aparajitacomputers@gmail.com', 'Aparajita Computers');
        $mail->addAddress($row['email'], $row['name']);

        $mail->isHTML(true);
        $mail->Subject = "🔥 Exclusive Offers from Aparajita Computers!";
        $mail->Body    = "
            <h2>Hi {$row['name']},</h2>
            <p>Here are this week's special offers just for you:</p>
            <ul>
                <li>💻 Laptop Servicing - 20% OFF</li>
                <li>🖨️ Printer Repair - Flat ₹199</li>
                <li>⚡ Software Installation - 30% OFF</li>
            </ul>
            <p>Hurry! These offers are valid till " . date('d M Y', strtotime('+7 days')) . ".</p>
            <p><a href='https://yourwebsite.com'>Book Now</a></p>
            <br>
            <p>— Aparajita Computers Team</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Offer email failed: {$mail->ErrorInfo}");
    }
}
?>
';
        $mail->Password   = 'YOUR_APP_PASSWORD';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('aparajitacomputers@gmail.com', 'Aparajita Computers');
        $mail->addAddress($row['email'], $row['name']);

        $mail->isHTML(true);
        $mail->Subject = "🔥 Exclusive Offers from Aparajita Computers!";
        $mail->Body    = "
            <h2>Hi {$row['name']},</h2>
            <p>Here are this week's special offers just for you:</p>
            <ul>
                <li>💻 Laptop Servicing - 20% OFF</li>
                <li>🖨️ Printer Repair - Flat ₹199</li>
                <li>⚡ Software Installation - 30% OFF</li>
            </ul>
            <p>Hurry! These offers are valid till " . date('d M Y', strtotime('+7 days')) . ".</p>
            <p><a href='https://yourwebsite.com'>Book Now</a></p>
            <br>
            <p>— Aparajita Computers Team</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Offer email failed: {$mail->ErrorInfo}");
    }
}
?>
