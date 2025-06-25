<?php
session_start();
if (!isset($_SESSION['admin_name'])) {
    header("Location: admin_login.php");
    exit();
}
include "db.php";

// Handle removal request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_id'])) {
    $remove_id = intval($_POST['remove_id']);
    $conn->query("UPDATE products SET status='pending' WHERE id=$remove_id");
    header("Location: approved_products.php");
    exit();
}

// Fetch approved products
$approved_products = $conn->query("SELECT * FROM products WHERE status='approved'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Approved Products</title>
    <link rel="stylesheet" href="styles-admin.css">
</head>
<body>
    <h2>Approved Products</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Farmer</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $approved_products->fetch_assoc()) { ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['product_name']) ?></td>
            <td><?= htmlspecialchars($row['farmer_id']) ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="remove_id" value="<?= htmlspecialchars($row['id']) ?>">
                    <button type="submit">Remove from Approved</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
    <a href="admin_dashboard.php">Back to Dashboard</a>
</body>
</html>
