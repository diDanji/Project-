<?php
// index.php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Student finance Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        /* internal styling */
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center; 
            background-image: linear-gradient(rgba(0,0,0,0.7),rgba(0,0,0,0.7)),url(images/pic1.jpg);
            background-size: cover;
            background-position: center;
        }
        .welcome-container {
            width: 100%;
            background: transparent;
            border: 2px solid rgba(255, 255, 255, .2);
            color: #fff;
            border-radius: 10px;
            padding: 30px 40px;
            backdrop-filter: blur(20px);
        }
        /* .dropdown-menu a {
            color: #007bff !important;
        }
        .dropdown-item:hover {
            background-color: #0056b3 !important;
            color: #fff !important;
        } */
        span{
        color: #9B5278;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <h1><span style="color: #fff;">Personal </span><span> Finance </span> Management <span>App </span></h1>
        <p>(Login if already have an account or create an account)</p>
        
        
        <!-- student Login Button -->
        <a href="login.php" class="btn btn-primary mb-2"> User Login</a>
        <a href="register.php" class="btn btn-primary mb-2">Create an Account</a>

        
    </div>

    <!-- Bootstrap JavaScript files -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
