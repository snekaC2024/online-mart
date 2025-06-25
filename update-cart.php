<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$product_id = $data['productId'] ?? null;
$addedToCart = $data['addedToCart'] ?? false;

include 'db.php';

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

if ($addedToCart) {
    // Check if the product is already in the cart
    $check_query = "SELECT id FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // If exists, increase quantity
        $update_query = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    } else {
        // Insert new item
        $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    }

    echo json_encode(["status" => "success", "message" => "Product added to cart"]);
} else {
    // Remove item from cart
    $delete_query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();

    echo json_encode(["status" => "success", "message" => "Product removed from cart"]);
}



// Get action and product ID
if (isset($_GET['action'], $_GET['product_id'])) {
    $action = $_GET['action'];
    $product_id = $_GET['product_id'];

    if ($action == "increase") {
        $query = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
    } elseif ($action == "decrease") {
        // Prevent quantity from going below 1
        $query = "UPDATE cart SET quantity = GREATEST(quantity - 1, 1) WHERE user_id = ? AND product_id = ?";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    
}

// Redirect back to cart page
header("Location: cart.php");
exit();

$stmt->close();
$conn->close();
?>
