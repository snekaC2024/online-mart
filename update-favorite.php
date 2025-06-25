<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id']; // Get logged-in user's ID

// Get the posted data from AJAX
$data = json_decode(file_get_contents("php://input"));
$product_id = $data->productId;
$favorited = $data->favorited; // True or False for adding/removing

// Database connection
include 'db.php';

// If the item is marked as favorite, add it to the favorites table
if ($favorited) {
    $stmt = $conn->prepare("INSERT INTO favorites (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $message = "Added to favorites!";
} else {
    // If the item is removed from favorites, delete it from the favorites table
    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $message = "Removed from favorites!";
}

$stmt->close();
$conn->close();

echo json_encode(['message' => $message]);
?>
