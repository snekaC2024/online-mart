<?php
session_start();

// Database Connection
include 'db.php';

// Delete functionality
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    // Create connection
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Delete query
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Product deleted successfully.";
    } else {
        $_SESSION['message'] = "Error deleting product: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    // Redirect to the same page after deletion
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Edit functionality
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    
    if (isset($_POST['update_product']) && isset($_GET['edit_id'])) {
        $edit_id = intval($_GET['edit_id']); // Get the product ID to edit
        $product_name = $_POST['product_name'];
        $price = floatval($_POST['price']);
        $category = $_POST['category'];
        $unit = $_POST['unit'];
        $unit_count = $_POST['unit_count'];
        $count = intval($_POST['count']);
        $description = $_POST['description'];
    
        $conn = new mysqli($servername, $db_username, $db_password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    
        $stmt = $conn->prepare("UPDATE products SET product_name = ?, price = ?, category = ?, unit = ?, unit_count = ?, count = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sdssssss", $product_name, $price, $category, $unit, $unit_count, $count, $description, $edit_id);
    
        if ($stmt->execute()) {
            $_SESSION['message'] = "Product updated successfully.";
        } else {
            $_SESSION['message'] = "Error updating product: " . $stmt->error;
        }
    
        $stmt->close();
        $conn->close();
    
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Fetch the product to edit
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login-farmer.php'); // Redirect to login page if not logged in
    exit();
}

// Get the logged-in farmer's username
$username = $_SESSION['username'];


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("
    SELECT p.id, p.product_name, p.price, p.category, p.unit, p.unit_count, p.count, p.description, p.image 
    FROM products p
    INNER JOIN farmers f ON p.farmer_id = f.id
    WHERE f.username = ?
");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .product-header {
            display: flex;
            align-items: center;
            padding: 10px;
            background-color: #f4f4f4;
            border-bottom: 1px solid #ddd;
        }
        .left-arrow {
            padding: 5px;
            cursor: pointer;
            font-size: 20px;
            background:rgb(14, 111, 215);
            color:#ffffff;
            border-radius: 5px;
        }
        .left-arrow:hover {
            color: #0056b3;
            background: white;
        }
        .action-buttons {
            display: flex;
            justify-content:center;
            display: flex;
            gap: 10px;
        }
        .edit-btn, .delete-btn {
            padding: 8px 15px;
            text-decoration: none;
            color: #fff;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
            margin-bottom:10px;
        }
        .edit-btn {
            background-color: #007bff; /* Blue for Edit */
        }
        .edit-btn:hover {
            background-color: #0056b3;
        }
        .delete-btn {
            background-color: #dc3545; /* Red for Delete */
        }
        .delete-btn:hover {
            background-color: #a71d2a;
        }
        .message {
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>

    
<script>       
    function updateUnitCount() {
    const unit = document.getElementById('unit').value;
    const unitCount = document.getElementById('unit_count');
    unitCount.innerHTML = ''; // Clear existing options

    // Define unit-specific options
    const options = {
        Kg: ['Half Kg', '1 Kg', '2 Kg', '5 Kg', '10 Kg'],
        Gram: ['100 Gram', '250 Gram', '500 Gram', '750 Gram', '1 Kg'],
        Liter: ['Half Liter', '1 Liter', '2 Liter', '5 Liter', '10 Liter'],
        Dozen: ['Half Dozen', '1 Dozen', '2 Dozen', '5 Dozen', '10 Dozen'],
        Piece: ['1 Piece', '5 Pieces', '10 Pieces', '20 Pieces', '50 Pieces'],
        Bundle: ['1 Bundle', '5 Bundles', '10 Bundles', '20 Bundles', '50 Bundles'],
        Sack: ['1 Sack', '2 Sacks', '5 Sacks', '10 Sacks'],
        Jar: ['1 Jar', '2 Jars', '5 Jars', '10 Jars'],
        Pack: ['1 Pack', '5 Packs', '10 Packs', '20 Packs'],
        Box: ['1 Box', '2 Boxes', '5 Boxes', '10 Boxes'],
        Tray: ['1 Tray', '2 Trays', '5 Trays', '10 Trays']
    };

    // Populate the unit count dropdown based on the selected unit
    if (options[unit]) {
        options[unit].forEach(count => {
            const option = document.createElement('option');
            option.value = count;
            option.textContent = count;
            unitCount.appendChild(option);
        });
    } else {
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Select a unit count';
        unitCount.appendChild(defaultOption);
    }
  }

</script>
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
        <a href="farmer-profile.php">
            <i class="fas fa-user-circle user-icon"></i>
        </a>   
        <a href="product-list.php" class="product-list-btn">
            <i class="fas fa-boxes-stacked product-list-icon"></i>
        </a>
        <a href="booking-orders.php" class="booking-orders-btn">
            <i class="fas fa-clipboard-list booking-orders-icon"></i>
        </a>
        <a href="notifications-farmer.php" class="notifications-btn">
            <i class="fas fa-bell notifications-icon"></i>
        </a>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt logout-icon"></i>
        </a>
    </div>
    </div>

    <div class="product-header">
        <div class="left-arrow" onclick="goBack()">
            <i class="fas fa-arrow-left"></i>
        </div>
    </div>

    <div class="main-content">
        <h1>My Products</h1>

        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
        }
        ?>

        <?php if (isset($_GET['edit_id'])): ?>
            <section class="add-product-section">
        <h2>Edit Product</h2>
        <form action="?edit_id=<?php echo $product['id']; ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

            <label for="product_name">Product Name:</label>
            <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>

            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="">Select a category</option>
                <option value="Fruits" <?php echo $product['category'] == 'Fruits' ? 'selected' : ''; ?>>Fruits</option>
                <option value="Vegetables" <?php echo $product['category'] == 'Vegetables' ? 'selected' : ''; ?>>Vegetables</option>
                <option value="Dairy" <?php echo $product['category'] == 'Dairy' ? 'selected' : ''; ?>>Dairy</option>
                <option value="Grains" <?php echo $product['category'] == 'Grains' ? 'selected' : ''; ?>>Grains</option>
                <option value="Beekeeping" <?php echo $product['category'] == 'Beekeeping' ? 'selected' : ''; ?>>Beekeeping</option>
                <option value="Processed-Products" <?php echo $product['category'] == 'Processed-Products' ? 'selected' : ''; ?>>Processed Products</option>
                <option value="Flowers-Plants" <?php echo $product['category'] == 'Flowers-Plants' ? 'selected' : ''; ?>>Flowers & Plants</option>
                <option value="Animal-Feed" <?php echo $product['category'] == 'Animal-Feed' ? 'selected' : ''; ?>>Animal Feed</option>
            </select>

            <label for="unit">Unit:</label>
            <select id="unit" name="unit" required onchange="updateUnitCount()">
                <option value="">Select a unit</option>
                <option value="Kg" <?php echo $product['unit'] == 'Kg' ? 'selected' : ''; ?>>Kg</option>
                <option value="Gram" <?php echo $product['unit'] == 'Gram' ? 'selected' : ''; ?>>Gram</option>
                <option value="Liter" <?php echo $product['unit'] == 'Liter' ? 'selected' : ''; ?>>Liter</option>
                <option value="Dozen" <?php echo $product['unit'] == 'Dozen' ? 'selected' : ''; ?>>Dozen</option>
                <option value="Piece" <?php echo $product['unit'] == 'Piece' ? 'selected' : ''; ?>>Piece</option>
                <option value="Bundle" <?php echo $product['unit'] == 'Bundle' ? 'selected' : ''; ?>>Bundle</option>
                <option value="Sack" <?php echo $product['unit'] == 'Sack' ? 'selected' : ''; ?>>Sack</option>
                <option value="Jar" <?php echo $product['unit'] == 'Jar' ? 'selected' : ''; ?>>Jar</option>
                <option value="Pack" <?php echo $product['unit'] == 'Pack' ? 'selected' : ''; ?>>Pack</option>
                <option value="Box" <?php echo $product['unit'] == 'Box' ? 'selected' : ''; ?>>Box</option>
                <option value="Tray" <?php echo $product['unit'] == 'Tray' ? 'selected' : ''; ?>>Tray</option>
            </select>

            <label for="unit_count">Unit Count:</label>
            <select id="unit_count" name="unit_count" required>
                <option value="">Select a unit count</option>
                <!-- Dynamic options will be populated here -->
            </select>

            <label for="count">Product Quantity:</label>
            <input type="number" id="count" name="count" value="<?php echo htmlspecialchars($product['count']); ?>" min="1" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>

            <label for="image">Product Image:</label>
            <input type="file" id="image" name="image">
            <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" width="100" alt="Product Image">

            <button type="submit" name="update_product">Update Product</button>
        </form>
    </section>
        <?php else: ?>
            <?php if ($result->num_rows > 0): ?>
                <div class="productCatalog">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="productCard">
                            <!-- Edit and Delete Buttons -->
                            <div class="action-buttons">
                                <a href="?edit_id=<?php echo $row['id']; ?>" class="icon-btn edit-btn" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?id=<?php echo $row['id']; ?>" class="icon-btn delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this product?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                            <div class="productDetails">
                                <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                                <p class="price">Price: ₹<?php echo htmlspecialchars(number_format($row['price'], 2)); ?></p>
                                <p>Category: <?php echo htmlspecialchars($row['category']); ?></p>
                                <p>Unit: <?php echo htmlspecialchars($row['unit']); ?> (<?php echo htmlspecialchars($row['unit_count']); ?>)</p>
                                <p>Quantity: <?php echo htmlspecialchars($row['count']); ?></p>
                                <p>Description: <?php echo htmlspecialchars($row['description']); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No products added yet. Add your first product <a href="farmers.php">here</a>.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="bottom-nav">
        <div class="buyers-icon" onclick="window.location.href='register-buyer.php'">
            <i class="fas fa-users buyers"></i>
        </div>
        
        <div class="farmers-icon active">
            <i class="fas fa-tractor farmers"></i>
        </div>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
