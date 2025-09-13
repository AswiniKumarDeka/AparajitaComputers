<?php
session_start();
require 'db_connect.php';

// Require PHPMailer
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Check if user logged in ---
if (empty($_SESSION['user_id'])) {
    header("Location: login.html?error=Please login first");
    exit;
}

// --- Allow only POST ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: payment.php?error=Invalid request");
    exit;
}

$user_id   = $_SESSION['user_id'];
$service   = trim($_POST['service_name'] ?? '');
$quantity  = intval($_POST['quantity'] ?? 1);
$amount    = floatval($_POST['amount'] ?? 0);
$email     = filter_var($_POST['customer_email'] ?? '', FILTER_VALIDATE_EMAIL);
$method    = $_POST['payment_method'] ?? 'cod';
$notes     = trim($_POST['instructions'] ?? "");

// --- Validation ---
if ($service === '' || $quantity <= 0 || $amount <= 0 || !$email) {
    header("Location: payment.php?error=Invalid order data");
    exit;
}

// --- Generate Order ID ---
$order_id = 'AC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), 7, 6));

try {
    // --- Save order ---
    $sql = "INSERT INTO payments 
            (user_id, order_id, service_name, quantity, instructions, amount, payment_method, payment_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $order_id, $service, $quantity, $notes, $amount, $method]);

    // --- Send acknowledgement email ---
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'aparajitacomputers.shop@gmail.com';
        $mail->Password   = 'YOUR_APP_PASSWORD'; // Use Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('aparajitacomputers.shop@gmail.com', 'Aparajita Computers');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Order Confirmation - {$order_id}";
        $mail->Body = "
            <h2>✅ Order Confirmed</h2>
            <p>Thank you for your order. Here are the details:</p>
            <ul>
                <li><strong>Order ID:</strong> {$order_id}</li>
                <li><strong>Service:</strong> " . htmlspecialchars($service) . "</li>
                <li><strong>Quantity:</strong> {$quantity}</li>
                <li><strong>Total Amount:</strong> ₹" . number_format($amount, 2) . "</li>
                <li><strong>Payment Method:</strong> {$method}</li>
                <li><strong>Status:</strong> Pending</li>
            </ul>
            <p>We will contact you shortly.</p>
        ";
        $mail->send();
    } catch (Exception $e) {
        error_log("Email error: " . $mail->ErrorInfo);
    }

    // --- Redirect success ---
    header("Location: success.php?order_id={$order_id}");
    exit;

} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    header("Location: payment.php?error=Could not place your order. Please try again.");
    exit;
}
