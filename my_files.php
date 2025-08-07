<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.html");
    exit;
}

$servername = "localhost";
$username_db = "root";
$password_db = "YourActualPassword"; // <-- IMPORTANT
$dbname = "my_shop_db";
$port = 3307; // <-- IMPORTANT

$conn = new mysqli($servername, $username_db, $password_db, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION["id"];
$stmt = $conn->prepare("SELECT original_filename, stored_filename, upload_timestamp FROM files WHERE user_id = ? ORDER BY upload_timestamp DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Files - Aparajita Computers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-900 text-white">
    <header class="bg-gray-800">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.html" class="text-xl font-bold text-cyan-400">APARAJITA COMPUTERS</a>
            <div>
                <a href="index.html" class="hover:text-cyan-400 mr-4">Main Site</a>
                <a href="logout.php" class="hover:text-cyan-400">Logout</a>
            </div>
        </nav>
    </header>
    <main class="container mx-auto px-6 py-12">
        <h1 class="text-3xl md:text-4xl font-bold mb-8">My Uploaded Files</h1>
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-x-auto">
            <?php if ($result->num_rows > 0): ?>
                <table class="w-full text-left">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="p-4">File Name</th>
                            <th class="p-4">Date Uploaded</th>
                            <th class="p-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="border-b border-gray-700">
                                <td class="p-4"><?php echo htmlspecialchars($row['original_filename']); ?></td>
                                <td class="p-4 text-gray-400"><?php echo date('d M Y, h:i A', strtotime($row['upload_timestamp'])); ?></td>
                                <td class="p-4"><a href="uploads/<?php echo htmlspecialchars($row['stored_filename']); ?>" class="text-cyan-400 hover:text-cyan-300 font-semibold" download>Download</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="p-8 text-center text-gray-400">You have not uploaded any files yet. Go to the <a href="index.html#upload" class="text-cyan-400">main page</a> to upload.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
