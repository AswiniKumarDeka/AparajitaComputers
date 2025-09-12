<?php
// admin_orders.php

// --- STEP 1: All PHP logic MUST come before any HTML output ---

// Start the session at the absolute beginning of the script.
session_start();

// --- Authentication Check ---
// Verify the user is logged in and is an admin. If not, redirect to the login page.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html?error=Access Denied");
    exit;
}

// --- Database Connection & Data Fetching ---
require 'db_connect.php';
$orders = []; // Initialize an empty array to hold the orders

try {
    // Use a prepared statement for security, although not strictly necessary for this query.
    // This is good practice.
    $stmt = $conn->prepare("
        SELECT 
            p.order_id, 
            u.username, 
            p.service_name, 
            p.instructions, 
            p.amount, 
            p.payment_method, 
            p.payment_status,
            p.quantity
        FROM payments p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.payment_date DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all results into an array
} catch (PDOException $e) {
    // In case of a database error, stop the script and show a friendly message.
    // In a real application, you would log this error.
    error_log("Admin Orders Page Error: " . $e->getMessage());
    die("Error: Could not fetch order data. Please check the server logs.");
}

// --- STEP 2: Now that all logic is done, you can start the HTML ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <style> body { font-family: "Poppins", sans-serif; } </style>
</head>
<body class="bg-gray-900 text-white">
    <?php include 'admin_header.php'; ?>
    <main class="container mx-auto p-4 sm:p-8">
        <h2 class="text-2xl sm:text-3xl font-bold mb-6">Order Management</h2>
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="p-4 font-semibold">Order ID</th>
                        <th class="p-4 font-semibold">User</th>
                        <th class="p-4 font-semibold">Service</th>
                        <th class="p-4 font-semibold hidden md:table-cell">Instructions</th>
                        <th class="p-4 font-semibold">Qty</th>
                        <th class="p-4 font-semibold">Method</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" class="text-center p-8 text-gray-500">No orders found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($orders as $order): ?>
                            <tr id="order-row-<?php echo htmlspecialchars($order['order_id']); ?>" class="border-b border-gray-700 hover:bg-gray-700/50">
                                <td class="p-4 font-mono text-sm"><?php echo htmlspecialchars($order['order_id']); ?></td>
                                <td class="p-4 font-semibold"><?php echo htmlspecialchars($order['username']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($order['service_name']); ?></td>
                                <td class="p-4 text-gray-400 text-sm hidden md:table-cell"><?php echo !empty($order['instructions']) ? htmlspecialchars($order['instructions']) : 'N/A'; ?></td>
                                <td class="p-4 font-bold"><?php echo htmlspecialchars($order['quantity']); ?></td>
                                <td class="p-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full capitalize <?php 
                                        switch($order['payment_method']) {
                                            case 'cod': echo 'bg-blue-500 text-blue-100'; break;
                                            case 'upload': echo 'bg-purple-500 text-purple-100'; break;
                                            default: echo 'bg-green-500 text-green-100'; break;
                                        }
                                    ?>"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                                </td>
                                <td class="p-4 status-cell">
                                    <span class="font-bold capitalize <?php 
                                        switch($order['payment_status']) {
                                            case 'completed':
                                            case 'success':
                                                echo 'text-green-400';
                                                break;
                                            case 'pending':
                                                echo 'text-yellow-400';
                                                break;
                                            case 'failed':
                                                echo 'text-red-400';
                                                break;
                                            default:
                                                echo 'text-gray-400';
                                        }
                                    ?>"><?php echo htmlspecialchars($order['payment_status']); ?></span>
                                </td>
                                <td class="p-4 action-cell">
                                    <?php if ($order['payment_status'] === 'pending'): ?>
                                        <button class="action-btn text-green-400 hover:underline" data-action="complete_order" data-id="<?php echo htmlspecialchars($order['order_id']); ?>">Complete</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script src="admin_script.js"></script>
</body>
</html>
