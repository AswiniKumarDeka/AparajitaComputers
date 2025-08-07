
<?php
// FILE 8 of 9: admin_files.php
// ===================================================================
require 'admin_auth_check.php';
require 'db_connect.php';
$files_result = $conn->query("SELECT id, user_id, original_file_name, file_path, uploaded_at FROM uploads ORDER BY uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>File Management - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <style> body { font-family: "Poppins", sans-serif; } </style>
</head>
<body class="bg-gray-900 text-white">
    <?php include 'admin_header.php'; ?>
    <main class="container mx-auto p-8">
        <h2 class="text-3xl font-bold mb-6">File Management</h2>
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="p-4 font-semibold">File Name</th><th class="p-4 font-semibold">Uploaded By (User ID)</th><th class="p-4 font-semibold">Uploaded At</th><th class="p-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($file = $files_result->fetch_assoc()): ?>
                        <tr class="border-b border-gray-700">
                            <td class="p-4"><?php echo htmlspecialchars($file['original_file_name']); ?></td>
                            <td class="p-4"><?php echo htmlspecialchars($file['user_id']); ?></td>
                            <td class="p-4 text-sm text-gray-400"><?php echo date("d M Y, h:i A", strtotime($file['uploaded_at'])); ?></td>
                            <td class="p-4">
                                <a href="<?php echo htmlspecialchars($file['file_path']); ?>" download class="text-cyan-400 hover:underline">Download</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
