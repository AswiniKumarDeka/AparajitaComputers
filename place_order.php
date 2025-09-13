<?php
session_start();
require 'db_connect.php';

// Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header("Location: login.html?error=Please+login+first");
    exit;
}

// Get service info from POST
$service_name = $_POST['service_name'] ?? null;
$unit_price   = isset($_POST['unit_price']) ? floatval($_POST['unit_price']) : 0;
$quantity     = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if (!$service_name || $unit_price <= 0) {
    header("Location: index.html?error=Invalid+service+selected");
    exit;
}

// Store service info in session for payment page
$_SESSION['service_name'] = $service_name;
$_SESSION['unit_price']   = $unit_price;
$_SESSION['quantity']     = $quantity;

// Get customer email
$customer_email = $_SESSION['email'] ?? '';
if (empty($customer_email)) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE id=? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) $customer_email = $row['email'];
}

// Escape for HTML output
$service_name_esc = htmlspecialchars($service_name, ENT_QUOTES);
$customer_email_esc = htmlspecialchars($customer_email, ENT_QUOTES);
$total_amount = $unit_price * $quantity;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Complete Your Order</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtyEl = document.getElementById('quantity');
    const price = parseFloat(document.getElementById('unit_price').value);
    const totalEl = document.getElementById('total_amount');
    const hiddenAmount = document.getElementById('hidden_amount');

    function updateTotal() {
        let q = parseInt(qtyEl.value) || 1;
        if (q < 1) q = 1;
        const total = q * price;
        totalEl.textContent = "₹" + total.toFixed(2);
        hiddenAmount.value = total.toFixed(2);
    }

    qtyEl.addEventListener('input', updateTotal);
    updateTotal();
});
</script>
</head>
<body class="bg-gradient-to-r from-indigo-100 via-purple-100 to-pink-100 flex items-center justify-center min-h-screen">
<div class="max-w-lg w-full bg-white rounded-2xl shadow-2xl p-8">
<h1 class="text-3xl font-extrabold text-center text-indigo-600 mb-6">Complete Your Order</h1>

<form action="payment.php" method="POST" class="space-y-5">
    <input type="hidden" name="service_name" value="<?php echo $service_name_esc; ?>">
    <input type="hidden" id="unit_price" value="<?php echo $unit_price; ?>">
    <input type="hidden" id="hidden_amount" name="amount" value="<?php echo $total_amount; ?>">

    <!-- Service -->
    <div>
        <label class="block text-sm font-medium text-gray-600">Service</label>
        <p class="mt-1 font-semibold"><?php echo $service_name_esc; ?></p>
    </div>

    <!-- Quantity -->
    <div>
        <label class="block text-sm font-medium text-gray-600">Quantity</label>
        <input type="number" id="quantity" name="quantity" value="<?php echo $quantity; ?>" min="1"
            class="mt-1 w-full rounded-lg border-gray-300 p-2">
    </div>

    <!-- Total Amount -->
    <div>
        <label class="block text-sm font-medium text-gray-600">Total Amount</label>
        <p id="total_amount" class="text-xl font-bold text-indigo-700">₹<?php echo $total_amount; ?></p>
    </div>

    <!-- Email -->
    <div>
        <label class="block text-sm font-medium text-gray-600">Your Email</label>
        <input type="email" name="customer_email" value="<?php echo $customer_email_esc; ?>" required
            class="mt-1 w-full rounded-lg border-gray-300 p-2">
    </div>

    <!-- Instructions -->
    <div>
        <label class="block text-sm font-medium text-gray-600">Special Instructions</label>
        <textarea name="instructions" rows="3" placeholder="Any details..." class="mt-1 w-full rounded-lg border-gray-300 p-2"></textarea>
    </div>

    <!-- Payment Method -->
    <div>
        <label class="block text-sm font-medium text-gray-600">Payment Method</label>
        <select name="payment_method" class="mt-1 w-full rounded-lg border-gray-300 p-2">
            <option value="cod">Cash on Delivery (COD)</option>
            <option value="razorpay">Pay Online (Razorpay)</option>
        </select>
    </div>

    <!-- Submit -->
    <div class="text-center">
        <button type="submit"
            class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-lg">🚀 Place Order</button>
    </div>
</form>
</div>
</body>
</html>
