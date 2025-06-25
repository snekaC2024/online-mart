<?php
include 'db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login-buyer.php");
    exit();
}


// Fetch only approved products for buyers
$product_result = $conn->query("SELECT * FROM products");


$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Get the logged-in user's name
$username = htmlspecialchars($_SESSION['username']);

// Insert the Review
// Assuming `reviews` table has product_id, user_id, rating, review_text
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $product_id = $_POST['product_id'];
  $user_id = $_SESSION['user_id'];
  $rating = $_POST['rating'];
  $review_text = $_POST['review_text'];

  if (!empty($rating) && $rating >= 1 && $rating <= 5) {  // Ensure rating is valid
      $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("iiis", $product_id, $user_id, $rating, $review_text);
      $stmt->execute();
      $stmt->close();
  }

  // Update avg_rating in products table dynamically
  $updateStmt = $conn->prepare("
      UPDATE products 
      SET avg_rating = (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE product_id = ?) 
      WHERE id = ?
  ");
  $updateStmt->bind_param("ii", $product_id, $product_id);
  $updateStmt->execute();
  $updateStmt->close();

  header("Location: product-details.php?id=" . $product_id);
  exit();
}


// Fetch all products and their favorite status for the logged-in user
$product_query = "
   SELECT p.id, p.product_name, p.status, p.price, p.category, p.unit, p.unit_count, p.count, p.description, p.image, f.username AS farmer_name,
    IF(fav.product_id IS NOT NULL, 1, 0) AS is_favorite,
    (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE product_id = p.id) AS avg_rating
    FROM products p
    INNER JOIN farmers f ON p.farmer_id = f.id
    LEFT JOIN favorites fav ON fav.product_id = p.id AND fav.user_id = ?
";


$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$product_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Farmers Market - Buyers</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
      
      .trusted-label {
            background-color: green;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            left: 10px; /* This should correctly position it to the top-right */
      } 
      
      .review-rating {
    color:rgb(255, 208, 0);
    font-size: 16px;
}
      
    .review-count {
      font-size: 12px;
      color: gray;
    }

    /* Bounce Animation for Favorite Icon */
    .bounce-animation {
      animation: bounce 0.3s ease-in-out;
    }
    @keyframes bounce {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.2); }
    }
  </style>
