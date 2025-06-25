<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email address!";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        // Database connection
       include 'db.php';

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = "An account with this username or email already exists. Please <a href='login-buyer.php'>login here</a>.";
        } else {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insert user into the database
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                $success_message = "Registration successful! You can now <a href='login-buyer.php'>login</a>.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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
        input[type="text"], input[type="email"], input[type="password"] {
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

        .success-message {
            margin-top: 10px;
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

    </style>
</head>
<body>
    <div class="container">
        <h1>Register as a customer </h1>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php else: ?>
            <form method="POST" action="">
                <input type="text" name="username" placeholder="User name" required>
                <input type="email" name="email" placeholder="Email address" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit">Sign Up</button>
            </form>
            <div class="footer-text">
                Already have an account? <a href="login-buyer.php">Login here</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
