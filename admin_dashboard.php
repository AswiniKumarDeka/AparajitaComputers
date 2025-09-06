<?php
session_start(); // ADD THIS LINE

// Security Check: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?error=You must be logged in to make a payment.");
    exit;
}
// This is the main landing page for the admin.

require 'admin_auth_check.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Aparajita Computers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <style> body { font-family: "Poppins", sans-serif; } </style>
</head>
<body class="bg-gray-900 text-white">

    <?php include 'admin_header.php'; ?>

    <main class="container mx-auto p-4 sm:p-6 lg:p-8">
        <h2 class="text-3xl font-bold mb-6">Dashboard Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="admin_users.php" class="bg-gray-800 p-6 rounded-lg shadow-lg hover:bg-gray-700 transition-colors">
                <h3 class="text-xl font-bold text-cyan-400 mb-2">User Management</h3>
                <p class="text-gray-400">View, delete, suspend, and manage all registered users.</p>
            </a>
            <a href="admin_orders.php" class="bg-gray-800 p-6 rounded-lg shadow-lg hover:bg-gray-700 transition-colors">
                <h3 class="text-xl font-bold text-cyan-400 mb-2">Order Management</h3>
                <p class="text-gray-400">View and update the status of all customer orders.</p>
            </a>
            <a href="admin_files.php" class="bg-gray-800 p-6 rounded-lg shadow-lg hover:bg-gray-700 transition-colors">
                <h3 class="text-xl font-bold text-cyan-400 mb-2">File Management</h3>
                <p class="text-gray-400">Browse and manage all files uploaded by users.</p>
            </a>
        </div>
    </main>

</body>
</html>
