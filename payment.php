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

// Insert order
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

// COD → success page
if ($payment_method === "cod") {
    header("Location: success.php?order_id=" . urlencode($order_id));
    exit;
}

// Razorpay → checkout
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pay with Razorpay</title>
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
  <script>
    var options = {
        "key": "rzp_test_BH9Fl1mKCz2rf8",
        "amount": "<?php echo intval($amount * 100); ?>", 
        "currency": "INR",
        "name": "Aparajita Computers",
        "description": "Payment for <?php echo htmlspecialchars($service_name); ?>",
        "order_id": "<?php echo $order_id; ?>", 
        "handler": function (response){
            // Send details to verify_payment.php
            var form = document.createElement("form");
            form.method = "POST";
            form.action = "verify_payment.php";

            var fields = {
              razorpay_payment_id: response.razorpay_payment_id,
              razorpay_signature: response.razorpay_signature,
              order_id: "<?php echo $order_id; ?>"
            };

            for (var key in fields) {
              var input = document.createElement("input");
              input.type = "hidden";
              input.name = key;
              input.value = fields[key];
              form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        },
        "prefill": {
            "email": "<?php echo htmlspecialchars($customer_email); ?>"
        },
        "theme": {
            "color": "#14b8a6"
        }
    };
    var rzp = new Razorpay(options);
    rzp.open();
  </script>
</body>
</html>
