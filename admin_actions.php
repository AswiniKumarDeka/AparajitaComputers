<?php
// admin_actions.php (Final, Corrected Version)

// These lines force PHP to display any errors, which is crucial for debugging.
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// The session must be started to access session variables securely.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db_connect.php';

$response = ['success' => false, 'error' => 'Invalid Request'];

// --- Security Check: Only Admins can perform actions ---
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $response['error'] = 'Authentication failed. You do not have permission to perform this action.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['id'])) {
    $action = $_POST['action'];
    $id = $_POST['id']; // Use the ID as sent from JavaScript

    switch ($action) {
        // --- User Actions ---
        case 'delete_user':
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
            $stmt->bind_param("i", intval($id));
            if ($stmt->execute()) {
                $response['success'] = true;
            } else {
                $response['error'] = 'Failed to delete user.';
            }
            $stmt->close();
            break;

        case 'suspend_user':
            $stmt = $conn->prepare("UPDATE users SET is_suspended = 1 WHERE id = ? AND role = 'user'");
            $stmt->bind_param("i", intval($id));
            if ($stmt->execute()) {
                $response['success'] = true;
            } else {
                $response['error'] = 'Failed to suspend user.';
            }
            $stmt->close();
            break;
        
        case 'unsuspend_user':
            $stmt = $conn->prepare("UPDATE users SET is_suspended = 0 WHERE id = ? AND role = 'user'");
            $stmt->bind_param("i", intval($id));
            if ($stmt->execute()) {
                $response['success'] = true;
            } else {
                $response['error'] = 'Failed to unsuspend user.';
            }
            $stmt->close();
            break;

        // --- Order Actions ---
        case 'complete_order':
            $order_id = $id; // For orders, the ID is the order_id string
            $stmt = $conn->prepare("UPDATE payments SET payment_status = 'completed' WHERE order_id = ?");
            $stmt->bind_param("s", $order_id);
            if ($stmt->execute()) {
                $response['success'] = true;
                // You can add logic here to send a completion email
            } else {
                $response['error'] = 'Failed to update order status.';
            }
            $stmt->close();
            break;
        
        default:
            $response['error'] = 'Unknown action specified.';
            break;
    }
}

$conn->close();
echo json_encode($response);
?>
