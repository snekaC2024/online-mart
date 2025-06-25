<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login-buyer.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get logged-in user's ID

// Database Connection
include 'db.php';

// Fetch favorite products
$favorite_products_query = "
    SELECT p.id, p.product_name, p.price, p.category, p.image
    FROM products p
    INNER JOIN favorites f ON p.id = f.product_id
    WHERE f.user_id = ?
";

$stmt = $conn->prepare($favorite_products_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$favorites = [];
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Favorite Products</title>
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
           </a>
            <a href="logout.php" class="logout-btn">
              <i class="fas fa-sign-out-alt logout-icon"></i>
            </a>
        </div>
    </div>
    <!-- Main Content -->
    <div class="main-content">
        <h1>Your Favorite Products</h1>

        <div class="productCatalog">
            <?php if (count($favorites) > 0): ?>
                <?php foreach ($favorites as $product): ?>
                    <div class="productCard" data-category="<?php echo htmlspecialchars($product['category']); ?>">
                        <!-- Favorite Icon -->
                        <div class="favorite-icon-container">
                          <i class="fas fa-heart favorite-product-icon active" data-product-id="<?php echo $product['id']; ?>"></i>
                        </div>
                        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        <p>Price: ₹<?php echo number_format($product['price'], 2); ?></p>
                        <p>Category: <?php echo htmlspecialchars($product['category']); ?></p>
                        
                        <!-- Add to Cart Button -->
                        <a href="cart.php?product_id=<?php echo $product['id']; ?>" class="add-to-cart-btn">
                            <button>Add to Cart</button>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>You have no favorite products yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <div class="buyers-icon active">
            <i class="fas fa-users buyers"></i>
        </div>
        <div class="farmers-icon" onclick="window.location.href='register-farmer.php'">
            <i class="fas fa-tractor farmers"></i>
        </div>
    </div>

    <script>
        // Favorite icon toggle functionality (optional)
        document.addEventListener('DOMContentLoaded', () => {
            const favoriteIcons = document.querySelectorAll('.favorite-product-icon');

            favoriteIcons.forEach(icon => {
                icon.addEventListener('click', () => {
                    // Toggle the 'active' class on the clicked icon
                    icon.classList.toggle('active');

                    const productId = icon.getAttribute('data-product-id'); // Get product ID
                    const isFavorited = icon.classList.contains('active'); // Check if favorited

                    // Optionally send an AJAX request to update the favorite status in the database
                    fetch('update-favorite.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ productId, favorited: isFavorited })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data.message); // Optional feedback
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });
        });
    </script>
</body>
</html>
