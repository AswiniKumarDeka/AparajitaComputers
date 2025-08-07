<?php
// track_order.php
header('Content-Type: application/json');
require 'db_connect.php';

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id'])) {
    $order_id = trim($_POST['order_id']);

    if (empty($order_id)) {
        $response['error'] = "Please enter an Order ID.";
        echo json_encode($response);
        exit;
    }

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT order_id, service_name, payment_status FROM payments WHERE order_id = ?");
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $order = $result->fetch_assoc();
        $response = $order;
    } else {
        $response['error'] = "No order found with that ID. Please check the ID and try again.";
    }

    $stmt->close();
    $conn->close();
} else {
    $response['error'] = "Invalid request.";
}

echo json_encode($response);
?>
