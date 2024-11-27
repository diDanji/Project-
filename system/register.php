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
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap');

*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Oswald", sans-serif;
    font-optical-sizing: auto;
    font-weight: weight;
    font-style: normal;
  }

  body{
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 20px;
    background-image: linear-gradient(rgba(0,0,0,0.7),rgba(0,0,0,0.7));
    background-repeat: no-repeat;
    background-position: center;
    background-size: cover;
  }

  .container{
    position: relative;
    max-width: 700px;
    width: 100%;
    background-color:#FFF;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(0, 0, 0.1);

  }
  .container header{
    font-size: 1.5rem;
    color: #333;
    font-weight: 500;
    text-align: center;

  }
  .container .form{
    margin-top: 30px;

  }
  .form .input-box{
    width: 100%;
    margin-top: 20px;
  }

  .input-box label{
    color: #333;
  }
  .form :where(.input-box input, .select-box){
    position: relative;
    height: 50px;
    width: 100%;
    outline: none;
    font-size: 1rem;
    margin-top: 8px;
    color: #707070;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 0 15px;
}

.form .column {
    display: flex;
    column-gap: 15px;
} 

.form .gender-box {
  margin-top: 20px; 
}

.gender-box h3{
  color: #333;
  font-size: 1rem;
  font-weight: 400;
  margin-bottom: 8px;


}
.form :where(.gender-option, .gender) {
    display: flex;
    align-items: center;
    column-gap: 50px;
    flex-wrap: wrap;
}

.form .gender {
    column-gap: 5px;
    
}

.gender input{
  accent-color: rgb(88, 56, 250);
}

.form :where(.gender input, .gender label){
    cursor: pointer;
}

.select-box select{
  height: 100%;
  width: 100%;
  outline: none;
  border: none;
  color: #707070;
  font-size: 1rem;
  
}

.form button{
  height: 55px;
  width: 100%;
  background: rgb(130, 106, 251);
  color: #fff;
  font-size: 1rem;
  border: none;
  margin-top: 30px;
  cursor: pointer;
  font-weight: 400;
  border-radius: 6px;
  transition: all 0.2s ease;

}

.form button:hover{
  background: rgb(88, 56, 250);
}


/* responsive */

@media screen and (max-width: 500px) {
    .form .column{
        flex-wrap: wrap;
    }

    .form :where(.gender-option, .gender){
      row-gap: 15px;
    }
    
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
   

    <section class="container"> 
   
   <header>Student Registration Form</header>
   <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>

   <form action="register.php" class="form" method="POST">
       <div class="input-box">
           <label >Username</label>
           <input type="text" name="username" placeholder="Username" required>
       
       </div>

       <div class="input-box">
           <label >Email</label>
           <input type="email" name="email" placeholder="Email" required>

       </div>
      
        <div class="column">
        <div class="input-box">
           <label >Password</label>
           <input type="password" name="password" placeholder="Password" required>

       </div>
       <div class="input-box">
           <label>Confirm password</label>
           <input type="password" name="confirm_password" placeholder="Confirm Password" required>

       </div>
</div>
       <button type="submit">Register</button>
       <div class="login-link">
        <p>Already have an account? <a href="login.php">Log in</a></p>
    </div>

</form>
</section>

</body>
</html>
