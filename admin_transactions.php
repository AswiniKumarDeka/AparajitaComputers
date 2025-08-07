<?php
// admin_transactions.php
session_start();
require 'admin_auth_check.php';
require 'db_connect.php';

// --- Fetch only successful online transactions ---
$sql = "SELECT order_id, service_name, amount, payment_method, payment_date 
        FROM payments 
        WHERE payment_method = 'razorpay' AND payment_status = 'success' 
        ORDER BY payment_date DESC";
$transactions_result = $conn->query($sql);

// --- Calculate Total Online Revenue ---
$total_revenue = 0;
if ($transactions_result->num_rows > 0) {
    // Create a separate query to sum the amounts
    $revenue_result = $conn->query("SELECT SUM(amount) as total FROM payments WHERE payment_method = 'razorpay' AND payment_status = 'success'");
    $revenue_row = $revenue_result->fetch_assoc();
    $total_revenue = $revenue_row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Online Transactions - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <style> body { font-family: "Poppins", sans-serif; } </style>
</head>
<body class="bg-gray-900 text-white">
    <?php include 'admin_header.php'; ?>
    <main class="container mx-auto p-8">
        <h2 class="text-3xl font-bold mb-6">Online Transactions</h2>

        <!-- Total Revenue Card -->
        <div class="bg-green-800/50 border border-green-600 p-6 rounded-lg shadow-lg mb-8 max-w-sm">
            <h3 class="text-lg font-semibold text-green-300">Total Online Revenue</h3>
            <p class="text-4xl font-bold text-white mt-2">₹<?php echo number_format($total_revenue, 2); ?></p>
        </div>

        <div class="bg-gray-800 rounded-lg shadow-lg overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="p-4 font-semibold">Order ID</th>
                        <th class="p-4 font-semibold">Service</th>
                        <th class="p-4 font-semibold">Amount</th>
                        <th class="p-4 font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($transactions_result->num_rows > 0): ?>
                        <?php 
                        // Reset pointer to the beginning of the results to loop again
                        $transactions_result->data_seek(0); 
                        ?>
                        <?php while($transaction = $transactions_result->fetch_assoc()): ?>
                            <tr class="border-b border-gray-700 hover:bg-gray-700/50 transition-colors">
                                <td class="p-4 font-mono text-sm"><?php echo htmlspecialchars($transaction['order_id']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($transaction['service_name']); ?></td>
                                <td class="p-4 font-bold">₹<?php echo htmlspecialchars($transaction['amount']); ?></td>
                                <td class="p-4 text-sm text-gray-400"><?php echo date("d M Y, h:i A", strtotime($transaction['payment_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center p-8 text-gray-500">No online transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
