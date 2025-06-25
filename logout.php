<?php
session_start();
// Destroy the session and log the user out
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .admin-login{
            background-color:rgb(202, 32, 137);
            color: white;
        }
        .admin-login:hover{
            background-color: rgb(177, 25, 119);
        }
    </style>
</head>
<body>
    <div class="logout-page">
        <h1>You have been logged out</h1>
        <p>Choose how you want to log in again:</p>
        <div class="login-options">
            <a href="login-buyer.php" class="login-btn buyer-login">Buyer Login</a>
            <a href="login-farmer.php" class="login-btn farmer-login">Farmer Login</a>
            <a href="admin_login.php" class="login-btn admin-login">Admin Login</a>
            <a href="index.php"  class="login-btn home">Home</a>
        </div>
    </div>
</body>
</html>
