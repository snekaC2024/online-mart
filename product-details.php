<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login-buyer.php");
    exit();
}

// Database Connection
include 'db.php';

// Validate and get the product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID.");
}
$product_id = intval($_GET['id']);

// Fetch product details with farmer info
$sql_product = "SELECT products.*, farmers.shop_name 
                FROM products 
                JOIN farmers ON products.farmer_id = farmers.id 
                WHERE products.id = ?";
$stmt_product = $conn->prepare($sql_product);
$stmt_product->bind_param("i", $product_id);
$stmt_product->execute();
$result_product = $stmt_product->get_result();

if ($result_product->num_rows === 0) {
    die("Product not found.");
}
$product = $result_product->fetch_assoc();

// Get average rating and review count
$sql_avg = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE product_id = ?";
$stmt_avg = $conn->prepare($sql_avg);
$stmt_avg->bind_param("i", $product_id);
$stmt_avg->execute();
$result_avg = $stmt_avg->get_result();
$ratingData = $result_avg->fetch_assoc();
$avg_rating   = round($ratingData['avg_rating'], 1);
$review_count = $ratingData['review_count'];

// Fetch reviews
$sql_reviews = "SELECT r.*, u.username FROM reviews r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.product_id = ? ORDER BY r.created_at DESC";
$stmt_reviews = $conn->prepare($sql_reviews);
$stmt_reviews->bind_param("i", $product_id);
$stmt_reviews->execute();
$reviews_result = $stmt_reviews->get_result();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> - Product Details</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        
.product-container {
    display: flex;
    max-width: 700px;
    margin: 50px auto;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.product-image img {
    width: 300px;
    height: auto;
    border-radius: 10px;
}

.product-details {
    margin-left: 20px;
}

.product-details h2 {
    color: #333;
    font-size: 24px;
    margin-bottom: 10px;
}

.price {
    color: #e44d26;
    font-size: 22px;
    font-weight: bold;
}

.stock {
    color: green;
    font-size: 16px;
}

.rating i {
    color: gold;
}

.buy-buttons {
    margin-top: 20px;
}

.buy-buttons button {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    border-radius: 5px;
    margin-right: 10px;
    transition: background 0.3s;
}

.buy-buttons button:hover {
    background-color: #218838;
}

.review-section {
    max-width: 900px;
    margin: 20px auto;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Grid layout for reviews */
.review-grid {
    display: grid;
    grid-template-columns:1fr 1fr;
    gap: 15px;
}

.review {
    border: 1px solid #ddd;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
    background: #f1f1f1;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: bold;
    margin-bottom: 5px;
}

.review-user {
    color: #333;
    font-size: 16px;
}

.review-rating {
    color:rgb(255, 208, 0);
    font-size: 16px;
}

.review-comment .review-text{
    text-align: left;
    font-size: 14px;
    color: #555;
}


.review-actions {
    display: flex;
    justify-content: center;
    margin-top: 5px;
}

.review-actions button {
    margin: 5px;
    padding: 5px 10px;
    border: none;
    background-color: #007bff;
    color: white;
    cursor: pointer;
    border-radius: 4px;
}

.review-actions button:hover {
    background-color: #0056b3;
}

.review-form textarea {
    width: 100%;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.review-form button {
    margin-top: 10px;
    background-color: #007bff;
    color: white;
    padding: 10px 15px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    transition: background 0.3s;
}

.review-form button:hover {
    background-color: #0056b3;
}

/* Notification Message */
.notification {
    display: none;
    position: fixed;
    top: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    font-weight: bold;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
}

.hidden-review {
    display: none;
}

.view-more-btn {
    margin-top: 10px;
    background-color: #007bff;
    color: white;
    padding: 10px 15px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    transition: background 0.3s;
}

.view-more-btn:hover {
    background-color: #0056b3;
}
    </style>
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

    <div class="notification" id="notification">Review Submitted Successfully!</div>

    <div class="product-container">
        <div class="product-image">
            <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image">
        </div>
        <div class="product-details">
            <h2><?php echo htmlspecialchars($product['product_name']); ?></h2>
            <p class="price">₹<?php echo number_format($product['price'], 2); ?></p>
            <p class="stock">In Stock: <?php echo htmlspecialchars($product['count']); ?> available</p>
            <p><strong>Sold by:</strong> <a href="farmer-shop-details.php?id=<?php echo $product['farmer_id']; ?>"><?php echo htmlspecialchars($product['shop_name']); ?></a></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
            <p><strong>Unit:</strong> <?php echo htmlspecialchars($product['unit_count']); ?></p>
            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            <div class="rating">
                <?php for ($i = 1; $i <= 5; $i++) {
                    echo ($i <= $avg_rating) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                } ?>
                <span>(<?php echo $review_count; ?> reviews)</span>
            </div>
            <div class="buy-buttons">
                <button onclick="window.location.href='checkout.php?product_id=<?php echo $product['id']; ?>&price=<?php echo $product['price']; ?>'">Buy Now</button>
                <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">Add to Cart</button>
            </div>
        </div>
    </div>

    <div class="sold-by-details">
        <h2>Sold by <b> <a href="farmer-shop-details.php?id=<?php echo $product['farmer_id']; ?>"><?php echo htmlspecialchars($product['shop_name']); ?></a></b></h2>
    </div>

    <div class="review-section">
    <h3>Customer Reviews</h3>
    <div class="review-grid">
        <?php 
        $count = 0;
        $loggedInUser = $_SESSION['user_id']; // Get the logged-in user ID

        while ($review = $reviews_result->fetch_assoc()): 
            $hiddenClass = $count >= 4 ? 'hidden-review' : ''; // Hide reviews after the fourth one
            $isOwner = $review['user_id'] == $loggedInUser; // Check if the review belongs to logged-in user
        ?>
            <div class="review <?php echo $hiddenClass; ?>" data-review-id="<?php echo $review['id']; ?>">
                <div class="review-header">
                    <span class="review-user"><?php echo htmlspecialchars($review['username']); ?></span>
                    <span class="review-rating">
                        <?php for ($i = 1; $i <= 5; $i++) {
                            echo ($i <= $review['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                        } ?>
                    </span>
                </div>
                <div class="review-comment">
                    <p class="review-text"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                </div>

                <?php if ($isOwner): ?>
                    <div class="review-actions">
                        <button class="edit-review-btn" data-id="<?php echo $review['id']; ?>" data-rating="<?php echo $review['rating']; ?>" data-comment="<?php echo htmlspecialchars($review['comment']); ?>">Edit</button>
                        <button class="delete-review-btn" data-id="<?php echo $review['id']; ?>">Delete</button>
                    </div>
                <?php endif; ?>
            </div>
        <?php 
        $count++;
        endwhile; 
        ?>

        <?php if ($review_count > 4): ?>
            <button id="viewMoreBtn" class="view-more-btn">View More</button>
        <?php endif; ?>
    </div>




    <div class="review-form">
            <h4>Write a Review</h4>
            <form id="reviewForm">
            <input type="hidden" id="product_id" value="<?php echo $product_id; ?>">
                <label for="rating">Rating:</label>
                <select name="rating" id="rating" required>
                    <option value="">Select rating</option>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Good</option>
                    <option value="3">3 - Average</option>
                    <option value="2">2 - Poor</option>
                    <option value="1">1 - Terrible</option>
                </select>
                <br>
                <label for="comment">Comment:</label>
                <br>
                <textarea name="comment" id="comment" rows="4" required></textarea>
                <br>
                <button type="submit">Submit Review</button>
            </form>
    </div>
 
</div>

    <script>
        document.querySelector('.add-to-cart-btn').addEventListener('click', function() {
            let productId = this.getAttribute('data-product-id');
            fetch('update-cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ productId, addedToCart: true })
            }).then(response => response.json())
            .then(data => { alert(data.message); })
            .catch(error => { console.error('Error:', error); });
        });
    </script>

<script>
document.getElementById("reviewForm").addEventListener("submit", function(event) {
    event.preventDefault();

    fetch("submit-review.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            product_id: document.getElementById("product_id").value,
            rating: document.getElementById("rating").value,
            comment: document.getElementById("comment").value
        })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById("notification").style.display = "block";
        setTimeout(() => location.reload(), 1500);
    });
});
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    let viewMoreBtn = document.getElementById("viewMoreBtn");
    if (viewMoreBtn) {
        viewMoreBtn.addEventListener("click", function () {
            document.querySelectorAll(".hidden-review").forEach(review => review.style.display = "block");
            viewMoreBtn.style.display = "none"; // Hide the button after showing reviews
        });
    }
    });   
</script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Handle Edit Review
    document.querySelectorAll(".edit-review-btn").forEach(button => {
        button.addEventListener("click", function () {
            let id = this.getAttribute("data-id");
            let existingRating = this.getAttribute("data-rating");
            let existingComment = this.getAttribute("data-comment");

            let newRating = prompt("Update Rating (1-5):", existingRating);
            let newComment = prompt("Update Comment:", existingComment);

            if (newRating && newComment) {
                fetch("edit-review.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `id=${id}&rating=${newRating}&comment=${encodeURIComponent(newComment)}`
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                });
            }
        });
    });

    // Handle Delete Review
    document.querySelectorAll(".delete-review-btn").forEach(button => {
        button.addEventListener("click", function () {
            let id = this.getAttribute("data-id");
            console.log("Deleting review with ID:", id); // Debugging line

            if (confirm("Are you sure you want to delete this review?")) {
                fetch("delete-review.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                });
            }
        });
    });
});

</script>

</body>
</html>
<?php $conn->close(); ?>
