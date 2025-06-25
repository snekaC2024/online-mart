<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login-buyer.php");
    exit();
}

// Database Connection
include 'db.php';

$user_id = $_SESSION['user_id'];

// Fetch orders for the logged-in user
$order_query = "
    SELECT o.id AS order_id, o.order_date, o.total_amount, o.status, 
           p.product_name, p.image, oi.quantity, p.price, f.username AS farmer_name
    FROM orders o
    INNER JOIN order_items oi ON o.id = oi.order_id
    INNER JOIN products p ON oi.product_id = p.id
    INNER JOIN farmers f ON p.farmer_id = f.id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC";

$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$order_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Farmers Market</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .order-container {
            width: 80%;
            margin: 20px auto;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fff;
        }
        .order-header {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .order-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .order-item img {
            width: 200px;
            height: 180px;
            border-radius: 5px;
            margin-right: 10px;
        }
        .order-details {
            flex: 1;
        }
        .status {
            font-weight: bold;
            color: green;
        }
        .pending {
            color: orange;
        }
        .delivered {
            color: blue;
        }
        .cancelled {
            color: red;
        }
    </style>
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

<div class="order-container">
    <h2>My Orders</h2>

    <?php if ($order_result->num_rows > 0): ?>
        <?php while ($order = $order_result->fetch_assoc()): ?>
            <div class="order-header">
                <p>Order ID: <?php echo $order['order_id']; ?> | Date: <?php echo $order['order_date']; ?></p>
            </div>
            <div class="order-item">
    <img src="uploads/<?php echo htmlspecialchars($order['image']); ?>" alt="Product Image">
    <div class="order-details">
        <p><strong><?php echo htmlspecialchars($order['product_name']); ?></strong></p>
        <p>Farmer: <?php echo htmlspecialchars($order['farmer_name']); ?></p>
        <p>Price: ₹<?php echo number_format($order['price'], 2); ?> | Quantity: <?php echo $order['quantity']; ?></p>
        <p>Total: ₹<?php echo number_format($order['total_amount'], 2); ?></p>
        <p class="status <?php echo strtolower($order['status']); ?>">Status: <?php echo $order['status']; ?></p>

        <?php if (strtolower(trim($order['status'])) !== 'cancelled' && strtolower(trim($order['status'])) !== 'delivered'): ?>
            <button class="cancel-btn" data-order-id="<?php echo $order['order_id']; ?>">Cancel Order</button>
        <?php endif; ?>
    </div>


            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No orders found.</p>
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".cancel-btn").forEach(button => {
        button.addEventListener("click", function () {
            let orderId = this.getAttribute("data-order-id");
            console.log("Cancel request for Order ID:", orderId); // Debugging
            if (confirm("Are you sure you want to cancel this order?")) {
                fetch("cancel_order.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "order_id=" + orderId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => console.error("Error:", error));
            }
        });
    });
});
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
