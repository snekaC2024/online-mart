<?php
session_start();
include 'db.php';

// Get user ID and review ID
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die(json_encode(["success" => false, "message" => "Invalid request."]));
}

$review_id = intval($_POST['id']);
$user_id = $_SESSION['user_id']; // Ensure the user is logged in

// Check if the review exists and belongs to the logged-in user
$sql_check = "SELECT * FROM reviews WHERE id = ? AND user_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $review_id, $user_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Review not found or unauthorized."]);
    exit();
}

// Delete the review
$sql_delete = "DELETE FROM reviews WHERE id = ? AND user_id = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("ii", $review_id, $user_id);

if ($stmt_delete->execute()) {
    echo json_encode(["success" => true, "message" => "Review deleted successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to delete review."]);
}

$conn->close();
?>
