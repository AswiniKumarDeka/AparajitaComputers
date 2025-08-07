<?php
// FILE: admin_reviews.php (NEW)
require 'admin_auth_check.php';
require 'db_connect.php';
$reviews_result = $conn->query("SELECT id, username, rating, review_text, is_approved FROM reviews ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head><title>Review Management</title><!-- ... headers ... --></head>
<body class="bg-gray-900 text-white">
    <?php include 'admin_header.php'; ?>
    <main class="container mx-auto p-8">
        <h2 class="text-3xl font-bold mb-6">Review Management</h2>
        <!-- ... review table HTML ... -->
    </main>
    <script src="admin_script.js"></script>
</body>
</html>
