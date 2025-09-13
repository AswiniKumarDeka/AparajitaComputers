<?php
$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    die("Order not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Success</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-green-100 via-emerald-100 to-teal-100 flex items-center justify-center min-h-screen">

  <div class="bg-white p-8 rounded-2xl shadow-2xl max-w-lg w-full text-center animate-fadeIn">
    <h2 class="text-3xl font-bold text-green-600 animate-bounce">🎉 Order placed successfully!</h2>
    <p class="mt-3 text-lg text-gray-700">Your Order ID: 
      <span class="font-semibold text-indigo-600"><?php echo htmlspecialchars($order_id); ?></span>
    </p>

    <!-- Upload Section -->
    <div class="mt-6">
      <h3 class="text-xl font-semibold text-gray-700">📂 Upload your files</h3>
      <form method="POST" action="upload.php" enctype="multipart/form-data" class="mt-4 space-y-4">
        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
        <input type="file" name="file" required
          class="w-full border border-gray-300 rounded-lg p-2">
        <button type="submit"
          class="w-full bg-green-600 text-white py-2 rounded-lg shadow-lg hover:bg-green-700 transform hover:scale-105 transition duration-300">
          ⬆️ Upload File
        </button>
      </form>
    </div>

    <div class="mt-6">
      <a href="index.html" class="text-indigo-600 hover:underline">← Back to Home</a>
    </div>
  </div>

  <style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px);} to {opacity:1; transform: translateY(0);} }
    .animate-fadeIn { animation: fadeIn 0.8s ease-out; }
  </style>

</body>
</html>
