<?php
session_start();
require 'db_connect.php';

// --- Ensure only admin can access ---
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied");
}

if (isset($_POST['approve'])) {
    $id = intval($_POST['approve']);
    $conn->query("UPDATE reviews SET approved = 1 WHERE id = $id");
}
if (isset($_POST['reject'])) {
    $id = intval($_POST['reject']);
    $conn->query("DELETE FROM reviews WHERE id = $id");
}

$result = $conn->query("SELECT r.id, r.rating, r.comment, r.created_at, u.name 
                        FROM reviews r
                        JOIN users u ON r.user_id = u.id
                        WHERE r.approved = 0
                        ORDER BY r.created_at ASC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Pending Reviews</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-900 text-white p-6">
  <h1 class="text-2xl font-bold mb-4">Pending Reviews</h1>

  <?php while($row = $result->fetch_assoc()): ?>
    <div class="bg-gray-800 p-4 rounded-lg mb-4">
      <p><strong>User:</strong> <?= htmlspecialchars($row['name']) ?></p>
      <p><strong>Rating:</strong> <?= $row['rating'] ?>/5</p>
      <p><strong>Comment:</strong> <?= htmlspecialchars($row['comment']) ?></p>
      <p class="text-sm text-gray-400"><?= $row['created_at'] ?></p>

      <form method="post" class="mt-2 space-x-2">
        <button name="approve" value="<?= $row['id'] ?>" class="bg-green-600 px-3 py-1 rounded">Approve</button>
        <button name="reject" value="<?= $row['id'] ?>" class="bg-red-600 px-3 py-1 rounded">Reject</button>
      </form>
    </div>
  <?php endwhile; ?>

</body>
</html>
