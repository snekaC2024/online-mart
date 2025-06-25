<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "farm_farmers";

// $host = "sql110.infinityfree.com";
// $user = "if0_38618113";
// $pass = "Osy8TreGzTAJ";
// $dbname = "if0_38618113_farm_farmer";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
