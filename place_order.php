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

// --- CORRECTED: Security Check using the right session variable ---
if (empty($_SESSION['user_id'])) {
    // If user_id is not set, they are not logged in.
    header("Location: login.html?error=You must be logged in to place an order.");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // --- CORRECTED: Get user ID from the correct session variable ---
    $user_id = $_SESSION['user_id']; 
    $service_name = $_POST['service_name'] ?? 'N/A';
    $quantity = intval($_POST['quantity'] ?? 1);
    $amount = floatval($_POST['amount'] ?? 0);
    $customer_email = filter_var($_POST['customer_email'] ?? '', FILTER_VALIDATE_EMAIL);
    $instructions = trim($_POST['instructions'] ?? "");

    // Basic validation
    if (empty($service_name) || $amount <= 0 || !$customer_email) {
        header("Location: payment.php?error=Invalid order data. Please try again.");
        exit;
    }

    // Generate a unique Order ID
    $order_id = 'AC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), 7, 6));

    try {
        // --- CORRECTED: Save COD order using PDO for PostgreSQL ---
        $sql = "INSERT INTO payments 
                (user_id, order_id, service_name, quantity, instructions, amount, payment_method, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, 'cod', 'pending')";
        
        $stmt = $conn->prepare($sql);
        // Parameters must match the order of the question marks
        $stmt->execute([$user_id, $order_id, $service_name, $quantity, $instructions, $amount]);

        // --- Send acknowledgement email ---
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            // --- CORRECTED: Username is your full Gmail address ---
            $mail->Username   = 'aparajitacomputers.shop@gmail.com'; 
            // --- IMPORTANT: Use a Google App Password here, not your regular password ---
            $mail->Password   = 'Aparajita$$1993'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('aparajitacomputers.shop@gmail.com', 'Aparajita Computers');
            $mail->addAddress($customer_email);

            $mail->isHTML(true);
            $mail->Subject = "Order Acknowledgement - Order ID {$order_id}";
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h2 style='color: #0891b2;'>✅ Order Acknowledgement</h2>
                    <p>Dear Customer,</p>
                    <p>We have successfully received your order. We will contact you shortly regarding delivery and payment.</p>
                    <h3 style='border-bottom: 2px solid #eee; padding-bottom: 5px;'>Order Details:</h3>
                    <ul style='list-style: none; padding: 0;'>
                        <li style='margin-bottom: 10px;'><strong>Order ID:</strong> {$order_id}</li>
                        <li style='margin-bottom: 10px;'><strong>Service:</strong> " . htmlspecialchars($service_name) . "</li>
                        <li style='margin-bottom: 10px;'><strong>Quantity:</strong> {$quantity}</li>
                        <li style='margin-bottom: 10px;'><strong>Instructions:</strong> " . (!empty($instructions) ? htmlspecialchars($instructions) : 'None') . "</li>
                        <li style='margin-bottom: 10px;'><strong>Total Amount:</strong> ₹" . number_format($amount, 2) . "</li>
                        <li style='margin-bottom: 10px;'><strong>Payment Method:</strong> Cash on Delivery (COD)</li>
                        <li style='margin-bottom: 10px;'><strong>Status:</strong> Payment Pending</li>
                    </ul>
                    <p><strong>Thank you for choosing Aparajita Computers!</strong></p>
                </div>
            ";
            $mail->send();
        } catch (Exception $e) {
            // Log the email error but don't stop the user from seeing the success page
            error_log("COD acknowledgement email failed for {$customer_email}: {$mail->ErrorInfo}");
        }

        // Redirect to a success page
        header("Location: success.html?type=cod&order_id={$order_id}");
        exit;

    } catch (PDOException $e) {
        // Log the database error
        error_log("Database error placing COD order: " . $e->getMessage());
        // Redirect the user to an error page
        header("Location: payment.php?error=Could not place your order due to a database error. Please try again.");
        exit;
    }

} else {
    // If the page is accessed without a POST request, redirect to the homepage
    header("Location: index.html");
    exit;
}
?>
