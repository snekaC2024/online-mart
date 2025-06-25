<?php
session_start();

// Dummy login verification (replace with your own authentication logic)
$login_successful = false;
$error_message = "";
$success_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

// Database connection
include 'db.php';

    // Query to check if user exists and password matches
    $stmt = $conn->prepare("SELECT id, password FROM farmers WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();
    
        // Verify password
        if (password_verify($password, $hashed_password)) {
            // Successful login
            $_SESSION['user_id'] = $user_id; // Store user ID
            $_SESSION['username'] = $username; // Store username
            $login_successful = true;
            $success_message = "Login Successful! Redirecting...";
        } else {
            // Invalid password
            $error_message = "Invalid username or password.";
        }
    } else {
        // No user found
        $error_message = "Invalid username or password.";
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #e6f7ff;
        }
        .container {
            width: 90%;
            max-width: 400px;
            padding: 20px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .container h1 {
            font-size: 24px;
            color: #007acc;
        }
        form {
            padding: 20px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            background: #007acc;
            border: none;
            color: #fff;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #005f99;
        }
        .error-message, .success-message {
            margin-top: 10px;
            font-size: 14px;
        }
        .error-message {
            color: red;
        }
        .success-message {
            color: green;
            font-size: 16px;
            padding: 15px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .success-message:hover {
            background-color: #c3e6cb;
            transform: scale(1.02);
        }
        .footer-text {
            margin-top: 15px;
            font-size: 14px;
        }
        .footer-text a {
            color: #007acc;
            text-decoration: none;
        }
        .footer-text a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }
            .container h1 {
                font-size: 20px;
            }
            input[type="text"], input[type="password"] {
                font-size: 14px;
                padding: 10px;
            }
            button {
                font-size: 14px;
                padding: 10px;
            }
            .footer-text {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Farmer Login</h1>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if ($login_successful): ?>
            <div class="success-message" id="success-message"><?php echo $success_message; ?></div>
            <script>
                setTimeout(function() {
                    window.location.href = "farmers.php"; // Redirect after 4 seconds
                }, 4000);
            </script>
        <?php else: ?>
            <form method="POST" action="">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <div class="footer-text">
                Don't have an account? <a href="register-farmer.php">Sign Up</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
