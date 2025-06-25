<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];

// Database Connection
include 'db.php';

// Check if product is already in favorites
$query = "SELECT * FROM favorites WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Product is already in favorites, remove it
    $delete_query = "DELETE FROM favorites WHERE user_id = ? AND product_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param('ii', $user_id, $product_id);
    $delete_stmt->execute();
    $response = ['success' => true, 'message' => 'Product removed from favorites.'];
} else {
    // Product is not in favorites, add it
    $insert_query = "INSERT INTO favorites (user_id, product_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param('ii', $user_id, $product_id);
    $insert_stmt->execute();
    $response = ['success' => true, 'message' => 'Product added to favorites.'];
}

$conn->close();
echo json_encode($response);
?>
