<?php
// payment.php — robust version that supports:
//  - ?service_id=123  (recommended, fetches price from DB)
//  - OR ?service=Name&price=123.45  (legacy fallback)
// Auto-calculates total and pre-fills customer email from session/DB.

session_start();
require 'db_connect.php'; // <-- must set $conn as a PDO instance

// ensure user is logged in
if (empty($_SESSION['user_id'])) {
    header("Location: login.html?error=Please+login+first");
    exit;
}

// determine service details
$service_name = null;
$unit_price = 0.0;
$service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;

if ($service_id > 0) {
    // try fetch from services table
    $stmt = $conn->prepare("SELECT id, name, price FROM services WHERE id = ? LIMIT 1");
    $stmt->execute([$service_id]);
    $svc = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($svc) {
        $service_name = $svc['name'];
        $unit_price = (float)$svc['price'];
    }
}

// fallback: legacy query params ?service=...&price=...
if ($service_name === null && isset($_GET['service'], $_GET['price'])) {
    $service_name = trim($_GET['service']);
    // cast price safely
    $unit_price = (float) str_replace(',', '', $_GET['price']);
    // note: we keep $service_id as 0 in this branch
}

if ($service_name === null || $unit_price <= 0) {
    // No valid service selected — redirect back to services list (or homepage)
    header("Location: index.html?error=Please+select+a+service");
    exit;
}

// get customer's email from session or DB
$customer_email = $_SESSION['email'] ?? '';

if (empty($customer_email)) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['email'])) {
        $customer_email = $row['email'];
    }
}

// prepare values for the form
$unit_price_js = json_encode($unit_price); // safe for JS
$service_name_escaped = htmlspecialchars($service_name, ENT_QUOTES);
$customer_email_escaped = htmlspecialchars($customer_email, ENT_QUOTES);
$service_id_field = $service_id > 0 ? intval($service_id) : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Complete Your Order — <?php echo $service_name_escaped; ?></title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<script>
/* JS handles dynamic total calculation */
document.addEventListener('DOMContentLoaded', function () {
    const unitPrice = <?php echo $unit_price_js; ?>;
    const qtyEl = document.getElementById('quantity');
    const amountHidden = document.getElementById('amount');
    const amountDisplay = document.getElementById('amount_display');

    function updateAmount() {
        let q = parseInt(qtyEl.value, 10);
        if (isNaN(q) || q < 1) q = 1;
        const total = (unitPrice * q);
        amountDisplay.textContent = '₹' + total.toFixed(2);
        amountHidden.value = total.toFixed(2);
    }

    // initialize
    updateAmount();

    // events
    qtyEl.addEventListener('input', updateAmount);
    qtyEl.addEventListener('change', updateAmount);
});
</script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
  <div class="w-full max-w-lg bg-white shadow-md rounded p-6">
    <h1 class="text-2xl font-bold mb-4">Complete Your Order</h1>

    <?php if (!empty($_GET['error'])): ?>
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <form action="place_order.php" method="POST" class="space-y-4">
      <!-- preserve service identity -->
      <?php if ($service_id_field !== ''): ?>
        <input type="hidden" name="service_id" value="<?php echo $service_id_field; ?>">
      <?php endif; ?>

      <!-- Always send service name and unit price for server-side safety -->
      <input type="hidden" name="service_name" value="<?php echo $service_name_escaped; ?>">
      <input type="hidden" id="unit_price" name="unit_price" value="<?php echo htmlspecialchars(number_format($unit_price,2,'.','')); ?>">

      <div>
        <label class="block text-sm font-medium text-gray-700">Service</label>
        <div class="mt-1"><?php echo $service_name_escaped; ?></div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Quantity</label>
        <input id="quantity" type="number" name="quantity" min="1" value="1" required
               class="mt-1 block w-full border-gray-300 rounded-md p-2">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Total Amount</label>
        <div id="amount_display" class="mt-1 text-lg font-semibold text-teal-600">₹0.00</div>
        <input type="hidden" id="amount" name="amount" value="">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Your Email</label>
        <input type="email" name="customer_email" value="<?php echo $customer_email_escaped; ?>" required
               class="mt-1 block w-full border-gray-300 rounded-md p-2" placeholder="you@example.com">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Special Instructions (optional)</label>
        <textarea name="instructions" rows="4" class="mt-1 block w-full border-gray-300 rounded-md p-2" placeholder="Any details..."></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
        <select name="payment_method" class="mt-1 block w-full border-gray-300 rounded-md p-2">
          <option value="cod">Cash on Delivery (COD)</option>
          <option value="razorpay">Pay Online (Razorpay)</option>
        </select>
      </div>

      <div>
        <button type="submit" class="w-full bg-teal-600 text-white py-2 rounded hover:bg-teal-700">Place Order</button>
      </div>
    </form>

    <p class="text-sm text-gray-500 mt-4">Unit price: ₹<?php echo number_format($unit_price,2); ?> — total updates automatically when you change quantity.</p>
  </div>
</body>
</html>
