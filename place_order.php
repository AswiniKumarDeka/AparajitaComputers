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

// Ensure user logged in
if (empty($_SESSION['user_id'])) {
    header("Location: login.html?error=You must be logged in to place an order.");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id   = $_SESSION['user_id']; 
    $service_name = trim($_POST['service_name'] ?? 'N/A');
    $quantity  = intval($_POST['quantity'] ?? 1);
    $amount    = floatval($_POST['amount'] ?? 0);
    $customer_email = filter_var($_POST['customer_email'] ?? '', FILTER_VALIDATE_EMAIL);
    $instructions   = trim($_POST['instructions'] ?? "");

    // Basic validation
    if ($service_name === '' || $amount <= 0 || $quantity <= 0 || !$customer_email) {
        header("Location: payment.php?error=Invalid price or quantity.");
        exit;
    }

    // Generate Order ID
    $order_id = 'AC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), 7, 6));

    try {
        // Save order into DB
        $sql = "INSERT INTO payments 
                (user_id, order_id, service_name, quantity, instructions, amount, payment_method, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, 'cod', 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id, $order_id, $service_name, $quantity, $instructions, $amount]);

        // Send email acknowledgement
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'aparajitacomputers.shop@gmail.com'; // replace with your email
            $mail->Password   = 'YOUR_APP_PASSWORD';                 // use Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('aparajitacomputers.shop@gmail.com', 'Aparajita Computers');
            $mail->addAddress($customer_email);

            $mail->isHTML(true);
            $mail->Subject = "Order Acknowledgement - {$order_id}";
            $mail->Body = "
                <h2>✅ Order Acknowledgement</h2>
                <p>Dear Customer,</p>
                <p>Your order has been received successfully.</p>
                <ul>
                    <li><strong>Order ID:</strong> {$order_id}</li>
                    <li><strong>Service:</strong> ".htmlspecialchars($service_name)."</li>
                    <li><strong>Quantity:</strong> {$quantity}</li>
                    <li><strong>Amount:</strong> ₹".number_format($amount, 2)."</li>
                    <li><strong>Payment Method:</strong> Cash on Delivery (COD)</li>
                    <li><strong>Status:</strong> Pending</li>
                </ul>
                <p>You can now upload your related files.</p>
            ";
            $mail->send();
        } catch (Exception $e) {
            error_log("Email failed: ".$mail->ErrorInfo);
        }

        // Redirect to success page with order_id
        header("Location: success.php?order_id={$order_id}");
        exit;

    } catch (PDOException $e) {
        error_log("Database error: ".$e->getMessage());
        header("Location: payment.php?error=Could not place your order. Please try again.");
        exit;
    }

} else {
    header("Location: index.html");
    exit;
}
