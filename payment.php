<?php
session_start();
require 'db_connect.php';

// Check login
if (empty($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login first");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch logged-in user’s email
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$customer_email = $user ? $user['email'] : "";

// Get service ID from URL
$service_id = intval($_GET['service_id'] ?? 0);
if ($service_id <= 0) {
    die("Invalid service.");
}

// Fetch service details
$stmt = $conn->prepare("SELECT id, name, price FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    die("Service not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complete Your Order</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    function updateAmount() {
        let price = parseFloat(document.getElementById("unit_price").value);
        let qty = parseInt(document.getElementById("quantity").value);
        document.getElementById("amount").value = price * qty;
        document.getElementById("amount_display").innerText = "₹" + (price * qty);
    }
    </script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold mb-4">Complete Your Order</h2>

        <?php if (isset($_GET['error'])): ?>
            <p class="text-red-600 mb-4"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <form action="place_order.php" method="POST" class="space-y-4">
            <!-- Hidden fields -->
            <input type="hidden" name="service_name" value="<?php echo htmlspecialchars($service['name']); ?>">
            <input type="hidden" name="customer_email" value="<?php echo htmlspecialchars($customer_email); ?>">
            <input type="hidden" id="unit_price" value="<?php echo $service['price']; ?>">

            <!-- Service Name -->
            <div>
                <label class="block font-semibold">Service Name</label>
                <p><?php echo htmlspecialchars($service['name']); ?></p>
            </div>

            <!-- Quantity -->
            <div>
                <label class="block font-semibold">Quantity</label>
                <input type="number" name="quantity" id="quantity" value="1" min="1" 
                    onchange="updateAmount()" class="w-full p-2 border rounded">
            </div>

            <!-- Total Amount -->
            <div>
                <label class="block font-semibold">Total Amount</label>
                <p id="amount_display" class="text-lg font-bold text-blue-600">₹<?php echo $service['price']; ?></p>
                <input type="hidden" name="amount" id="amount" value="<?php echo $service['price']; ?>">
            </div>

            <!-- Instructions -->
            <div>
                <label class="block font-semibold">Special Instructions</label>
                <textarea name="instructions" class="w-full p-2 border rounded"></textarea>
            </div>

            <!-- Payment Method -->
            <div>
                <label class="block font-semibold">Payment Method</label>
                <select name="payment_method" class="w-full p-2 border rounded">
                    <option value="cod">Cash on Delivery (COD)</option>
                    <option value="razorpay">Online Payment (Razorpay)</option>
                </select>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Place Order
            </button>
        </form>
    </div>
</body>
</html>

