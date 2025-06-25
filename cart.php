<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("You need to login first.");
}

$user_id = $_SESSION['user_id'];

$cart_query = "SELECT c.product_id, p.product_name, p.image, p.price, p.category, p.unit_count, p.description, c.quantity 
               FROM cart c
               JOIN products p ON c.product_id = p.id
               WHERE c.user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();
$cart_products = $cart_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Farmers Market</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
     <!-- Top Navigation -->
  <div class="top-nav">
    <div class="user-profile">
      <div class="platform-name">
        <h1>Farm Flow</h1>
      </div>
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

<div class="cart-content">
    <h1>Your Cart</h1>
    
    <?php if (empty($cart_products)): ?>
        <p>Your cart is empty. Please add products to your cart.</p>
    <?php else: ?>
        <div class="cart-items">
            <?php foreach ($cart_products as $product): ?>
                <div class="cart-item">
                    <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                    <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                    <p>Price: ₹<?php echo number_format($product['price'], 2); ?></p>
                    <p>Category: <?php echo htmlspecialchars($product['category']); ?></p>
                    <p>Unit: <?php echo htmlspecialchars($product['unit_count']); ?></p>
                    <p>Description: <?php echo htmlspecialchars($product['description']); ?></p>

                    <!-- Quantity Controls -->
                    <div class="quantity-controls">
                        <a href="update-cart.php?product_id=<?php echo $product['product_id']; ?>&action=decrease" class="quantity-btn">-</a>
                        <span><?php echo $product['quantity']; ?></span>
                        <a href="update-cart.php?product_id=<?php echo $product['product_id']; ?>&action=increase" class="quantity-btn">+</a>
                    </div>

                    <!-- Remove from Cart Button -->
                    <a href="remove-from-cart.php?product_id=<?php echo $product['product_id']; ?>" class="remove-btn">Remove</a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Checkout Button -->
        <a href="checkout.php" class="checkout-btn">
            <button>Proceed to Checkout</button>
        </a>
    <?php endif; ?>
</div>

</body>
</html>

<?php $conn->close(); ?>
