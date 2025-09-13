<?php
session_start();
require 'db_connect.php';

// --- Security Check ---
if (empty($_SESSION['user_id'])) {
    header("Location: login.html?error=Session+expired.+Please+login+again.");
    exit;
}

// --- Validate Request ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit;
}

// --- Get All Order Data from Form ---
$user_id = $_SESSION['user_id'];
$service_name = $_POST['service_name'] ?? 'N/A';
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$customer_email = filter_var($_POST['customer_email'] ?? '', FILTER_VALIDATE_EMAIL);
$instructions = trim($_POST['instructions'] ?? "");
$payment_method = $_POST['payment_method'] ?? 'cod';

// --- Basic Validation ---
if ($quantity <= 0 || $amount <= 0 || !$customer_email) {
    header("Location: index.html?error=Invalid+order+details.");
    exit;
}

// --- Generate a unique Order ID ---
$order_id = 'AC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), 7, 6));

// --- Route to the Correct Payment Handler ---

// ===================================================================
//  HANDLER 1: CASH ON DELIVERY (COD)
// ===================================================================
if ($payment_method === 'cod') {
    try {
        $sql = "INSERT INTO payments 
                (user_id, order_id, service_name, quantity, instructions, amount, payment_method, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, 'cod', 'pending')";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id, $order_id, $service_name, $quantity, $instructions, $amount]);

        // You can add your email sending logic here if you wish

        // Redirect to a success page
        header("Location: success.html?type=cod&order_id={$order_id}");
        exit;

    } catch (PDOException $e) {
        error_log("COD Database Error: " . $e->getMessage());
        header("Location: place_order.php?error=Database+error.+Could+not+place+order.");
        exit;
    }
}

// ===================================================================
//  HANDLER 2: RAZORPAY ONLINE PAYMENT
// ===================================================================
elseif ($payment_method === 'razorpay') {
    require 'razorpay-php/Razorpay.php';
    use Razorpay\Api\Api;

    $keyId = 'rzp_test_BH9Fl1mKCz2rf8'; // Replace with your Key ID
    $keySecret = '0ivx7uoRuO6zWgeqkBNPylai'; // Replace with your Key Secret

    $api = new Api($keyId, $keySecret);
    $amount_in_paise = $amount * 100;

    $orderData = [
        'receipt'         => $order_id,
        'amount'          => $amount_in_paise,
        'currency'        => 'INR',
        'payment_capture' => 1
    ];

    try {
        $razorpayOrder = $api->order->create($orderData);
        $razorpayOrderId = $razorpayOrder['id'];
        $_SESSION['razorpay_order_id'] = $razorpayOrderId;

        // Store the pending order in our database BEFORE payment
        $sql = "INSERT INTO payments 
                (user_id, order_id, razorpay_order_id, service_name, quantity, instructions, amount, payment_method, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'razorpay', 'created')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id, $order_id, $razorpayOrderId, $service_name, $quantity, $instructions, $amount]);

        // Now, display the payment button to the user
        echo '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Redirecting to Payment...</title>
                <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
                <script src="https://cdn.tailwindcss.com"></script>
            </head>
            <body class="bg-gray-900 flex items-center justify-center min-h-screen">
                <div class="text-center text-white">
                    <p class="text-xl">Redirecting to our secure payment gateway...</p>
                    <p class="text-gray-400">Please do not refresh this page.</p>
                </div>
                <form name="razorpayform" action="verify_payment.php" method="POST">
                    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                    <input type="hidden" name="razorpay_signature" id="razorpay_signature">
                </form>
                <script>
                    var options = {
                        "key": "' . $keyId . '",
                        "amount": "' . $amount_in_paise . '",
                        "currency": "INR",
                        "name": "Aparajita Computers",
                        "description": "Order ID: ' . $order_id . '",
                        "order_id": "' . $razorpayOrderId . '",
                        "handler": function (response){
                            document.getElementById("razorpay_payment_id").value = response.razorpay_payment_id;
                            document.getElementById("razorpay_signature").value = response.razorpay_signature;
                            document.razorpayform.submit();
                        },
                        "prefill": {
                            "name": "' . htmlspecialchars($_SESSION['username'] ?? 'Customer') . '",
                            "email": "' . htmlspecialchars($customer_email) . '"
                        },
                        "theme": { "color": "#0891b2" }
                    };
                    var rzp1 = new Razorpay(options);
                    window.onload = function() {
                        rzp1.open();
                    };
                </script>
            </body>
            </html>';
        exit;

    } catch (Exception $e) {
        error_log("Razorpay Error: " . $e->getMessage());
        header("Location: place_order.php?error=Payment+gateway+error.+Please+try+again.");
        exit;
    }
}
?>
