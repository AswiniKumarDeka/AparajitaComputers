<?php
session_start();
require 'db_connect.php';

// --- Security Check ---
// If the user is not logged in, redirect them to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// --- Fetch User Data ---
// Get the user's details from the database to display on the page
$userId = $_SESSION['user_id'];
$user = null;
$uploads = [];
$orders = []; // Initialize orders array

try {
    // This part uses PDO, which is correct with your db_connect.php
    // Fetch user profile information using prepared statements for security
    $stmt = $conn->prepare("SELECT name, email, created_at FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch the user's 5 most recent file uploads
    $stmt = $conn->prepare("SELECT original_file_name, uploaded_at FROM uploads WHERE user_id = :user_id ORDER BY uploaded_at DESC LIMIT 5");
    $stmt->execute([':user_id' => $userId]);
    $uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch the user's 5 most recent orders
    $stmt = $conn->prepare("SELECT service_name, payment_status, payment_date FROM payments WHERE user_id = :user_id ORDER BY payment_date DESC LIMIT 5");
    $stmt->execute([':user_id' => $userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // In a production environment, you would log this error instead of showing it.
    error_log("Dashboard Error: " . $e->getMessage());
    die("Error: Could not fetch user data. Please contact support.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Aparajita Computers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <style> 
        body { font-family: "Poppins", sans-serif; } 
        /* Animation for the modal */
        .modal-fade-enter { transition: opacity 0.3s ease-out, transform 0.3s ease-out; }
        .modal-fade-enter-from { opacity: 0; transform: scale(0.95); }
        .modal-fade-enter-to { opacity: 1; transform: scale(1); }
    </style>
</head>
<body class="bg-gray-900 text-white">

    <!-- =========== START: MAINTENANCE POPUP =========== -->
    <div id="maintenance-modal" class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-50 hidden modal-fade-enter modal-fade-enter-from">
        <div class="bg-gray-800 border border-yellow-500/30 w-full max-w-lg p-6 sm:p-8 rounded-2xl shadow-2xl space-y-4 text-center">
            <div class="flex justify-center">
                <svg class="w-16 h-16 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-yellow-400">We're Making Things Better!</h2>
            <p class="text-gray-300">We're currently performing scheduled maintenance to improve our services for you. During this time, the services and payments section is temporarily unavailable.</p>
            <p class="font-bold text-red-400 bg-red-500/10 p-3 rounded-md">
                To ensure a smooth update, please refrain from placing new orders or making payments. Transactions attempted during this period will not be processed.
            </p>
            <p class="text-gray-400 text-sm">We appreciate your patience and look forward to bringing you an enhanced experience shortly!</p>
            <button id="close-modal-btn" class="w-full bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-3 px-6 rounded-md transition-colors mt-4">I Understand</button>
        </div>
    </div>
    <!-- =========== END: MAINTENANCE POPUP =========== -->


    <!-- Header Navigation -->
    <header class="bg-gray-800">
        <nav class="container mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <a href="index.html" class="text-xl font-bold text-cyan-400">Aparajita Computers</a>
            <div class="flex items-center space-x-4">
                <span class="text-gray-300">
                    Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!
                </span>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin_dashboard.php" class="text-yellow-400 hover:underline font-semibold">Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php" class="text-sm bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md transition-colors">
                    Logout
                </a>
            </div>
        </nav>
    </header>

    <!-- Main Dashboard Content -->
    <main class="container mx-auto p-4 sm:p-6 lg:p-8">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-8">Your Dashboard</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Profile Card (Left Column) -->
            <div class="md:col-span-1 bg-gray-800 p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold text-cyan-400 mb-4">Your Profile</h2>
                <?php if ($user): ?>
                    <div class="space-y-3 text-gray-300">
                        <p><strong class="font-semibold text-white">Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                        <p><strong class="font-semibold text-white">Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong class="font-semibold text-white">Member Since:</strong> <?php echo date("F j, Y", strtotime($user['created_at'])); ?></p>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">Could not load profile information.</p>
                <?php endif; ?>
            </div>

            <!-- Activity and Actions (Right Columns) -->
            <div class="md:col-span-2 space-y-8">
                
                <!-- Quick Actions Card -->
                <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h2 class="text-2xl font-bold text-cyan-400 mb-4">Quick Actions</h2>
                    <div class="flex flex-wrap gap-4">
                        <a href="index.html#upload" class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-3 px-6 rounded-md transition-colors">
                            Upload a New File
                        </a>
                        <a href="track_order.html" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-md transition-colors">
                            Track My Orders
                        </a>
                    </div>
                </div>

                <!-- Recent Orders Card -->
                <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h2 class="text-2xl font-bold text-cyan-400 mb-4">Recent Orders</h2>
                    <div class="space-y-4">
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                                <div class="flex justify-between items-center bg-gray-700 p-4 rounded-md">
                                    <div>
                                        <p class="font-semibold text-white"><?php echo htmlspecialchars($order['service_name']); ?></p>
                                        <p class="text-sm text-gray-400">Ordered on: <?php echo date("d M Y", strtotime($order['payment_date'])); ?></p>
                                    </div>
                                    <div>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full capitalize
                                            <?php 
                                                $statusClass = 'bg-yellow-500 text-yellow-100'; // Default for pending
                                                if ($order['payment_status'] === 'completed' || $order['payment_status'] === 'success') {
                                                    $statusClass = 'bg-green-500 text-green-100';
                                                } elseif ($order['payment_status'] === 'failed') {
                                                    $statusClass = 'bg-red-500 text-red-100';
                                                }
                                                echo $statusClass;
                                            ?>">
                                            <?php echo htmlspecialchars($order['payment_status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-gray-500">You haven't placed any orders yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Uploads Card -->
                <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h2 class="text-2xl font-bold text-cyan-400 mb-4">Recent Uploads</h2>
                    <div class="space-y-3">
                        <?php if (!empty($uploads)): ?>
                            <ul class="list-disc list-inside text-gray-300">
                                <?php foreach ($uploads as $upload): ?>
                                    <li>
                                        <?php echo htmlspecialchars($upload['original_file_name']); ?>
                                        <span class="text-xs text-gray-500 ml-2">(<?php echo date("d M Y", strtotime($upload['uploaded_at'])); ?>)</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-gray-500">You haven't uploaded any files yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('maintenance-modal');
            const closeModalBtn = document.getElementById('close-modal-btn');

            // --- Logic to show modal only once per session ---
            if (!sessionStorage.getItem('maintenanceNoticeSeen')) {
                // Show the modal with a fade-in effect
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.remove('modal-fade-enter-from');
                    modal.classList.add('modal-fade-enter-to');
                }, 10); // A small delay to allow CSS transition to trigger
            }

            // --- Function to close the modal ---
            const closeModal = () => {
                // Add fade-out effect
                modal.classList.remove('modal-fade-enter-to');
                modal.classList.add('modal-fade-enter-from');
                
                // Hide modal after animation and set session flag
                setTimeout(() => {
                    modal.classList.add('hidden');
                    sessionStorage.setItem('maintenanceNoticeSeen', 'true');
                }, 300); // Must match the transition duration in CSS
            };

            closeModalBtn.addEventListener('click', closeModal);
            // Optional: allow closing by clicking the background overlay
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });
        });
    </script>

</body>
</html>

