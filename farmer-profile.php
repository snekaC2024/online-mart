<?php
session_start();

// Database connection
include 'db.php';

// Check if farmer is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login-farmer.php");
    exit();
}

$farmer_id = $_SESSION['user_id'];

// Fetch farmer data
$query = "SELECT * FROM farmers WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
$farmer = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $shop_name = $_POST['shop_name'];
    $shop_type = $_POST['shop_type'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];
    $bio = $_POST['bio'];

    $update_query = "UPDATE farmers 
                     SET username = ?, email = ?, phone = ?, shop_name = ?, shop_type = ?, 
                         address = ?, city = ?, state = ?, zip = ?, bio = ? 
                     WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssssssssi", $username, $email, $phone, $shop_name, $shop_type, $address, $city, $state, $zip, $bio, $farmer_id);

    if ($stmt->execute()) {
         // Update the session variable with the new username
        $_SESSION['username'] = $username;
        $success_message = "Profile updated successfully!";
        // Refresh the page to reflect the updated values
        header("Location: farmer-profile.php");
        exit();
    } else {
        $error_message = "Error updating profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Profile</title>
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

    <div class="container">
        <h2>Update Profile</h2>
        <?php if (isset($success_message)) echo "<p style='color:green;'>$success_message</p>"; ?>
        <?php if (isset($error_message)) echo "<p style='color:red;'>$error_message</p>"; ?>

        <form method="POST">
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($farmer['username']); ?>" required><br>

            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($farmer['email']); ?>" required><br>

            <label>Phone:</label>
            <input type="text" name="phone" minlength="10" maxlength="10"  value="<?php echo htmlspecialchars($farmer['phone']); ?>" required><br>

            <label>Shop Name:</label>
            <input type="text" name="shop_name" value="<?php echo htmlspecialchars($farmer['shop_name']); ?>" required><br>

            <label for="shop_type">Shop Type:</label>
            <select name="shop_type" id="shop_type" required>
                <option value="">Select Shop Type</option>
                <option value="Vegetable Farm">Vegetable Farm</option>
                <option value="Fruit Orchard">Fruit Orchard</option>
                <option value="Dairy Farm">Dairy Farm</option>
                <option value="Poultry Farm">Poultry Farm</option>
                <option value="Grain Supplier">Grain Supplier</option>
                <option value="Organic Farm">Organic Farm</option>
                <option value="Herbal & Medicinal Plants">Herbal & Medicinal Plants</option>
                <option value="Florist / Flower Farm">Florist / Flower Farm</option>
                <option value="Spice Farm">Spice Farm</option>
                <option value="Dry Fruits & Nuts Supplier">Dry Fruits & Nuts Supplier</option>
                <option value="Bee Keeping Products Supplier">Bee Keeping Products Supplier</option>
                <option value="Handmade & Artisanal Goods">Handmade & Artisanal Goods</option>
                <option value="Seed Supplier">Seed Supplier</option>
                <option value="Animal Feeds">Animal Feeds Supplier</option>
                <option value="Farm Equipment & Tools">Farm Equipment & Tools</option>
            </select>
            <br>

            <label>Address:</label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($farmer['address']); ?>" required><br>

            <label>City:</label>
            <input type="text" name="city" value="<?php echo htmlspecialchars($farmer['city']); ?>" required><br>

            <label>State:</label>
            <input type="text" name="state" value="<?php echo htmlspecialchars($farmer['state']); ?>" required><br>

            <label>ZIP Code:</label>
            <input type="text" name="zip" value="<?php echo htmlspecialchars($farmer['zip']); ?>" required><br>

            <label>Bio:</label>
            <textarea name="bio" required><?php echo htmlspecialchars($farmer['bio']); ?></textarea><br>

            <button type="submit">Update Profile</button>
        </form>
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

</body>
</html>
