<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

include 'db.php';

// Get data
if (!isset($_POST['id'], $_POST['rating'], $_POST['comment']) || 
    !is_numeric($_POST['id']) || 
    !is_numeric($_POST['rating'])) {
    echo json_encode(["success" => false, "message" => "Invalid input."]);
    exit();
}

$review_id = intval($_POST['id']);
$rating    = intval($_POST['rating']);
$comment   = trim($_POST['comment']);
$user_id   = $_SESSION['user_id'];

// Validate rating range
if ($rating < 1 || $rating > 5) {
    echo json_encode(["success" => false, "message" => "Invalid rating."]);
    exit();
}

// Verify review ownership
$sql_check = "SELECT id FROM reviews WHERE id = ? AND user_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $review_id, $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Review not found or not yours."]);
    exit();
}

// Update the review
$sql_update = "UPDATE reviews SET rating = ?, comment = ? WHERE id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("isi", $rating, $comment, $review_id);

if ($stmt_update->execute()) {
    echo json_encode(["success" => true, "message" => "Review updated successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update review."]);
}

$conn->close();
?>
