<?php
session_start();
require 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You need to login first.");
}

$user_id = $_SESSION['user_id'];

echo "<h3>Your Notifications</h3>";

// Fetch notifications for the logged-in user
$query = $conn->prepare("SELECT message, image, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Ensure image path is correct
        $imagePath = !empty($row['image']) ? "uploads/" . htmlspecialchars($row['image']) : "default-image.jpg";

        echo "<div class='notification-item'>";
        echo "<img src='{$imagePath}' width='80' height='80' alt='Product Image' style='border-radius: 5px;'>";
        echo "<p><strong>" . htmlspecialchars($row['message']) . "</strong></p>";
        echo "<p><small>Received on: " . htmlspecialchars($row['created_at']) . "</small></p>";
        echo "<hr>";
        echo "</div>";
    }

    // ✅ Mark all notifications as read
    $updateQuery = $conn->prepare("UPDATE notifications SET status = 'read' WHERE user_id = ?");
    $updateQuery->bind_param("i", $user_id);
    $updateQuery->execute();
} else {
    echo "<p>No new notifications.</p>";
}

$conn->close();
?>
