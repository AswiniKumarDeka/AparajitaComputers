<?php
// admin.php
session_start();

// --- Security Check ---
// Redirect to login if not logged in, or if the user is not an admin.
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit;
}

require 'db_connect.php';

// Fetch all uploads from the database, newest first
$result = $conn->query("SELECT * FROM uploads ORDER BY uploaded_at DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Uploads</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        body { font-family: "Poppins", sans-serif; }
    </style>
</head>
<body class="bg-gray-900 text-white">

    <header class="bg-gray-800">
        <nav class="container mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <h1 class="text-xl font-bold text-cyan-400">Admin Panel</h1>
            <div>
                <a href="index.html" class="hover:text-cyan-400 transition-colors mr-4">Back to Website</a>
                <a href="logout.php" class="hover:text-cyan-400 transition-colors">Logout</a>
            </div>
        </nav>
    </header>

    <main class="container mx-auto p-4 sm:p-6 lg:p-8">
        <h2 class="text-3xl font-bold mb-6">Customer File Uploads</h2>

        <div class="bg-gray-800 rounded-lg shadow-lg overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="p-4 font-semibold">Customer Name</th>
                        <th class="p-4 font-semibold">Email</th>
                        <th class="p-4 font-semibold">Instructions</th>
                        <th class="p-4 font-semibold">File</th>
                        <th class="p-4 font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="border-b border-gray-700 hover:bg-gray-700/50">
                                <td class="p-4"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($row['customer_email']); ?></td>
                                <td class="p-4 text-gray-300">
                                    <?php 
                                        // Display instructions, or a message if they are empty
                                        echo !empty($row['instructions']) ? htmlspecialchars($row['instructions']) : '<span class="text-gray-500">No instructions</span>'; 
                                    ?>
                                </td>
                                <td class="p-4">
                                    <a href="<?php echo htmlspecialchars($row['file_path']); ?>" 
                                       download="<?php echo htmlspecialchars($row['original_file_name']); ?>"
                                       class="text-cyan-400 hover:underline font-semibold">
                                        Download File
                                    </a>
                                </td>
                                <td class="p-4 text-sm text-gray-400">
                                    <?php echo date("d M Y, h:i A", strtotime($row['uploaded_at'])); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center p-8 text-gray-500">No files have been uploaded yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
<?php
$conn->close();
?>
