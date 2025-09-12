<?php
// place_order.php
session_start();
require 'db_connect.php';

// Require PHPMailer
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Security Check
if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['id'];
    $service_name = $_POST['service_name'] ?? '';
    $quantity = intval($_POST['quantity'] ?? 1);
    $amount = floatval($_POST['amount'] ?? 0);
    $customer_email = $_POST['customer_email'] ?? "unknown@example.com";
    $instructions = trim($_POST['instructions'] ?? "");

    // Generate Order ID
    $order_id = 'AC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), 7, 6));

    // Save COD order
    $stmt = $conn->prepare("INSERT INTO payments 
        (user_id, order_id, service_name, instructions, amount, payment_method, payment_status) 
        VALUES (?, ?, ?, ?, ?, 'cod', 'pending')");
    $stmt->bind_param("isssd", $user_id, $order_id, $service_name, $instructions, $amount);

    if ($stmt->execute()) {
        // Send acknowledgement email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'aparajitacomputers.shoop.com'; 
            $mail->Password   = 'Aparajita$$1993'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('aparajitacomputers.shop@gmail.com', 'Aparajita Computers');
            $mail->addAddress($customer_email);

            $mail->isHTML(true);
            $mail->Subject = "Order Acknowledgement - Order ID {$order_id}";
            $mail->Body = "
                <h2>✅ Order Acknowledgement</h2>
                <p>Dear Customer,</p>
                <p>We have successfully received your order.</p>
                <h3>Order Details:</h3>
                <ul>
                    <li><strong>Order ID:</strong> {$order_id}</li>
                    <li><strong>Service:</strong> {$service_name}</li>
                    <li><strong>Quantity:</strong> {$quantity}</li>
                    <li><strong>Instructions:</strong> " . (!empty($instructions) ? htmlspecialchars($instructions) : 'None') . "</li>
                    <li><strong>Total Amount:</strong> ₹{$amount}</li>
                    <li><strong>Payment Method:</strong> Cash on Delivery (COD)</li>
                    <li><strong>Status:</strong> Payment Pending</li>
                </ul>
                <p>We will contact you shortly regarding delivery.</p>
                <p><strong>Thank you for choosing Aparajita Computers!</strong></p>
            ";
            $mail->send();
        } catch (Exception $e) {
            error_log("COD acknowledgement email failed: {$mail->ErrorInfo}");
        }

        header("Location: success.html?type=cod&order_id={$order_id}");
        exit;
    } else {
        header("Location: payment.php?error=Could not place your order. Please try again.");
        exit;
    }
} else {
    header("Location: index.html");
    exit;
}
?>


