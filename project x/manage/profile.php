<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch user data
$user_id = $_SESSION['user_id'];
try {
    $query = "SELECT username, email, password FROM users WHERE id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching user data: " . $e->getMessage();
}

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate old password and new password match
    if (!password_verify($old_password, $user['password'])) {
        $error = "Old password is incorrect.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = "New password and confirmation do not match.";
    } else {
        try {
            $query = "UPDATE users SET username = :username, email = :email";
            $params = [
                'username' => $new_username,
                'email' => $new_email,
                'user_id' => $user_id
            ];

            // Only update password if provided
            if (!empty($new_password)) {
                $query .= ", password = :password";
                $params['password'] = password_hash($new_password, PASSWORD_DEFAULT);
            }
            $query .= " WHERE id = :user_id";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            // Update session data
            $_SESSION['username'] = $new_username;
            $_SESSION['email'] = $new_email;

            $success = "Profile updated successfully.";
        } catch (PDOException $e) {
            $error = "Error updating profile: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        *{
            font-family: poppins, sans-serif;
        }

        body {
            font-family: Poppins, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            font-family: Poppins, sans-serif;
            background-image: linear-gradient(rgba(0,0,0,0.7),rgba(0,0,0,0.7)),url(images/pic7.jfif);
            background-size: cover;
            background-position: center;
        }
                              
/* footer */
.footer-container{
    max-width: 1170px;
    margin: auto;
}

.footer{
    background-color: #24262b;
    padding: 70px 0;
}

ul{
    list-style: none;
}

.row-footer{
    display: flex;
    flex-wrap: wrap;
}

.footer-col{
    width: 25%;
    padding: 0 15px;
}

.footer-col h4{
    font-size: 18px;
    color: #fff;
    text-transform: capitalize;
    margin-bottom: 35px;
    font-weight: 500;
    position: relative;
}

.footer-col h4::before{
    content: '';
    position: absolute;
    left: 0;
    bottom: -10px;
    background-color: #ea1538;
    height: 2px;
    box-sizing: border-box;
    width: 50px;
}

.footer-col ul li:not(:last-child){
    margin-bottom: 10px;

}

.footer-col ul li a{
    font-size: 16px;
    text-transform: capitalize;
    color: #ffffff;
    text-decoration: none;
    font-weight: 300;
    color: #bbbbbb;
    display: block;
    transition: all 0.3s ease;
}

.footer-col ul li a:hover{
    color: #ffffff;
    padding-left: 8px;
}

.footer-col .social-links a{
    display: inline-block;
    height: 40px;
    width: 40px;
    background-color: rgba(255, 255, 255, 0.2);
    padding-top: 15px;
    padding-bottom: -5px;
    margin: 0 10px 10px 0;
    text-align: center;
    line-height: 40px;
    border-radius: 50%;
    color: #ffffff;
    transition: all 0.5s ease;
}

.footer-col .social-links a:hover{
    color: #24262b;
    background-color: #ffffff;
}

/* navbar */
nav{
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 40px;
    padding-left: 10%;
    padding-right: 10%;
}

span{
    color: #ea1538;
}

nav ul li{
    list-style-type: none;
    display: inline-block;
    padding: 10px 10px;
}

nav ul li a{
    color: white;
    text-decoration: none;
    font-weight: bold;
}

nav ul li a:hover{
    color:#ea1538 ;
    transition: .3s;
}

h1 {
    color:#fff;
    font-size: 25px;
    text-align:center;
}

h2{
   
}

.container-login{
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .wrapper{
            width: 420px;
            background: transparent;
            border: 2px solid rgba(255, 255, 255, .2);
            color: #fff;
            border-radius: 10px;
            padding: 30px 40px;
            backdrop-filter: blur(30px);
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
            font-weight: 5px;
            color: #fff;
            padding: 20px 45px 20px 20px;
        }

        .input-box input::placeholder{
            color: #fff;
            font-size:12px;
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

        }

        p{
            text-align: center;
            font-family: poppins, sans-serif;
        }

    </style>
</head>
<body>
<nav>
            <h2><span style="color: azure;"> Student Finance </span><span>Management System</span> </h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="expenses.php">Expense</a></li>
                <li><a href="budget.php">Budget</a></li>
                <li><a href="reports.php">Report</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="signout.php" onclick="return confirmLogout()">Logout</a></li>
            </ul>
        </nav>
        <br>

        <div class="container-login">
    <div class="wrapper">
    
    <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
<form method="POST" action="login.php">
<h3>User credentials</h3>
    <div class="input-box">
    <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
    </div>
    <div class="input-box">
    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
    </div>
    <hr>
            <h3>Change Password</h3>
    <div class="input-box">
    <input type="password" class="form-control" id="old_password" name="old_password" placeholder="old password">
    </div>
    <div class="input-box">
    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="new password">
    </div>
    <div class="input-box">
    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="confirm password">
    </div>

    <button type="submit" class="btn1">Update profile</button>
</form>
    </div>
</div>   
     <!-- footer -->
     <footer class="footer">
        <div class="footer-container">
            <div class="row-footer">
                <div class="footer-col">
                    <h4>Pages</h4>
                    <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="expenses.php">Expense</a></li>
                <li><a href="budget.php">Budget</a></li>
                <li><a href="reports.php">Report</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="signout.php" onclick="return confirmLogout()">Logout</a></li>
            </ul>
        </nav>
                </div>

                <div class="footer-col">
                    <h4>services</h4>
                    <ul>
                        <ul>
                            <li><a href="">FAQ</a></li>
                            <li><a href="">Consult</a></li>
                            <li><a href="">support</a></li>
                        </ul>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>follow us</h4>
                   <div class="social-links">
                    <a href="#"><i class="fab fa-github"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                    &copy;2024 dilli tonny
                    <h2 style="color: azure; font-size: 10px;"><span> &copy;2024 Student Finance Management System</span><span></span> </h2>
                </div>

            </div>
        </div>
    </footer>
</body>
</html>
