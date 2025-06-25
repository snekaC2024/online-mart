<?php
session_start();

// Database connection
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login-byer.php");
    exit();
}

$user_id = $_SESSION['user_id'];


// Fetch user data from database
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $pincode = $_POST['pincode'];
   
    
    $update_query = "UPDATE users SET username = ?, email = ?, phone = ?, address = ?, city = ?, state = ?, pincode = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssssssi", $name, $email, $phone, $address, $city, $state, $pincode, $user_id);
    
    if ($stmt->execute()) {
        // **Update session username**
        $_SESSION['username'] = $name; 
        $success_message = "Profile updated successfully!";
    } else {
        $error_message = "Error updating profile.";
    }
}


// **Re-fetch updated user data after profile update**
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
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

    <div class="container">
    <h2>Update Profile</h2>
    <?php if (isset($success_message)) echo "<p style='color:green;'>$success_message</p>"; ?>
    <?php if (isset($error_message)) echo "<p style='color:red;'>$error_message</p>"; ?>
    
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br>
        
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>
        
        <label>Phone:</label>
        <input type="text" name="phone" minlength="10" maxlength="10"  value="<?php echo htmlspecialchars($user['phone']); ?>" required><br>
        
        <label>Address:</label>
        <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required><br>

        <label>City:</label>
        <input type="text" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" required><br>

        <label>State:</label>
        <input type="text" name="state" value="<?php echo htmlspecialchars($user['state']); ?>" required><br>

        <label>PinCode:</label>
        <input type="text" name="pincode" value="<?php echo htmlspecialchars($user['pincode']); ?>" required><br>

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
