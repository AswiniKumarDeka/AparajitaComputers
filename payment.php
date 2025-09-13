<?php
session_start();
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html?error=Invalid+request");
    exit;
}

// Check login
if (empty($_SESSION['user_id'])) {
    header("Location: login.html?error=Please+login+first");
    exit;
}

$user_id        = $_SESSION['user_id'];
$service_name   = $_POST['service_name'] ?? '';
$quantity       = intval($_POST['quantity'] ?? 1);
$amount         = floatval($_POST['amount'] ?? 0);
$customer_email = $_POST['customer_email'] ?? '';
$instructions   = $_POST['instructions'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';

if (empty($service_name) || $quantity < 1 || $amount <= 0) {
    header("Location: index.html?error=Invalid+order+details");
    exit;
}

// Insert order into database
$stmt = $conn->prepare("INSERT INTO orders 
    (user_id, service_name, quantity, amount, customer_email, instructions, payment_method, status, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

$status = ($payment_method === "cod") ? "confirmed" : "pending";

$stmt->execute([
    $user_id,
    $service_name,
    $quantity,
    $amount,
    $customer_email,
    $instructions,
    $payment_method,
    $status
]);

$order_id = $conn->lastInsertId();

if ($payment_method === "cod") {
    // Redirect directly to success page
    header("Location: success.php?order_id=" . urlencode($order_id));
    exit;
} else {
    // Razorpay Payment
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <title>Pay with Razorpay</title>
      <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    </head>
    <body>
      <h2>Redirecting to payment...</h2>
      <script>
        var options = {
            "key": "rzp_test_BH9Fl1mKCz2rf8", // replace with your Razorpay key
            "amount": "<?php echo intval($amount * 100); ?>", // amount in paise
            "currency": "INR",
            "name": "My Service",
            "description": "Payment for <?php echo htmlspecialchars($service_name); ?>",
            "order_id": "", 
            "handler": function (response){
                // ✅ After payment success, redirect to success.php
                window.location.href = "success.php?order_id=<?php echo $order_id; ?>";
            },
            "prefill": {
                "email": "<?php echo htmlspecialchars($customer_email); ?>"
            },
            "theme": {
                "color": "#3399cc"
            }
        };
        var rzp1 = new Razorpay(options);
        rzp1.open();
      </script>
    </body>
    </html>
    <?php
}
