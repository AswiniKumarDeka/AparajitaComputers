<?php
// place_order.php (Corrected Version with Instructions)
session_start();
require 'db_connect.php';

// Require PHPMailer for sending emails
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Security Check: Ensure user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get order details from the form
    $user_id = $_SESSION['id'];
    $service_name = $_POST['service_name'];
    $quantity = intval($_POST['quantity']);
    $amount = floatval($_POST['amount']);
    $customer_email = $_POST['customer_email'];
    $instructions = trim($_POST['instructions']); // Get instructions from the form

    // Generate a unique, human-readable Order ID
    $order_id = 'AC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), 7, 6));

    // --- Save the COD order to the database ---
    // UPDATED: This query now includes the new 'instructions' column.
    $stmt = $conn->prepare("INSERT INTO payments (user_id, order_id, service_name, instructions, amount, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, 'cod', 'pending')");
    $stmt->bind_param("isssds", $user_id, $order_id, $service_name, $instructions, $amount);
    
    if ($stmt->execute()) {
        // --- Order saved, now send confirmation email ---
        $mail = new PHPMailer(true);
        try {
            // SMTP Server Settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your-email@gmail.com'; // Your Gmail address
            $mail->Password   = 'your-gmail-app-password'; // Your Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Recipients
            $mail->setFrom('your-email@gmail.com', 'Aparajita Computers');
            $mail->addAddress($customer_email);

            // Email Content (Order Confirmation Invoice)
            $mail->isHTML(true);
            $mail->Subject = 'Order Confirmation - Aparajita Computers (Order ID: ' . $order_id . ')';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                    <h2>Your Order has been Confirmed!</h2>
                    <p>Hi there,</p>
                    <p>Thank you for placing your order with Aparajita Computers. Your order details are below. Please keep this email as your invoice.</p>
                    <h3>Invoice / Order Details:</h3>
                    <ul>
                        <li><strong>Order ID:</strong> {$order_id}</li>
                        <li><strong>Service:</strong> {$service_name}</li>
                        <li><strong>Quantity:</strong> {$quantity}</li>
                        <li><strong>Instructions:</strong> " . (!empty($instructions) ? htmlspecialchars($instructions) : 'None') . "</li>
                        <li><strong>Total Amount:</strong> ₹{$amount}</li>
                        <li><strong>Payment Method:</strong> Cash on Delivery (COD)</li>
                        <li><strong>Status:</strong> Payment Pending</li>
                    </ul>
                    <p>We will process your request and you can pay upon delivery/collection. Thank you for your business!</p>
                    <p><strong>Aparajita Computers</strong></p>
                </div>
            ";
            
            $mail->send();
        } catch (Exception $e) {
            // Log email error but don't stop the process
            error_log("COD confirmation email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }

        // --- Redirect to a success page with a COD message ---
        header('Location: success.html?type=cod&order_id=' . $order_id);
        exit;

    } else {
        // Database error
        header('Location: payment.php?error=Could not place your order. Please try again.');
        exit;
    }
} else {
    header("Location: index.html");
    exit;
}
?>
