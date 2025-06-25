<?php
session_start();
if (!isset($_SESSION['admin_name'])) {
    header("Location: admin_login.php");
    exit();
}
include "db.php";

// Fetch pending products with their individual review count
$products = $conn->query("
    SELECT p.id, p.product_name, p.farmer_id, 
           COALESCE((SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND rating >= 4), 0) AS good_reviews
    FROM products p
    WHERE p.status='pending'
");

if (isset($_GET['approve'])) {
    $id = $_GET['approve'];

    // Fetch good review count for the specific product
    $review_check = $conn->query("
        SELECT COUNT(*) as good_reviews FROM reviews WHERE product_id=$id AND rating >= 4
    ")->fetch_assoc();

    if ($review_check['good_reviews'] >= 5) { // Only approve if at least 4 good reviews
        $conn->query("UPDATE products SET status='approved' WHERE id=$id");
    }

    header("Location: approve_products.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Approve Products</title>
    <link rel="stylesheet" href="styles-admin.css">
</head>
<body>
    <h2>Pending Products</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Farmer</th>
            <th>Good Reviews</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $products->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['product_name'] ?></td>
            <td><?= $row['farmer_id'] ?></td>
            <td><?= $row['good_reviews'] ?></td>
            <td>
                <?php if ($row['good_reviews'] >= 5) { ?>
                    <a href="?approve=<?= $row['id'] ?>">Approve</a>
                <?php } else { ?>
                    <span style="color:red;">Not enough good reviews</span>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
    </table>
    <a href="admin_dashboard.php">Back to Dashboard</a>
</body>
</html>
