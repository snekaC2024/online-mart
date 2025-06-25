<?php
session_start();
if (!isset($_SESSION['admin_name'])) {
    header("Location: admin_login.php");
    exit();
}
include "db.php";

// Fetch orders with customer name
$query = "SELECT orders.id, users.username AS customer_name, orders.status 
          FROM orders 
          JOIN users ON orders.user_id = users.id";
$orders = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Orders</title>
    <link rel="stylesheet" href="styles-admin.css">
</head>
<body>
    <h2>Order List</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Status</th>
        </tr>
        <?php while ($row = $orders->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['customer_name'] ?></td>
            <td><?= $row['status'] ?></td>
        </tr>
        <?php } ?>
    </table>
    <a href="admin_dashboard.php">Back to Dashboard</a>
</body>
</html>
