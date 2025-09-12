<?php
// FILE 7 of 9: admin_orders.php
// ===================================================================
require 'admin_auth_check.php';
require 'db_connect.php';
$orders_result = $conn->query("
    SELECT 
        p.order_id, u.username, p.service_name, p.instructions, p.amount, 
        p.payment_method, p.payment_status
    FROM payments p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.payment_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Management - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <style> body { font-family: "Poppins", sans-serif; } </style>
</head>
<body class="bg-gray-900 text-white">
    <?php include 'admin_header.php'; ?>
    <main class="container mx-auto p-8">
        <h2 class="text-3xl font-bold mb-6">Order Management</h2>
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="p-4 font-semibold">Order ID</th><th class="p-4 font-semibold">User</th><th class="p-4 font-semibold">Service</th><th class="p-4 font-semibold">Instructions</th><th class="p-4 font-semibold">Method</th><th class="p-4 font-semibold">Status</th><th class="p-4 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = $orders_result->fetch_assoc()): ?>
                        <tr id="order-row-<?php echo htmlspecialchars($order['order_id']); ?>" class="border-b border-gray-700">
                            <td class="p-4 font-mono text-sm"><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td class="p-4 font-semibold"><?php echo htmlspecialchars($order['username']); ?></td>
                            <td class="p-4"><?php echo htmlspecialchars($order['service_name']); ?></td>
                            <td class="p-4 text-gray-400 text-sm"><?php echo !empty($order['instructions']) ? htmlspecialchars($order['instructions']) : 'N/A'; ?></td>
                            <td class="p-4"><span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $order['payment_method'] === 'cod' ? 'bg-blue-500 text-blue-100' : 'bg-green-500 text-green-100'; ?>"><?php echo htmlspecialchars($order['payment_method']); ?></span></td>
                            <td class="p-4 status-cell"><span class="font-bold <?php echo $order['payment_status'] === 'completed' || $order['payment_status'] === 'success' ? 'text-green-400' : 'text-yellow-400'; ?>"><?php echo htmlspecialchars($order['payment_status']); ?></span></td>
                            <td class="p-4 action-cell">
                                <?php if ($order['payment_status'] === 'pending'): ?>
                                    <button class="action-btn text-green-400 hover:underline" data-action="complete_order" data-id="<?php echo htmlspecialchars($order['order_id']); ?>">Mark as Complete</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script src="admin_script.js"></script>
</body>
</html>
