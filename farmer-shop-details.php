<?php
session_start();

// Check if farmer ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid farmer ID.");
}
$farmer_id = intval($_GET['id']);

// Database Connection
include 'db.php';

// Fetch farmer details
$sql_farmer = "SELECT * FROM farmers WHERE id = ?";
$stmt_farmer = $conn->prepare($sql_farmer);
$stmt_farmer->bind_param("i", $farmer_id);
$stmt_farmer->execute();
$result_farmer = $stmt_farmer->get_result();

if ($result_farmer->num_rows === 0) {
    die("Farmer not found.");
}
$farmer = $result_farmer->fetch_assoc();

// Fetch farmer's products
$sql_products = "SELECT * FROM products WHERE farmer_id = ?";
$stmt_products = $conn->prepare($sql_products);
$stmt_products->bind_param("i", $farmer_id);
$stmt_products->execute();
$products_result = $stmt_products->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($farmer['shop_name']); ?> - Shop Details</title>
    <link rel="stylesheet" href="styles.css">
    
    <style>
        /* General Styles */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f5f5f5;
}

/* Shop Container */
.shop-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.shop-container h1 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.shop-container p {
    font-size: 16px;
    color: #555;
    margin: 8px 0;
}

/* Products Container */
.products-container {
    max-width: 1100px;
    margin: 40px auto;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.products-container h2 {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 20px;
}

/* Product List */
.product-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
}

/* Individual Product Item */
.product-item {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    width: 250px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s;
}

.product-item:hover {
    transform: translateY(-5px);
}

.product-item img {
    max-width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 6px;
}

.product-item h3 {
    font-size: 18px;
    color: #333;
    margin: 10px 0;
}

.product-item p {
    font-size: 16px;
    color: #B12704;
    font-weight: bold;
}

.product-item a {
    display: inline-block;
    margin-top: 10px;
    padding: 8px 12px;
    background: #2c3e50;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    transition: background 0.3s;
}

.product-item a:hover {
    background: #1a252f;
}

/* Responsive Design */
@media (max-width: 768px) {
    .product-list {
        flex-direction: column;
        align-items: center;
    }

    .product-item {
        width: 90%;
    }
}

    </style>
</head>
<body>
    <div class="shop-container">
        <h1><?php echo htmlspecialchars($farmer['shop_name']); ?></h1>
        <p><strong>Owner:</strong> <?php echo htmlspecialchars($farmer['username']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($farmer['address']); ?></p>
        <p><strong>Contact:</strong> <?php echo htmlspecialchars($farmer['phone']); ?></p>
    </div>
    
    <div class="products-container">
        <h2>Featured Products</h2>
        <div class="product-list">
            <?php while ($product = $products_result->fetch_assoc()): ?>
                <div class="product-item">
                    <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                    <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                    <p>₹<?php echo number_format($product['price'], 2); ?></p>
                    <a href="product-details.php?id=<?php echo $product['id']; ?>">View Details</a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
