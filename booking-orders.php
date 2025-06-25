<?php
include 'db.php';
session_start();

// Ensure the farmer is logged in
if (!isset($_SESSION['username'])) {
    die("You need to login first.");
}

$username = $_SESSION['username'];

// Fetch the farmer_id using the username
$query = "SELECT id FROM farmers WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$farmer_id = $row['id'] ?? null;

if (!$farmer_id) {
    die("Farmer ID not found.");
}

// Fetch order counts for dashboard
$count_query = "SELECT 
    SUM(CASE WHEN o.status = 'pending' THEN 1 ELSE 0 END) AS pending_orders,
    SUM(CASE WHEN o.status = 'delivered' THEN 1 ELSE 0 END) AS delivered_orders,
    SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_orders
    FROM orders o 
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.farmer_id = ?";
$stmt = $conn->prepare($count_query);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
$counts = $result->fetch_assoc();

$pending_orders = $counts['pending_orders'] ?? 0;
$delivered_orders = $counts['delivered_orders'] ?? 0;
$cancelled_orders = $counts['cancelled_orders'] ?? 0;

// Fetch orders along with user address and calculate the price per item
$sql = "SELECT o.id, p.product_name, oi.quantity, 
        (oi.quantity * p.price) AS item_total_price, o.order_date, o.status, 
        u.address, u.city, u.state 
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        JOIN users u ON o.user_id = u.id  
        WHERE p.farmer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="booking-orders.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Booking Orders</title>
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
<h2>📜 My Orders</h2>

<div class="dashboard">
    <div class="pending">Pending Orders: <span id="pending-count"><?php echo $pending_orders; ?></span></div>
    <div class="delivered">Delivered Orders: <span id="delivered-count"><?php echo $delivered_orders; ?></span></div>
    <div class="cancelled">Cancelled Orders: <span id="cancelled-count"><?php echo $cancelled_orders; ?></span></div>
</div>

<div class="filter-container">
    <label for="status-filter">Filter by Status:</label>
    <select id="status-filter">
        <option value="all">All</option>
        <option value="pending">Pending</option>
        <option value="delivered">Delivered</option>
        <option value="cancelled">Cancelled</option>
    </select>
</div>

<table border="1">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Price (₹)</th>
            <th>Order Date</th>
            <th>Status</th>
            <th>Address</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr data-status="<?php echo $row['status']; ?>">
                <td>#<?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td>₹<?php echo $row['item_total_price']; ?></td>
                <td><?php echo $row['order_date']; ?></td>
                <td>
                    <span class="order-status status-<?php echo strtolower($row['status']); ?>" data-id="<?php echo $row['id']; ?>">
                        <?php echo ucfirst($row['status']); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($row['address']) . ", " . htmlspecialchars($row['city']) . ", " . htmlspecialchars($row['state']); ?></td>
                <td>
                    <?php if ($row['status'] == 'pending') : ?>
                        <button class="mark-delivered" data-id="<?php echo $row['id']; ?>">Mark as Delivered</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
document.getElementById("status-filter").addEventListener("change", function() {
    let selectedStatus = this.value;
    document.querySelectorAll("tbody tr").forEach(row => {
        let rowStatus = row.getAttribute("data-status");
        row.style.display = (selectedStatus === "all" || rowStatus === selectedStatus) ? "" : "none";
    });
});
</script>

</body>
</html>