</head>
<body>
  <!-- Top Navigation --> 
  <div class="top-nav">
    <div class="user-profile"> <!--logo-->
      <div class="platform-name">
        <h1>Farm Flow</h1>
      </div>
    </div>
    <div class="top-icons"> <!--nav-icons-->
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

  <!-- Main Content -->
  <div class="main-content">
    <div class="search-bar">
      <input type="text" id="productSearch" placeholder="Search for products...">
      <button><i class="fas fa-search"></i></button>
    </div>  

    <h1>Welcome, <?php echo $username; ?>!</h1>
    <p>Find and purchase fresh produce directly from farmers.</p>

    <!-- Filter Section -->
    <div class="filter-section">
      <div class="filter-group">
        <label for="categories">Category:</label>
        <select id="categories">
          <option value="all">All</option>
          <option value="fruits">Fruits</option>
          <option value="vegetables">Vegetables</option>
          <option value="dairy">Dairy</option>
          <option value="grains">Grains</option>
          <option value="beekeeping">Beekeeping</option>
          <option value="Processed-Products">Processed Products</option>
          <option value="Flowers-Plants">Flowers & Plants</option>
          <option value="Animal-Feed">Animal Feed</option>
        </select>
      </div>
      <button id="apply-filters">Apply Filters</button>
    </div>

    <!-- Product Catalog (Category Thumbnails) -->
    <div class="catalog">
      <div class="catalog-item">
        <img src="images/fruits.jpg" alt="Fruits">
        <span>Fruits</span>
      </div>
      <div class="catalog-item">
        <img src="images/vegetable.jpg" alt="Vegetables">
        <span>Vegetables</span>
      </div>
      <div class="catalog-item">
        <img src="images/dairy.jpg" alt="Dairy">
        <span>Dairy</span>
      </div>
      <div class="catalog-item">
        <img src="images/grains.jpg" alt="Grains">
        <span>Grains</span>
      </div>
      <div class="catalog-item">
        <img src="images/beekeeping.jpg" alt="Beekeeping">
        <span>Beekeeping</span>
      </div>
      <div class="catalog-item">
        <img src="images/processed.jpg" alt="Processed Products">
        <span>Processed-Products</span>
      </div>
      <div class="catalog-item">
        <img src="images/flowers.jpg" alt="Flowers & Plants">
        <span>Flowers-Plants</span>
      </div>
      <div class="catalog-item">
        <img src="images/animal_feed.jpg" alt="Animal Feed">
        <span>Animal-Feed</span>
      </div>
    </div>  

    <!-- Farmers' Products Section -->
    <div class="productCatalog">
    <?php if ($product_result->num_rows > 0): ?>
    <?php while ($product = $product_result->fetch_assoc()): ?>
        <!-- The entire card is clickable and navigates to the product details page -->
        <div class="productCard" data-category="<?php echo htmlspecialchars($product['category']); ?>" 
             onclick="window.location.href='product-details.php?id=<?php echo $product['id']; ?>'">
            
            <!-- Show Trusted Label Only for Approved Products -->
            <?php if ($product['status'] === 'approved') { ?> 
                <span class="trusted-label">Trusted</span>
            <?php } ?>

            <!-- Favorite Icon -->
            <div class="favorite-icon-container">
                <i class="fas fa-heart favorite-product-icon <?php echo $product['is_favorite'] ? 'active' : ''; ?>" 
                   data-product-id="<?php echo $product['id']; ?>"></i>
            </div>

            <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                 alt="<?php echo htmlspecialchars($product['product_name']); ?>">
            <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>

             <!-- Rating Display --> 
            <p>Rating: 
            <span  class="review-rating">
            <?php
            $rating = round($product['avg_rating'], 1); // Round to 1 decimal place
            echo $rating . " ";
            for ($i = 1; $i <= 5; $i++) {
                echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
            }
            ?>
            </span>
        </p>
       

            <p>Price: ₹<?php echo number_format($product['price'], 2); ?></p>
            <p>Category: <?php echo htmlspecialchars($product['category']); ?></p>
            <p>Unit: <?php echo htmlspecialchars($product['unit_count']); ?></p>
            <p>Description: <?php echo htmlspecialchars($product['description']); ?></p>

            <!-- Add to Cart Button -->
            <div class="add-to-cart-btn">
              <button data-product-id="<?php echo $product['id']; ?>">Add to Cart</button>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No products available.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Favorite Icon Click Event
      const favoriteIcons = document.querySelectorAll('.favorite-product-icon');
      favoriteIcons.forEach(icon => {
        icon.addEventListener('click', (event) => {
          event.stopPropagation(); // Prevent card click event
          icon.classList.toggle('active');
          icon.classList.add('bounce-animation');
          
          // Remove bounce class after animation
          setTimeout(() => {
            icon.classList.remove('bounce-animation');
          }, 300);

          const productId = icon.getAttribute('data-product-id');
          const isFavorited = icon.classList.contains('active');

          fetch('update-favorite.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ productId, favorited: isFavorited })
          })
          .then(response => response.json())
          .then(data => {
            console.log(data.message);
          })
          .catch(error => {
            console.error('Error:', error);
          });
        });
      });

      // Add to Cart Button Click Event
      const addToCartButtons = document.querySelectorAll('.add-to-cart-btn button');
      addToCartButtons.forEach(button => {
        button.addEventListener('click', (event) => {
          event.stopPropagation(); // Prevent card click event
          const productId = button.getAttribute('data-product-id');
          const isAddedToCart = button.classList.contains('active');

          // Toggle the active class and update button text
          button.classList.toggle('active');
          button.textContent = isAddedToCart ? 'Add to Cart' : 'Added to Cart';

          fetch('update-cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ productId, addedToCart: !isAddedToCart })
          })
          .then(response => response.json())
          .then(data => {
            console.log(data.message);
          })
          .catch(error => {
            console.error('Error:', error);
          });
        });
      });
    });

    // Real-Time Search Functionality
    document.getElementById('productSearch').addEventListener('input', function () {
      let searchValue = this.value.toLowerCase();
      let products = document.querySelectorAll('.productCard');

      products.forEach(product => {
        let productName = product.querySelector('h3').innerText.toLowerCase();
        let productCategory = product.getAttribute('data-category');
        productCategory = productCategory ? productCategory.toLowerCase() : "";

        if (productName.includes(searchValue) || productCategory.includes(searchValue)) {
          product.style.display = 'block';
        } else {
          product.style.display = 'none';
        }
      });
    });

    // Catalog Item Click Event for Filtering
    document.addEventListener('DOMContentLoaded', () => {
      const catalogItems = document.querySelectorAll('.catalog-item');
      const productCards = document.querySelectorAll('.productCard');

      function filterProducts(selectedCategory) {
        selectedCategory = selectedCategory.toLowerCase();
        productCards.forEach(card => {
          let productCategory = card.getAttribute('data-category') || '';
          if (selectedCategory === 'all' || productCategory.toLowerCase() === selectedCategory) {
            card.style.display = 'block';
          } else {
            card.style.display = 'none';
          }
        });
      }

      catalogItems.forEach(item => {
        item.addEventListener('click', function () {
          const selectedCategory = this.querySelector('span').innerText.trim();
          filterProducts(selectedCategory);
        });
      });
    });

    // Dropdown Filter for Category
    document.getElementById('categories').addEventListener('change', function () {
      const selectedCategory = this.value.toLowerCase();
      const productCards = document.querySelectorAll('.productCard');
      productCards.forEach(card => {
        let productCategory = card.getAttribute('data-category') || '';
        productCategory = productCategory ? productCategory.toLowerCase() : "";
        if (selectedCategory === 'all' || productCategory === selectedCategory) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });
  </script>

  <!-- Bottom Navigation -->
  <div class="bottom-nav">
    <div class="buyers-icon active">
      <i class="fas fa-users buyers"></i>
    </div>
    <div class="farmers-icon" onclick="window.location.href='register-farmer.php'">
      <i class="fas fa-tractor farmers"></i>
    </div>
  </div>
</body>
</html>

<?php
$conn->close();
?>
