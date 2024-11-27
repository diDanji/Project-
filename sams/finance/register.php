<?php
// Include the database connection
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture and sanitize user inputs
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Error handling
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            // Check if the user already exists
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['email' => $email]);
            if ($stmt->rowCount() > 0) {
                $error = "Email is already registered.";
            } else {
                // Insert the new user into the database
                $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'password' => $hashedPassword,
                ]);

                $success = "Registration successful! Redirecting to login...";
                
                // Redirect to login page
                header("Location: login.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        form {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input {
            display: block;
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
        }
        button {
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .error, .success {
            text-align: center;
            margin-bottom: 10px;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
        .login-link {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h2 style="text-align: center;">Register</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
    <form method="POST" action="register.php">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit">Register</button>
    </form>
    <div class="login-link">
        <p>Already have an account? <a href="login.php">Log in</a></p>
    </div>
</body>
</html>
