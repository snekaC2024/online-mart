<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die(json_encode(["status" => "error", "message" => "You must be logged in to submit a review."]));
}

// Database Connection
include 'db.php';
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}

// Validate inputs
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['product_id'], $data['rating'], $data['comment']) || !is_numeric($data['rating'])) {
    die(json_encode(["status" => "error", "message" => "Invalid input."]));
}

$product_id = intval($data['product_id']);
$rating = intval($data['rating']);
$comment = trim($data['comment']);
$user_id = $_SESSION['user_id'];

$sql = "INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Review submitted successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to submit review."]);
}

$stmt->close();
$conn->close();
?>
