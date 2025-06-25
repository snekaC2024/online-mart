<?php
include 'db.php';
session_start();

if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    // Prevent updating if already cancelled
    $check_sql = "SELECT status FROM orders WHERE id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if ($order['status'] !== 'cancelled') {
        $update_sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $new_status, $order_id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Order status updated."]);
        } else {
            echo json_encode(["success" => false, "message" => "Update failed."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Cannot update a cancelled order."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>
