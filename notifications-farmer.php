<?php
session_start();
include 'db.php'; // Ensure this file connects to your database

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$farmer_username = $_SESSION['username'];

// Fetch the farmer's ID from the database
$sql_farmer = "SELECT id FROM farmers WHERE username = ?";
$stmt_farmer = $conn->prepare($sql_farmer);
$stmt_farmer->bind_param("s", $farmer_username);
$stmt_farmer->execute();
$result_farmer = $stmt_farmer->get_result();
$row_farmer = $result_farmer->fetch_assoc();
$farmer_id = $row_farmer['id'] ?? null;
$stmt_farmer->close();

if (!$farmer_id) {
    die("Farmer not found.");
}

// Fetch low-stock products for this farmer
$sql = "SELECT product_name, count FROM products WHERE farmer_id = ? AND count <= 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = "Low stock alert: " . $row['product_name'] . " has only " . $row['count'] . " left!";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
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
    <h2>Stock Notifications</h2>
    <ul>
        <?php
        if (empty($notifications)) {
            echo "<li>No low stock alerts.</li>";
        } else {
            foreach ($notifications as $note) {
                echo "<li>$note</li>";
            }
        }
        ?>
    </ul>
</body>
</html>
