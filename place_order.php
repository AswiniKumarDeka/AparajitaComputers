<?php
session_start();
require 'db_connect.php';

// Load PHPMailer
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure logged in
if (empty($_SESSION['user_id'])) {
    header("Location: login.html?error=Please login first");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $service_id = intval($_POST['service_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    $amount = floatval($_POST['amount'] ?? 0);
    $customer_email = filter_var($_POST['customer_email'] ?? '', FILTER_VALIDATE_EMAIL);
    $instructions = trim($_POST['instructions'] ?? "");

    if ($service_id <= 0 || $quantity <= 0 || $amount <= 0 || !$customer_email) {
        header("Location: payment.php?service_id=$service_id&error=Invalid order details.");
        exit;
    }

    // Fetch service again for safety
    $stmt = $conn->prepare("SELECT name, price FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        header("Location: payment.php?error=Service not found.");
        exit;
    }

    // Generate order ID
    $order_id = 'AC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), 7, 6));

    try {
        // Save order
        $sql = "INSERT INTO payments (user_id, order_id, service_name, quantity, instructions, amount, payment_method, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, 'cod', 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id, $order_id, $service['name'], $quantity, $instructions, $amount]);

        // Send email
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'aparajitacomputers.shop@gmail.com'; // change
        $mail->Password = 'Aparajita$$1993';   // change
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('aparajitacomputers.shop@gmail.com', 'Aparajita Computers');
        $mail->addAddress($customer_email);
        $mail->isHTML(true);
        $mail->Subject = "Order Acknowledgement - {$order_id}";
        $mail->Body = "
            <h2>✅ Order Acknowledgement</h2>
            <p>We received your order.</p>
            <ul>
                <li><b>Order ID:</b> $order_id</li>
                <li><b>Service:</b> {$service['name']}</li>
                <li><b>Quantity:</b> $quantity</li>
                <li><b>Total:</b> ₹" . number_format($amount, 2) . "</li>
                <li><b>Status:</b> Pending</li>
            </ul>
        ";
        $mail->send();

        header("Location: success.html?order_id=$order_id");
        exit;
    } catch (Exception $e) {
        error_log("Order placement failed: " . $e->getMessage());
        header("Location: payment.php?service_id=$service_id&error=Could not place your order. Please try again.");
        exit;
    }
} else {
    header("Location: index.html");
    exit;
}
?>
