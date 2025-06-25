<?php
session_start();
include "db.php";

$success_message = "";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You need to login first.");
}
$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = "SELECT address, state, city, phone FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

$user_address = $user_data['address'] ?? '';
$user_state = $user_data['state'] ?? '';
$user_city = $user_data['city'] ?? '';
$user_phone = $user_data['phone'] ?? '';

// Fetch cart or single product
$cart_products = [];
$total_price = 0;

if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $product_query = "SELECT id AS product_id, product_name, image, price, count, farmer_id FROM products WHERE id = ?";
    $stmt = $conn->prepare($product_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();
    
    if ($product_row = $product_result->fetch_assoc()) {
        if ($product_row['count'] > 0) {
            $product_row['quantity'] = 1;
            $cart_products[] = $product_row;
            $total_price = $product_row['price'];
        } else {
            die("<p style='color: red;'>❌ Product is out of stock.</p>");
        }
    }
} else {
    $cart_query = "SELECT c.product_id, p.product_name, p.image, p.price, c.quantity, p.count, p.farmer_id 
                   FROM cart c
                   JOIN products p ON c.product_id = p.id
                   WHERE c.user_id = ?";
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_result = $stmt->get_result();
    $cart_products = $cart_result->fetch_all(MYSQLI_ASSOC);
    
    foreach ($cart_products as $product) {
        if ($product['count'] < $product['quantity']) {
            die("<p style='color: red;'>❌ Not enough stock for {$product['product_name']}.</p>");
        }
        $total_price += $product['price'] * $product['quantity'];
    }
}
// Process Checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $phone = $_POST['phone'];
    $payment_method = $_POST['payment_method'];

    if (empty($address) || empty($city) || empty($state) || empty($phone)) {
        $success_message = "<p style='color: red;'>Please fill all address details.</p>";
    } else {
        // ✅ Insert Order into Orders Table
        $order_query = "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')";
        $stmt = $conn->prepare($order_query);
        $stmt->bind_param("id", $user_id, $total_price);

        if ($stmt->execute()) {
            $order_id = $conn->insert_id;

            // ✅ Insert Each Product into order_items Table
            foreach ($cart_products as $product) {
                $product_id = $product['product_id'];
                $farmer_id = $product['farmer_id'];
                $quantity = $product['quantity'];
                $price = $product['price'];
                $total_price_item = $price * $quantity;
                $image = $product['image']; // ✅ Fetch image from products table

                // ✅ Insert into order_items (Now Including Image)
                $order_item_query = "INSERT INTO order_items (order_id, product_id, farmer_id, quantity, product_price, total_price, image) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($order_item_query);
                $stmt->bind_param("iiiidds", $order_id, $product_id, $farmer_id, $quantity, $price, $total_price_item, $image);
                $stmt->execute();

                // ✅ Reduce Stock
                $update_stock_query = "UPDATE products SET count = count - ? WHERE id = ?";
                $stmt = $conn->prepare($update_stock_query);
                $stmt->bind_param("ii", $quantity, $product_id);
                $stmt->execute();

                // ✅ Notify Buyer
                $notification_msg_buyer = "🎉 Your order for {$product['product_name']} has been placed successfully!";
                addNotification($order_id, $user_id, $notification_msg_buyer, $image);
            }

            // ✅ Clear Cart After Order
            if (!isset($_GET['product_id'])) {
                $clear_cart = "DELETE FROM cart WHERE user_id = ?";
                $stmt = $conn->prepare($clear_cart);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
            }

            $success_message = "🎉 Order placed successfully!";
        } else {
            $success_message = "<p style='color: red;'>❌ Failed to place order.</p>";
        }
    }
}


// ✅ Function to Add Notifications
function addNotification($order_id, $userId, $message, $image = null) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifications (order_id, user_id, message, image, status, created_at) VALUES (?, ?, ?, ?, 'unread', NOW())");
    $stmt->bind_param("iiss", $order_id, $userId, $message, $image);
    $stmt->execute();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Farmers Market</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="checkout-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
 
<div class="top-nav">
    <div class="platform-name">
        <h1>Farm Flow</h1>
    </div>
    <div class="top-icons">
      <a href="buyers.php" class="main-icon">
        <i class="fas fa-home main-icon"></i>
      </a>
      <a href="user-profile.php" class="user-icon">
        <i class="fas fa-user-circle user-icon"></i>
      </a>
      <a href="favorite.php" class="favorite-icon">
        <i class="fas fa-heart favorite-icon"></i>
      </a>
      <a href="cart.php">
        <i class="fas fa-shopping-cart cart-icon"></i>
      </a>
      <a href="my-orders.php" class="orders-icon">
        <i class="fas fa-box-open orders-icon"></i>
      </a>
      <a href="notification.php" class="notifications-icon">
        <i class="fas fa-bell notifications-icon"></i>
            <?php
              require 'db.php';

              // Count Unread Notifications for the Buyer
              $notiQuery = $conn->query("SELECT COUNT(*) AS total FROM notifications WHERE user_id = {$_SESSION['user_id']} AND status = 'unread'");
              $notiCount = $notiQuery->fetch_assoc()['total'];

              // Show badge only if there are unread notifications
              if ($notiCount > 0) {
              echo "<span class='badge'>$notiCount</span>";
             }
            ?>
        </a>
      <a href="logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt logout-icon"></i>
      </a>
    </div>
</div>

<!-- ✅ SHOW SUCCESS MESSAGE BELOW NAVIGATION -->
<?php if ($success_message != ""): ?>
<div class="success-message hide">
    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
</div>
<?php endif; ?>

    <h1>Checkout</h1>
    <h2>Order Summary</h2>

    <div class="product-container">
        <?php foreach ($cart_products as $product): ?>
            <div class="product-card">
                <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                <p>Price: ₹<?php echo number_format($product['price'], 2); ?></p>
                <p>Quantity: <?php echo $product['quantity']; ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <p class="total-price"><strong>Total Price: ₹<?php echo number_format($total_price, 2); ?></strong></p>

    <h2>Delivery Details</h2>
    <form method="POST">
        <label for="address">Address:</label>
        <textarea name="address" required><?php echo htmlspecialchars($user_address); ?></textarea>

        <label for="city">City:</label>
        <input type="text" name="city" value="<?php echo htmlspecialchars($user_city); ?>" required>

        <label for="state">State:</label>
        <input type="text" name="state" value="<?php echo htmlspecialchars($user_state); ?>" required>

        <label for="phone">Phone Number:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user_phone); ?>" required>

        <label for="payment_method">Payment Method:</label>
        <select name="payment_method">
            <option value="Cash on Delivery">Cash on Delivery</option>
            <option value="UPI">UPI</option>
        </select>
        <button type="submit">Place Order</button>
    </form>
<script>
    // ✅ Auto Hide Notification After 5 Seconds
setTimeout(function() {
    var successMessage = document.querySelector('.success-message');
    if(successMessage) {
        successMessage.classList.add('hide');
    }
}, 7000);
</script>
    
</body>
</html>

<?php $conn->close(); ?>