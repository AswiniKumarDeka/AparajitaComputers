<?php
session_start();
require 'db_connect.php';

// --- Security Check: Redirect if not logged in ---
if (empty($_SESSION['user_id'])) {
    header("Location: login.html?error=Please+login+to+place+an+order");
    exit;
}

// --- Data Validation: Ensure data is coming from the form POST ---
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['service_name'])) {
    // If accessed directly or without data, redirect home.
    header("Location: index.html?error=Invalid+service+selection");
    exit;
}

// --- Get Service Info from POST ---
$service_name = $_POST['service_name'];
$unit_price   = isset($_POST['unit_price']) ? floatval($_POST['unit_price']) : 0;
$quantity     = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($unit_price <= 0 || $quantity <= 0) {
    header("Location: index.html?error=Invalid+price+or+quantity");
    exit;
}

// --- Fetch Customer Email ---
// We prioritize getting the most up-to-date email from the database.
$customer_email = '';
try {
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $customer_email = $user['email'];
    }
} catch (PDOException $e) {
    error_log("Failed to fetch user email: " . $e->getMessage());
    // Continue with a blank email, but log the error.
}


// --- Prepare variables for display ---
$total_amount = $unit_price * $quantity;
$service_name_esc = htmlspecialchars($service_name, ENT_QUOTES);
$customer_email_esc = htmlspecialchars($customer_email, ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Confirm Your Order</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
<style>
    body { font-family: "Poppins", sans-serif; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtyEl = document.getElementById('quantity');
    const price = parseFloat(document.getElementById('unit_price').value);
    const totalEl = document.getElementById('total_amount_display');
    const hiddenAmountEl = document.getElementById('hidden_total_amount');

    function updateTotal() {
        let q = parseInt(qtyEl.value, 10) || 1;
        if (q < 1) {
            q = 1;
            qtyEl.value = 1;
        }
        const total = q * price;
        totalEl.textContent = "₹" + total.toFixed(2);
        hiddenAmountEl.value = total.toFixed(2);
    }

    qtyEl.addEventListener('input', updateTotal);
    // Initial calculation on page load
    updateTotal();
});
</script>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen p-4">
<div class="max-w-lg w-full bg-gray-800 rounded-2xl shadow-2xl p-8 space-y-6">
    <h1 class="text-3xl font-bold text-center text-cyan-400">Confirm Your Order Details</h1>

    <!-- Display any errors from the payment page -->
    <?php if (isset($_GET['error'])): ?>
        <div class="p-4 bg-red-500/20 text-red-400 rounded-md text-center">
            <p><?php echo htmlspecialchars($_GET['error']); ?></p>
        </div>
    <?php endif; ?>

    <form action="payment.php" method="POST" class="space-y-5">
        <!-- Hidden fields to pass all data to the final payment script -->
        <input type="hidden" name="service_name" value="<?php echo $service_name_esc; ?>">
        <input type="hidden" id="unit_price" value="<?php echo $unit_price; ?>">
        <input type="hidden" id="hidden_total_amount" name="amount" value="<?php echo $total_amount; ?>">

        <!-- Service Summary -->
        <div class="border-b border-gray-700 pb-4">
            <label class="block text-sm font-medium text-gray-400">Service</label>
            <p class="mt-1 text-lg font-semibold text-white"><?php echo $service_name_esc; ?></p>
        </div>

        <!-- Quantity -->
        <div>
            <label for="quantity" class="block text-sm font-medium text-gray-400">Quantity</label>
            <input type="number" id="quantity" name="quantity" value="<?php echo $quantity; ?>" min="1"
                   class="mt-1 w-full rounded-lg bg-gray-700 border-gray-600 p-3 text-white focus:ring-cyan-500 focus:border-cyan-500">
        </div>

        <!-- Total Amount -->
        <div class="text-center bg-gray-900 p-4 rounded-lg">
            <label class="block text-sm font-medium text-gray-400">Total Amount</label>
            <p id="total_amount_display" class="text-3xl font-bold text-cyan-400">₹<?php echo number_format($total_amount, 2); ?></p>
        </div>

        <!-- Email -->
        <div>
            <label for="customer_email" class="block text-sm font-medium text-gray-400">Confirm Your Email</label>
            <input type="email" id="customer_email" name="customer_email" value="<?php echo $customer_email_esc; ?>" required
                   class="mt-1 w-full rounded-lg bg-gray-700 border-gray-600 p-3 text-white focus:ring-cyan-500 focus:border-cyan-500">
        </div>

        <!-- Instructions -->
        <div>
            <label for="instructions" class="block text-sm font-medium text-gray-400">Special Instructions (Optional)</label>
            <textarea id="instructions" name="instructions" rows="3" placeholder="e.g., 'Please print 10 copies in color'" class="mt-1 w-full rounded-lg bg-gray-700 border-gray-600 p-3 text-white focus:ring-cyan-500 focus:border-cyan-500"></textarea>
        </div>

        <!-- Payment Method -->
        <div>
            <label for="payment_method" class="block text-sm font-medium text-gray-400">Payment Method</label>
            <select id="payment_method" name="payment_method" class="mt-1 w-full rounded-lg bg-gray-700 border-gray-600 p-3 text-white focus:ring-cyan-500 focus:border-cyan-500">
                <option value="cod">Cash on Delivery (COD)</option>
                <option value="razorpay">Pay Online (Secure)</option>
            </select>
        </div>

        <!-- Submit Button -->
        <div class="text-center pt-4">
            <button type="submit"
                    class="w-full bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-3 px-6 rounded-lg transition-transform hover:scale-105 duration-300">
                Proceed to Payment
            </button>
        </div>
    </form>
</div>
</body>
</html>

