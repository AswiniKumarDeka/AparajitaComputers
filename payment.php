<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



session_start();

// --- STEP 1: Error reporting (for debugging only, remove on production) ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- STEP 2: Security check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?error=You must be logged in to make a payment.");
    exit;
}

// --- STEP 3: Include dependencies ---
require 'db_connect.php'; 
require 'razorpay-php/Razorpay.php'; 

use Razorpay\Api\Api;
use Razorpay\Api\Errors\BadRequestError;

// --- Razorpay API Keys (replace with your real keys) ---
$keyId = 'rzp_test_BH9Fl1mKCz2rf8'; 
$keySecret = '0ivx7uoRuO6zWgeqkBNPylai';

// --- STEP 4: Get data from URL safely ---
$service_name = isset($_GET['service']) ? htmlspecialchars($_GET['service']) : 'Unknown Service';
$base_price   = isset($_GET['price']) ? floatval($_GET['price']) : 0.00;
$quantity     = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;

if ($base_price <= 0 || $quantity <= 0) {
    die("Invalid price or quantity.");
}

$final_price      = $base_price * $quantity;
$amount_in_paise  = $final_price * 100;

// --- STEP 5: Create Razorpay order ---
$razorpayOrder = null;
$api_error     = null;

try {
    $api = new Api($keyId, $keySecret);
    $orderData = [
        'receipt'         => 'rcptid_' . uniqid(),
        'amount'          => $amount_in_paise,
        'currency'        => 'INR',
        'payment_capture' => 1
    ];
    $razorpayOrder = $api->order->create($orderData);
    $_SESSION['razorpay_order_id'] = $razorpayOrder['id'];

} catch (BadRequestError $e) {
    $api_error = "Razorpay Error: " . $e->getMessage();
} catch (Exception $e) {
    $api_error = "Unexpected error: " . $e->getMessage();
}

// --- STEP 6: Fetch user details from DB ---
// --- STEP 6: Fetch user details from DB (PDO Version) ---
$customer_name  = isset($_SESSION['username']) ? $_SESSION['username'] : "Customer";
$customer_email = "unknown@example.com";

if (!empty($_SESSION['user_id'])) {
    // Prepare the statement using the PDO $conn object
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");

    // Execute the statement, passing parameters as an array
    $stmt->execute([$_SESSION['user_id']]);

    // Fetch the user data
    $user = $stmt->fetch(); // No need for get_result()->fetch_assoc()

    if ($user) {
        $customer_email = $user['email'];
    }
}

// Close the connection by setting the object to null
$conn = null;
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Complete Your Order - <?php echo $service_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <style> body { font-family: "Poppins", sans-serif; } </style>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
    <div class="w-full max-w-lg p-8 space-y-6 bg-gray-800 rounded-2xl shadow-lg">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-cyan-400">Complete Your Order</h1>
        </div>

        <?php if ($api_error) { ?>
            <div class="p-4 bg-red-500/20 text-red-400 rounded-md text-center">
                <h3 class="font-bold">Payment System Error!</h3>
                <p><?php echo htmlspecialchars($api_error); ?></p>
                <p class="mt-2 text-sm">Please check your API keys or contact support.</p>
            </div>
        <?php } else { ?>
            <div class="border-y border-gray-700 py-6 space-y-4">
                <div class="flex justify-between items-center"><span class="text-gray-300 font-semibold">Service:</span><span class="text-lg font-bold"><?php echo $service_name; ?></span></div>
                <div class="flex justify-between items-center"><span class="text-gray-300 font-semibold">Quantity:</span><span class="text-lg font-bold">x <?php echo $quantity; ?></span></div>
                <div class="flex justify-between items-center"><span class="text-gray-300 font-semibold">Amount to Pay:</span><span class="text-2xl font-bold text-cyan-400">₹<?php echo number_format($final_price, 2); ?></span></div>
            </div>

            <div class="text-center space-y-4">
                <h3 class="text-lg font-semibold text-gray-300">Choose Payment Method</h3>
                <button id="rzp-button1" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-md transition-colors">Pay Online Securely</button>
                <div class="flex items-center"><div class="flex-grow border-t border-gray-700"></div><span class="flex-shrink mx-4 text-gray-500">OR</span><div class="flex-grow border-t border-gray-700"></div></div>
                <form action="place_order.php" method="POST">
                    <input type="hidden" name="service_name" value="<?php echo $service_name; ?>">
                    <input type="hidden" name="quantity" value="<?php echo $quantity; ?>">
                    <input type="hidden" name="amount" value="<?php echo $final_price; ?>">
                    <input type="hidden" name="customer_email" value="<?php echo $customer_email; ?>">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-md transition-colors">Confirm Cash on Delivery</button>
                </form>
            </div>
        <?php } ?>

        <p class="text-center text-sm text-gray-400"><a href="index.html#services" class="hover:underline">&larr; Back to Services</a></p>
    </div>

    <?php if (!$api_error && $razorpayOrder) { ?>
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <form name='razorpayform' action="verify_payment.php" method="POST">
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_signature"  id="razorpay_signature">
        </form>
        <script>
        var options = {
            "key": "<?php echo $keyId; ?>",
            "amount": "<?php echo $amount_in_paise; ?>",
            "currency": "INR",
            "name": "Aparajita Computers",
            "description": "Payment for <?php echo $quantity; ?> x <?php echo $service_name; ?>",
            "order_id": "<?php echo $razorpayOrder['id']; ?>",
            "handler": function (response){
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_signature').value = response.razorpay_signature;
                document.razorpayform.submit();
            },
            "prefill": { "name": "<?php echo $customer_name; ?>", "email": "<?php echo $customer_email; ?>" },
            "notes": { "service": "<?php echo $service_name; ?>", "quantity": "<?php echo $quantity; ?>" },
            "theme": { "color": "#0891b2" }
        };
        var rzp1 = new Razorpay(options);
        document.getElementById('rzp-button1').onclick = function(e){ rzp1.open(); e.preventDefault(); }
        </script>
    <?php } ?>
</body>
</html>
