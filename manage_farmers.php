<?php
session_start();
if (!isset($_SESSION['admin_name'])) {
    header("Location: admin_login.php");
    exit();
}
include "db.php";

// Fetch farmers
$farmers = $conn->query("SELECT * FROM farmers");

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM farmers WHERE id=$id");
    header("Location: manage_farmers.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Farmers</title>
    <link rel="stylesheet" href="styles-admin.css">
</head>
<body>
    <h2>Farmers List</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Shop Name</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $farmers->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['username'] ?></td>
            <td><?= $row['shop_name'] ?></td>
            <td>
                <a href="?delete=<?= $row['id'] ?>">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
    <a href="admin_dashboard.php">Back to Dashboard</a>
</body>
</html>
