<?php
session_start();

// Database Connection
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You need to login first.");
}
$user_id = $_SESSION['user_id'];

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // Remove item from the cart
    $delete_query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
}

// Redirect back to cart page
header("Location: cart.php");
exit();
?>
