<?php
// Include the database connection
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture and sanitize user inputs
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        try {
            // Fetch the user from the database
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid email or password.";
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- <link rel="stylesheet" href="css/bootstrap.min.css"> -->


    <title>Login</title>
   <!-- css registration -->
   <style>
        .container-login{
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
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
            font-family: poppins, sans-serif;

        } 

        .wrapper .register-link{
            font-size: 14.5px;
            text-align: center;
            margin: 20px 0 15px;
        }

        .register-link p a{
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link p a:hover{
            text-decoration: underline;

        }

        .error {
            color: red;
            font-size: 16px;
            text-align: center;
            font-family: poppins, sans-serif;
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
            color:  #ea1538;

        }

        p{
            text-align: center;
            font-family: poppins, sans-serif;
        }
    </style>
   
</head>
<body>

<div class="container-login">
    <div class="wrapper">
    <h1 style="color: #ea1538;">User login</h1>
    <p>(Please provide your login Details)</p>


<?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
<form method="POST" action="login.php">
    <div class="input-box">
    <input type="email" name="email" placeholder="Email" required>
    <i class='bx bxs-envelope' ></i>
    </div>
    <div class="input-box">
    <input type="password" name="password" placeholder="Password" required>
    <i class='bx bxs-lock-alt' ></i>
    </div>
    <button type="submit" class="btn1">Login</button>
</form>
<div class="register-link">
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
    </div>
</div>   

</body>
</html>
 