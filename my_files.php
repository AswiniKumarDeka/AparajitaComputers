<?php
session_start();
require 'db_connect.php'; // Use the central, correct database connection

// --- CORRECTED SECURITY CHECK ---
// Check for 'user_id' to be consistent with your other pages (e.g., payment.php)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?error=Please log in to view your files.");
    exit;
}

$files = []; // Initialize an array to hold the files
$error_message = null;

try {
    $user_id = $_SESSION['user_id']; // Use the correct session variable

    // --- CORRECTED DATABASE QUERY (Using PDO for PostgreSQL) ---
    // The table is likely 'uploads', not 'files', based on your dashboard code.
    // The columns are also updated to match your dashboard.
    $stmt = $conn->prepare("SELECT original_file_name, stored_file_name, uploaded_at FROM uploads WHERE user_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$user_id]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Log the error and show a user-friendly message
    error_log("My Files Error: " . $e->getMessage());
    $error_message = "Error: Could not retrieve your files at this time. Please try again later.";
}

// Close the database connection
$conn = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Files - Aparajita Computers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-900 text-white">
    <header class="bg-gray-800">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.html" class="text-xl font-bold text-cyan-400">APARAJITA COMPUTERS</a>
            <div>
                <a href="dashboard.php" class="hover:text-cyan-400 mr-4">Dashboard</a>
                <a href="index.html" class="hover:text-cyan-400 mr-4">Main Site</a>
                <a href="logout.php" class="hover:text-cyan-400">Logout</a>
            </div>
        </nav>
    </header>
    <main class="container mx-auto px-6 py-12">
        <h1 class="text-3xl md:text-4xl font-bold mb-8">My Uploaded Files</h1>
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-x-auto">
            <?php if ($error_message): ?>
                <p class="p-8 text-center text-red-400"><?php echo $error_message; ?></p>
            <?php elseif (!empty($files)): ?>
                <table class="w-full text-left">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="p-4">File Name</th>
                            <th class="p-4">Date Uploaded</th>
                            <th class="p-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($files as $file): ?>
                            <tr class="border-b border-gray-700">
                                <!-- Use correct column names from the corrected query -->
                                <td class="p-4"><?php echo htmlspecialchars($file['original_file_name']); ?></td>
                                <td class="p-4 text-gray-400"><?php echo date('d M Y, h:i A', strtotime($file['uploaded_at'])); ?></td>
                                <td class="p-4"><a href="uploads/<?php echo htmlspecialchars($file['stored_file_name']); ?>" class="text-cyan-400 hover:text-cyan-300 font-semibold" download>Download</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="p-8 text-center text-gray-400">You have not uploaded any files yet. Go to the <a href="index.html#upload" class="text-cyan-400">main page</a> to upload.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

