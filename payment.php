<?php
session_start();

// Redirect to login if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login first");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Aparajita Computers</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        .container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.2); }
        label { font-weight: bold; display: block; margin-top: 10px; }
        input, textarea, select { width: 100%; padding: 8px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc; }
        button { background: #0891b2; color: white; padding: 12px; border: none; border-radius: 5px; margin-top: 15px; cursor: pointer; font-size: 16px; width: 100%; }
        button:hover { background: #0e7490; }
        .error { color: red; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Complete Your Order</h2>

        <!-- Show error if redirected with ?error -->
        <?php if (isset($_GET['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <form method="POST" action="place_order.php">
            <!-- Service Name -->
            <label for="service_name">Service Name</label>
            <input type="text" name="service_name" id="service_name" value="Computer Repair" required>

            <!-- Quantity -->
            <label for="quantity">Quantity</label>
            <input type="number" name="quantity" id="quantity" value="1" min="1" required>

            <!-- Amount -->
            <label for="amount">Amount (₹)</label>
            <input type="number" name="amount" id="amount" value="500" min="1" required>

            <!-- Customer Email -->
            <label for="customer_email">Your Email</label>
            <input type="email" name="customer_email" id="customer_email"
                   value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" 
                   placeholder="you@example.com" required>

            <!-- Instructions -->
            <label for="instructions">Special Instructions</label>
            <textarea name="instructions" id="instructions" rows="4" placeholder="Enter any additional details..."></textarea>

            <!-- Payment Method -->
            <label for="payment_method">Payment Method</label>
            <select name="payment_method" id="payment_method" required>
                <option value="cod">Cash on Delivery (COD)</option>
                <option value="razorpay">Pay Online (Razorpay)</option>
            </select>

            <!-- Submit -->
            <button type="submit">Place Order</button>
        </form>
    </div>
</body>
</html>
