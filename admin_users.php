<?php
// FILE 6 of 9: admin_users.php
// ===================================================================
require 'admin_auth_check.php';
require 'db_connect.php';
$users_result = $conn->query("SELECT id, username, email, role, is_suspended FROM users ORDER BY username ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Management - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <style> body { font-family: "Poppins", sans-serif; } </style>
</head>
<body class="bg-gray-900 text-white">
    <?php include 'admin_header.php'; ?>
    <main class="container mx-auto p-8">
        <h2 class="text-3xl font-bold mb-6">User Management</h2>
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="p-4 font-semibold">Username</th><th class="p-4 font-semibold">Email</th><th class="p-4 font-semibold">Role</th><th class="p-4 font-semibold">Status</th><th class="p-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $users_result->fetch_assoc()): ?>
                        <tr id="user-row-<?php echo $user['id']; ?>" class="border-b border-gray-700 hover:bg-gray-700/50 transition-colors">
                            <td class="p-4"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td class="p-4"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="p-4"><span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $user['role'] === 'admin' ? 'bg-yellow-500 text-yellow-900' : 'bg-blue-500 text-blue-100'; ?>"><?php echo htmlspecialchars($user['role']); ?></span></td>
                            <td class="p-4 status-cell"><?php echo $user['is_suspended'] ? '<span class="text-red-400 font-bold">Suspended</span>' : '<span class="text-green-400 font-bold">Active</span>'; ?></td>
                            <td class="p-4 action-cell">
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <?php if ($user['is_suspended']): ?>
                                        <button class="action-btn text-green-400 hover:underline" data-action="unsuspend_user" data-id="<?php echo $user['id']; ?>">Unsuspend</button>
                                    <?php else: ?>
                                        <button class="action-btn text-yellow-400 hover:underline" data-action="suspend_user" data-id="<?php echo $user['id']; ?>">Suspend</button>
                                    <?php endif; ?>
                                    <button class="action-btn text-red-400 hover:underline ml-4" data-action="delete_user" data-id="<?php echo $user['id']; ?>">Delete</button>
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

