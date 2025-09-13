<?php
session_start();
require 'db_connect.php';

// --- Security: Ensure user is logged in ---
if (empty($_SESSION['user_id'])) {
    header("Location: login.html?error=You+must+be+logged+in+to+upload+a+file.");
    exit;
}

// --- Validation: Check for POST request and file upload ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    header("Location: index.html#upload?error=File+upload+failed.+Please+try+again.");
    exit;
}

// --- Configuration ---
$uploadDir = 'uploads/';
$servicePrice = 20.00; // Set a default price for custom print/file orders

// --- File Handling & Validation ---
$file = $_FILES['file'];
$originalFileName = basename($file['name']);
$fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
$storedFileName = uniqid('file_', true) . '.' . $fileExtension;
$destination = $uploadDir . $storedFileName;

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    header("Location: index.html#upload?error=Could+not+save+the+file.");
    exit;
}

// --- Prepare Data for Order Confirmation Page ---
// We will now redirect the user to the standard order page.
// We use the session to pass the file details securely.
$_SESSION['upload_context'] = [
    'original_filename' => $originalFileName,
    'stored_filename' => $storedFileName,
    'instructions' => trim($_POST['instructions'] ?? '')
];

// --- Create a self-submitting form to POST data to place_order.php ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Processing Upload...</title>
</head>
<body>
    <p>Processing your upload, please wait...</p>
    <form id="redirectForm" action="place_order.php" method="POST">
        <input type="hidden" name="service_name" value="Custom File Upload & Print">
        <input type="hidden" name="unit_price" value="<?php echo $servicePrice; ?>">
        <input type="hidden" name="quantity" value="1">
    </form>
    <script>
        document.getElementById('redirectForm').submit();
    </script>
</body>
</html>
