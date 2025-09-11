<?php
// Always start the session at the very beginning
session_start();

// Include the database connection file
require 'db_connect.php';

// Set the header to return JSON
header('Content-Type: application/json');

// Function to send a JSON response and exit
function json_response($success, $message, $data = []) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

// --- 1. Basic Validation ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method.');
}

if (!isset($_POST['email'], $_POST['password'])) {
    json_response(false, 'Email and password are required.');
}

$email = trim($_POST['email']);
$password = $_POST['password'];

if (empty($email) || empty($password)) {
    json_response(false, 'Please fill in both fields.');
}

// --- 2. Database Interaction ---
try {
    // Prepare a statement to select the user by email
    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    // Fetch the user
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // --- 3. Verify User and Password ---
    if ($user && password_verify($password, $user['password'])) {
        // --- SUCCESSFUL LOGIN ---
        
        // Regenerate session ID for security
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['name']; // Using 'name' column for username
        $_SESSION['role'] = $user['role']; // Store the user's role

        json_response(true, 'Login successful! Redirecting...');

    } else {
        // --- FAILED LOGIN ---
        json_response(false, 'Invalid email or password.');
    }

} catch (PDOException $e) {
    // --- DATABASE ERROR ---
    // In a production environment, you would log this error.
    error_log('Login PDOException: ' . $e->getMessage());
    json_response(false, 'A server error occurred. Please try again later.');
}

// Close the connection
$conn = null;
?>
