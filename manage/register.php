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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        .container-login{
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: url('images/pic1.jpg');
            background-image: linear-gradient(rgba(0,0,0,0.7),rgba(0,0,0,0.7)),url(images/pic1.jpg);
            background-size: cover;
            background-position: center;
        }

        .wrapper{
            width: 420px;
            background: transparent;
            border: 2px solid rgba(255, 255, 255, .2);
            color: #fff;
            border-radius: 10px;
            padding: 30px 40px;
            backdrop-filter: blur(20px);
        }

        .wrapper h1{
            font-size: 20px;
            text-align: center;
            font-family: poppins, sans-serif;
        }

        .wrapper .input-box{
            position: relative;
            width: 100%;
            height: 50px;
            margin: 30px 0;
        }

        .input-box input{
            width: 100%;
            height:100%;
            background: transparent;
            border: none;
            outline: none;
            border: 2px solid rgba(255, 255, 255, .2);
            border-radius: 40px;
            font-size: 16px;
            color: #fff;
            padding: 20px 45px 20px 20px;
        }

        .input-box input::placeholder{
            color: #fff;
        }

        .input-box i{
            position: absolute;
            right:20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
        }
        
        .wrapper .remember-forgot{
            display: flex;
            justify-content: space-between;
            font-size: 14.5px;
            margin: -15px 0 15px;
        }

        .remember-forgot label input{
            accent-color: #fff;
            margin-right: 3px;
        }

        .remember-forgot a{
            color: #fff;
            text-decoration: none;
        }

        .remember-forgot a:hover{
            text-decoration: underline;
        }

        .wrapper .btn1{
            width: 100%;
            height:45px;
            background: #0d6efd;
            border:none;
            outline: none;
            border-radius: 40px;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
            cursor: pointer;
            font-size: 16px;
            color: #fff;
            font-weight: 600;
        } 

        .wrapper .register-link{
            font-size: 14.5px;
            text-align: center;
            margin: 20px 0 15px;
            font-family: poppins, sans-serif;

        }

        .register-link p a{
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
            font
        }

        .register-link p a:hover{
            text-decoration: underline;

        }
        .error .success {
            text-align: center;
            margin-bottom: 10px;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
    
</head>
<body>
    <div class="container-login">
        <div class="wrapper">
        <h1>Register</h1>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
    <form method="POST" action="register.php">
        <div class="input-box">
        <input type="text" name="username" placeholder="Username" required>
        </div>
        <div class="input-box">
        <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="input-box">
        <input type="password" name="password" placeholder="Password" required>   
        </div>
        <div class="input-box">
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        </div>

        <button type="submit" class="btn1">Register</button>
    </form>
    <div class="register-link">
        <p>Already have an account? <a href="login.php">Log in</a></p>
    </div>
        </div>
    </div>
</body>
</html>
