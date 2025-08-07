<?php
// verify_payment.php
session_start();
require 'db_connect.php';

// Require the Razorpay and PHPMailer libraries
require 'razorpay-php/Razorpay.php';
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$success = false;
$error = "Payment Failed";

if (!empty($_POST['razorpay_payment_id'])) {
    
    // --- Get API Keys and Payment IDs ---
    $keyId = 'YOUR_KEY_ID'; // Replace with your Razorpay Key ID
    $keySecret = 'YOUR_KEY_SECRET'; // Replace with your Razorpay Key Secret
    $api = new Api($keyId, $keySecret);

    $razorpay_order_id = $_SESSION['razorpay_order_id'];
    $razorpay_payment_id = $_POST['razorpay_payment_id'];
    $razorpay_signature = $_POST['razorpay_signature'];

    // --- Verify the Payment Signature (CRUCIAL SECURITY STEP) ---
    try {
        $attributes = array(
            'razorpay_order_id' => $razorpay_order_id,
            'razorpay_payment_id' => $razorpay_payment_id,
            'razorpay_signature' => $razorpay_signature
        );
        $api->utility->verifyPaymentSignature($attributes);
        $success = true;
    } catch(SignatureVerificationError $e) {
        $success = false;
        $error = 'Razorpay Error : ' . $e->getMessage();
    }
}

if ($success === true) {
    // --- Payment is successful and verified ---

    // Fetch payment details from Razorpay API
    $payment = $api->payment->fetch($razorpay_payment_id);
    $service_name = $payment['notes']['service'];
    $amount = $payment['amount'] / 100; // Convert from paise to rupees
    $customer_email = $payment['email'];
    $user_id = $_SESSION['id'];

    // --- Save the successful transaction to your database ---
    $stmt = $conn->prepare("INSERT INTO payments (user_id, razorpay_payment_id, razorpay_order_id, razorpay_signature, service_name, amount) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssd", $user_id, $razorpay_payment_id, $razorpay_order_id, $razorpay_signature, $service_name, $amount);
    $stmt->execute();
    $stmt->close();

    // --- Send E-Receipt Email using PHPMailer ---
    $mail = new PHPMailer(true);
    try {
        // --- SMTP Server Settings (IMPORTANT: Use an App Password for Gmail) ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com'; // Your Gmail address
        $mail->Password   = 'your-gmail-app-password'; // Your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // --- Recipients ---
        $mail->setFrom('your-email@gmail.com', 'Aparajita Computers');
        $mail->addAddress($customer_email); // Add a recipient

        // --- Email Content ---
        $mail->isHTML(true);
        $mail->Subject = 'Your E-Receipt from Aparajita Computers';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h2>Thank you for your order!</h2>
                <p>Hi there,</p>
                <p>This is your e-receipt for your recent purchase from Aparajita Computers.</p>
                <h3>Order Details:</h3>
                <ul>
                    <li><strong>Service:</strong> {$service_name}</li>
                    <li><strong>Amount Paid:</strong> ₹{$amount}</li>
                    <li><strong>Payment ID:</strong> {$razorpay_payment_id}</li>
                    <li><strong>Date:</strong> " . date('d M Y, h:i A') . "</li>
                </ul>
                <p>We will process your request shortly. Thank you for your business!</p>
                <p><strong>Aparajita Computers</strong></p>
            </div>
        ";
        $mail->AltBody = "Thank you for your order! Service: {$service_name}, Amount Paid: INR {$amount}, Payment ID: {$razorpay_payment_id}.";

        $mail->send();
        
        // --- Redirect to a success page ---
        header('Location: success.html');
        exit;

    } catch (Exception $e) {
        // Email failed, but payment was successful. Log this error.
        // For now, we'll still redirect to success page as the payment is done.
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        header('Location: success.html?email_error=1');
        exit;
    }
} else {
    // Payment failed or signature was invalid
    header('Location: payment.php?error=' . urlencode($error));
    exit;
}
?>
