<?php
session_start();
require 'db_connect.php';

// Check login
if (empty($_SESSION['user_id'])) {
    header("Location: login.html?error=Please login first");
    exit;
}

// Get service_id from URL
$service_id = intval($_GET['service_id'] ?? 0);
if ($service_id <= 0) {
    die("Invalid service selected.");
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
<html>
<head>
    <meta charset="UTF-8">
    <title>Complete Your Order</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f9f9f9; }
        .container { max-width: 500px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 10px; }
        h2 { text-align: center; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 20px; width: 100%; padding: 12px; background: teal; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #006666; }
    </style>
    <script>
        function updateAmount() {
            let qty = document.getElementById("quantity").value;
            let price = <?= $service['price']; ?>;
            document.getElementById("amount").value = (qty * price).toFixed(2);
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Complete Your Order</h2>
    <?php if (!empty($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>

    <form action="place_order.php" method="POST">
        <input type="hidden" name="service_id" value="<?= $service['id']; ?>">

        <label>Service</label>
        <input type="text" value="<?= htmlspecialchars($service['name']); ?>" readonly>

        <label>Quantity</label>
        <input type="number" id="quantity" name="quantity" value="1" min="1" onchange="updateAmount()" required>

        <label>Total Amount (₹)</label>
        <input type="text" id="amount" name="amount" value="<?= $service['price']; ?>" readonly>

        <label>Your Email</label>
        <input type="email" name="customer_email" required>

        <label>Special Instructions</label>
        <textarea name="instructions"></textarea>

        <label>Payment Method</label>
        <select name="payment_method">
            <option value="cod">Cash on Delivery (COD)</option>
        </select>

        <button type="submit">Place Order</button>
    </form>
</div>
</body>
</html>
