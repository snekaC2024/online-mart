<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $user_id = $_SESSION['user_id'];

    // Database Connection
include 'db.php';

    // Debugging: Check if the correct order_id is received
    error_log("Cancelling order ID: " . $order_id . " for user ID: " . $user_id);

    // Prepare the SQL statement
    $stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ? AND user_id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("ii", $order_id, $user_id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No rows updated. Order may not exist or is already cancelled.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel order: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

?>
