<?php
session_start();

// If the user is not logged in, send them back to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <style> body { font-family: "Poppins", sans-serif; } </style>
</head>
<body class="bg-gray-900 text-white p-8">
    <div class="container mx-auto">
        <h1 class="text-3xl font-bold text-cyan-400">Welcome to your Dashboard!</h1>
        <p class="mt-4">You are successfully logged in.</p>

        <a href="logout.php" class="text-red-400 hover:underline mt-6 inline-block">Logout</a>
    </div>
</body>
</html>
