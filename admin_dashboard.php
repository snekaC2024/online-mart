<?php
session_start();
if (!isset($_SESSION['admin_name'])) {
    header("Location: admin_login.php");
    exit();
}
include "db.php";

// Fetch counts
$farmers = $conn->query("SELECT COUNT(*) AS count FROM farmers")->fetch_assoc();
$users = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc();
$pending_products = $conn->query("SELECT COUNT(*) AS count FROM products WHERE status='pending'")->fetch_assoc();
$orders = $conn->query("SELECT COUNT(*) AS count FROM orders")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles-admin.css">
</head>
<body>
    <h2>Welcome, Admin</h2>
    <a href="manage_farmers.php">Manage Farmers (<?= $farmers['count'] ?>)</a><br>
    <a href="approve_products.php">Pending Products (<?= $pending_products['count'] ?>)</a><br>
    <a href="approved_products.php">Approved Products</a><br>
    <a href="manage_orders.php">Manage Orders (<?= $orders['count'] ?>)</a><br>
    <a href="logout.php">Logout</a>
</body>
</html>
