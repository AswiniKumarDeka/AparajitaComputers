<?php
$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    die("Order not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Order Success</title>
</head>
<body>
  <h2>🎉 Order placed successfully!</h2>
  <p>Your Order ID: <strong><?php echo htmlspecialchars($order_id); ?></strong></p>

  <h3>Upload your files:</h3>
  <form method="POST" action="upload.php" enctype="multipart/form-data">
    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
    <input type="file" name="file" required>
    <button type="submit">Upload</button>
  </form>
</body>
</html>
