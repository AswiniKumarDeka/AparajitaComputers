<?php
// FILE 3 of 9: admin_header.php
// ===================================================================
// This header is included on all admin pages.
?>
<header class="bg-gray-800 border-b border-gray-700">
    <nav class="container mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <div class="flex items-center space-x-6">
            <a href="admin_dashboard.php" class="text-lg font-bold text-cyan-400">Admin Home</a>
            <a href="admin_files.php" class="text-sm hover:text-cyan-400 transition-colors">Files</a>
            <a href="admin_users.php" class="text-sm hover:text-cyan-400 transition-colors">User Management</a>
            <a href="admin_orders.php" class="text-sm hover:text-cyan-400 transition-colors">Order Management</a>
            <a href="admin_transactions.php" class="text-sm hover:text-cyan-400 transition-colors">Transactions</a>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-gray-300">Hello, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?>!</span>
            <a href="logout.php" class="text-sm bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md transition-colors">Logout</a>
        </div>
    </nav>
</header>
