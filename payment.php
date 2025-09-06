<?php
session_start(); // ADD THIS LINE

// Security Check: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?error=You must be logged in to make a payment.");
    exit;
}
// payment.php (Full-Featured, Secure, and Corrected Version)

// --- STEP 1: Enhanced Error Reporting (for debugging) ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

// session_start();

// // --- STEP 2: CRUCIAL SECURITY CHECK ---
// // This guard is at the very top. If the user is not logged in,
// // the script stops here and redirects them. No other code will run.
// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
//     header("Location: login.html?error=Please log in to place an order.");
//     die("Redirecting to login page..."); 
// }

// --- If the script reaches this point, the user is confirmed to be logged in. ---

require 'db_connect.php'; 
require 'razorpay-php/Razorpay.php'; 

use Razorpay\Api\Api;
use Razorpay\Api\Errors\BadRequestError;

// --- Razorpay API Keys ---
// IMPORTANT: You MUST replace these with your actual keys from Razorpay.
$keyId = 'rzp_test_BH9Fl1mKCz2rf8'; 
$keySecret = '0ivx7uoRuO6zWgeqkBNPylai';

// --- Get data from URL ---
$service_name = isset($_GET['service']) ? htmlspecialchars($_GET['service']) : 'Unknown Service';
$base_price = isset($_GET['price']) ? floatval($_GET['price']) : 0.00;
$quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;

// --- Calculate Final Price ---
$final_price = $base_price * $quantity;
$amount_in_paise = $final_price * 100;

// --- STEP 3: Create Razorpay Order with Error Handling ---
$razorpayOrder = null;
$api_error = null;
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
    // This will catch the "Authentication failed" error gracefully
    $api_error = "Razorpay Error: " . $e->getMessage();
} catch (Exception $e) {
    $api_error = "An unexpected error occurred. Please contact support.";
}


// Get user details for prefill
$customer_name = $_SESSION['username']; 
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$customer_email = $user['email'];
$stmt->close();
$conn->close();

?>
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

        <?php if ($api_error): ?>
            <!-- Display a clear error message if Razorpay connection failed -->
            <div class="p-4 bg-red-500/20 text-red-400 rounded-md text-center">
                <h3 class="font-bold">Payment System Error!</h3>
                <p><?php echo htmlspecialchars($api_error); ?></p>
                <p class="mt-2 text-sm">Please check your API keys or contact support.</p>
            </div>
        <?php else: ?>
            <!-- Show payment options only if there was no error -->
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
        <?php endif; ?>

        <p class="text-center text-sm text-gray-400"><a href="index.html#services" class="hover:underline">&larr; Back to Services</a></p>
    </div>

    <?php if (!$api_error && $razorpayOrder): ?>
        <!-- Only include Razorpay scripts if the order was created successfully -->
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
    <?php endif; ?>
</body>
</html>
