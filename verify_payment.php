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
    
    // --- Razorpay API Keys (replace with your keys) ---
    $keyId = 'rzp_test_BH9Fl1mKCz2rf8';
    $keySecret = '0ivx7uoRuO6zWgeqkBNPylai';
    $api = new Api($keyId, $keySecret);

    $razorpay_order_id = $_SESSION['razorpay_order_id'];
    $razorpay_payment_id = $_POST['razorpay_payment_id'];
    $razorpay_signature = $_POST['razorpay_signature'];

    // --- Verify the Payment Signature ---
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
    // --- Fetch payment details ---
    $payment = $api->payment->fetch($razorpay_payment_id);
    $service_name = $payment['notes']['service'];
    $amount = $payment['amount'] / 100; // convert paise to INR
    $customer_email = $payment['email'];
    $user_id = $_SESSION['id'];

    // --- Save payment in database ---
    $stmt = $conn->prepare("INSERT INTO payments (user_id, razorpay_payment_id, razorpay_order_id, razorpay_signature, service_name, amount) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssd", $user_id, $razorpay_payment_id, $razorpay_order_id, $razorpay_signature, $service_name, $amount);
    $stmt->execute();
    $stmt->close();

    // --- Send Email Receipt to Customer + Acknowledgement Copy to Admin ---
    $mail = new PHPMailer(true);
    try {
        // SMTP settings (for Gmail)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'aparajitacomputers.shop@gmail.com';  // your Gmail
        $mail->Password   = 'Aparajita$$1993';             // App Password (not normal Gmail password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Sender & Recipient
        $mail->setFrom('aparajitacomputers.shop@gmail.com', 'Aparajita Computers');
        $mail->addAddress($customer_email);          // customer
        $mail->addBCC('aparajitacomputers.shop@gmail.com'); // copy for you (acknowledgement)

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = 'Your E-Receipt from Aparajita Computers';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h2>Payment Successful ✅</h2>
                <p>Dear Customer,</p>
                <p>Thank you for your payment. Below are your transaction details:</p>
                <h3>Order Receipt:</h3>
                <ul>
                    <li><strong>Service:</strong> {$service_name}</li>
                    <li><strong>Amount Paid:</strong> ₹{$amount}</li>
                    <li><strong>Payment ID:</strong> {$razorpay_payment_id}</li>
                    <li><strong>Date:</strong> " . date('d M Y, h:i A') . "</li>
                </ul>
                <p>We have also sent an acknowledgement copy to Aparajita Computers.</p>
                <br>
                <p><strong>Aparajita Computers</strong></p>
            </div>
        ";
        $mail->AltBody = "Payment Successful. Service: {$service_name}, Amount: ₹{$amount}, Payment ID: {$razorpay_payment_id}.";

        $mail->send();

        // Redirect to success page
        header('Location: success.html');
        exit;

    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        header('Location: success.html?email_error=1');
        exit;
    }
} else {
    // Payment failed
    header('Location: payment.php?error=' . urlencode($error));
    exit;
}
?>
