<?php
session_start();

// Database Connection
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login-farmer.php'); // Redirect to login page if not logged in
    exit();
}

// Get the logged-in farmer's username
$username = $_SESSION['username'];


// Handle product addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch data from the form
    $farmerId = 1; // For demo purposes, assume farmerId = 1 (you can fetch it from the session)
    $productName = $_POST['product_name'];
    $price = $_POST['price'];
    $category = $_POST['category']; // This fetches the category
    $unit = $_POST['unit'];
    $unitCount = $_POST['unit_count'];
    $count = $_POST['count'];
    $description = $_POST['description'];
    $image = $_FILES['image'];

    if (!empty($productName) && !empty($price) && !empty($category) && !empty($unit) && !empty($unitCount) && !empty($description) && !empty($count) && !empty($image['name'])) {
        // Image upload
        $targetDir = "uploads/";
        $imageFileName = basename($image['name']);
        $targetFilePath = $targetDir . $imageFileName;
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($image['tmp_name'], $targetFilePath)) {
                // Insert product into database
                $stmt = $conn->prepare("
                   INSERT INTO products (farmer_id, product_name, price, category, unit, unit_count, count, description, image) 
                   VALUES ((SELECT id FROM farmers WHERE username = ?), ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("ssdssssss", $username, $productName, $price, $category, $unit, $unitCount, $count, $description, $imageFileName);


                if ($stmt->execute()) {
                    $productAdded = true;  // Set success flag
                } else {
                    $productAdded = false;
                }
                $stmt->close();
            } else {
                echo "<p>Failed to upload image.</p>";
            }
        } else {
            echo "<p>Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.</p>";
        }
    } else {
        echo "<p>Please fill out all fields and upload an image.</p>";
    }
}

// PHP section to display notification on success
if (isset($productAdded) && $productAdded) {
    echo "<script>document.getElementById('success-notification').style.display = 'block'; setTimeout(function(){ document.getElementById('success-notification').style.display = 'none'; }, 5000);</script>";
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmers - Add Products</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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
    <!-- Notification Message (Initially Hidden) -->
    <div id="success-notification" class="notification">
        Product added successfully!
    </div>

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

    <div class="main-content">
        <div class="search-bar">
           <input type="text" placeholder="Search for products...">
           <button><i class="fas fa-search"></i></button>
        </div>  

        <h1>Welcome, <?php echo $username; ?>!</h1>
        <p>Find and purchase fresh produce directly from farmers.</p>

        <section class="add-product-section">
           <h2>Add Your Products</h2>
           <form action="farmers.php" method="POST" enctype="multipart/form-data">
               <label for="product_name">Product Name:</label>
               <input type="text" id="product_name" name="product_name" required>
        
               <label for="price">Price:</label>
               <input type="number" id="price" name="price" step="0.01" required>

               <label for="category">Category:</label>
               <select id="category" name="category" required>
                    <option value="">Select a category</option>
                    <option value="Fruits">Fruits</option>
                    <option value="Vegetables">Vegetables</option>
                    <option value="Dairy">Dairy</option>
                    <option value="Grains">Grains</option>
                    <option value="Beekeeping">Beekeeping</option>
                    <option value="Processed-Products">Processed-Products</option>
                    <option value="Flowers-Plants">Flowers-Plants</option>
                    <option value="Animal-Feed">Animal-Feed</option>
               </select>

               <label for="unit">Unit:</label>
               <select id="unit" name="unit" required onchange="updateUnitCount()">
                    <option value="">Select a unit</option>
                    <option value="Kg">Kg</option>
                    <option value="Gram">Gram</option>
                    <option value="Liter">Liter</option>
                    <option value="Dozen">Dozen</option>
                    <option value="Piece">Piece</option>
                    <option value="Bundle">Bundle</option>
                    <option value="Sack">Sack</option>
                    <option value="Jar">Jar</option>
                    <option value="Pack">Pack</option>
                    <option value="Box">Box</option>
                    <option value="Tray">Tray</option>
               </select>

               <label for="unit_count">Unit Count:</label>
               <select id="unit_count" name="unit_count" required>
                 <option value="">Select a unit count</option>
                 <!-- Dynamic options will be populated here -->
               </select>
        
               <label for="count">Product Quantity:</label>
               <input type="number" id="count" name="count" min="1" required>

               <label for="description">Description:</label>
               <textarea id="description" name="description" required></textarea>
         
               <label for="image">Product Image:</label>
               <input type="file" id="image" name="image" required>
        
               <button type="submit">Add Product</button>
           </form>
       </section>
    </div>

    <div class="bottom-nav">
        <div class="buyers-icon" onclick="window.location.href='register-buyer.php'" >
            <i class="fas fa-users buyers"></i>
        </div>
    
        <div class="farmers-icon active">
            <i class="fas fa-tractor farmers"></i>
        </div>
    </div>

    <script>
        // Show success notification for 5 seconds if product is added
        <?php if (isset($productAdded) && $productAdded): ?>
            document.getElementById('success-notification').style.display = 'block';
            setTimeout(function() {
                document.getElementById('success-notification').style.display = 'none';
            }, 5000);
        <?php endif; ?>
    </script>
    
</body>
</html>
