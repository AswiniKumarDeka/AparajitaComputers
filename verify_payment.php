<?php
// verify_payment.php
session_start();
require 'db_connect.php';

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
    $keyId = 'rzp_test_BH9Fl1mKCz2rf8'; 
    $keySecret = '0ivx7uoRuO6zWgeqkBNPylai';
    $api = new Api($keyId, $keySecret);

    $razorpay_order_id = $_SESSION['razorpay_order_id'];
    $razorpay_payment_id = $_POST['razorpay_payment_id'];
    $razorpay_signature = $_POST['razorpay_signature'];

    try {
        $attributes = [
            'razorpay_order_id' => $razorpay_order_id,
            'razorpay_payment_id' => $razorpay_payment_id,
            'razorpay_signature' => $razorpay_signature
        ];
        $api->utility->verifyPaymentSignature($attributes);
        $success = true;
    } catch(SignatureVerificationError $e) {
        $success = false;
        $error = 'Razorpay Error : ' . $e->getMessage();
    }
}

if ($success === true) {
    $payment = $api->payment->fetch($razorpay_payment_id);
    $service_name = $payment['notes']['service'];
    $amount = $payment['amount'] / 100; 
    $customer_email = $payment['email'];
    $user_id = $_SESSION['id'];

    // Generate unique Order ID
    $order_id = 'AC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), 7, 6));

    $stmt = $conn->prepare("INSERT INTO payments (user_id, order_id, razorpay_payment_id, razorpay_order_id, razorpay_signature, service_name, amount, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'razorpay', 'success')");
    $stmt->bind_param("isssssd", $user_id, $order_id, $razorpay_payment_id, $razorpay_order_id, $razorpay_signature, $service_name, $amount);
    $stmt->execute();
    $stmt->close();

    // Send acknowledgement email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com';
        $mail->Password   = 'your-gmail-app-password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('your-email@gmail.com', 'Aparajita Computers');
        $mail->addAddress($customer_email);

        $mail->isHTML(true);
        $mail->Subject = 'Payment Acknowledgement - Aparajita Computers (Order ID: ' . $order_id . ')';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h2>✅ Payment Acknowledgement</h2>
                <p>Dear Customer,</p>
                <p>We acknowledge receipt of your payment. Your order has been confirmed successfully.</p>
                <h3>Payment Receipt:</h3>
                <ul>
                    <li><strong>Order ID:</strong> {$order_id}</li>
                    <li><strong>Service:</strong> {$service_name}</li>
                    <li><strong>Amount Paid:</strong> ₹{$amount}</li>
                    <li><strong>Payment ID:</strong> {$razorpay_payment_id}</li>
                    <li><strong>Status:</strong> Success</li>
                    <li><strong>Date:</strong> " . date('d M Y, h:i A') . "</li>
                </ul>
                <p>Your order is now being processed. You will be notified when it is ready.</p>
                <p><strong>Thank you for your trust in Aparajita Computers!</strong></p>
            </div>
        ";
        $mail->send();

        header('Location: success.html?type=razorpay&order_id=' . $order_id);
        exit;
    } catch (Exception $e) {
        error_log("Razorpay acknowledgement email failed: {$mail->ErrorInfo}");
        header('Location: success.html?type=razorpay&order_id=' . $order_id . '&email_error=1');
        exit;
    }
} else {
    header('Location: payment.php?error=' . urlencode($error));
    exit;
}
?>
