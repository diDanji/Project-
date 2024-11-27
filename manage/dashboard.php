<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include the database connection
require_once 'config.php';

// Fetch user data
try {
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Fetch total expenses
    $expenseQuery = "SELECT SUM(amount) AS total_expenses FROM expenses WHERE user_id = :user_id";
    $expenseStmt = $pdo->prepare($expenseQuery);
    $expenseStmt->execute(['user_id' => $_SESSION['user_id']]);
    $totalExpenses = $expenseStmt->fetchColumn();

    // Fetch total budget
    $budgetQuery = "SELECT SUM(amount) AS total_budget FROM budgets WHERE user_id = :user_id";
    $budgetStmt = $pdo->prepare($budgetQuery);
    $budgetStmt->execute(['user_id' => $_SESSION['user_id']]);
    $totalBudget = $budgetStmt->fetchColumn();

    // Calculate savings rate
    $savingsRate = ($totalBudget > 0) ? (($totalBudget - $totalExpenses) / $totalBudget) * 100 : 0;

} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- <link rel="stylesheet" href="style.css"> -->
    <style>
        body {
            font-family: Poppins, sans-serif;
            margin: 0;
            background-color: #f4f4f9;
        }
        header {
            background-image: linear-gradient(rgba(0,0,0,0.7),rgba(0,0,0,0.7)),url(images/pic1.jpg);
            background-size: cover;
            background-position: center;
            color: white;
            padding: 15px 20px;
            text-align: center;
        }
        header h1 {
            margin: 0;
            font-size: 1.8rem;
        }
        
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .card {
            flex: 1;
            padding: 20px;
            background: #9B5278;
            color: white;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card h2 {
            margin: 10px 0;
        }
        .card i {
            font-size: 2rem;
        }
        .content {
            margin-top: 20px;
        }
        .logout {
            text-align: center;
            margin-top: 20px;
        }
        .logout a {
            text-decoration: none;
            color: #fff;
            font-size: 1.1rem;
        }
        .logout a:hover {
            text-decoration: underline;
        }

        input{
        background: transparent;
        border: none;
        outline: none;
        border: 2px solid #3b5998;
        border-radius: 40px;
        font-size: 16px;
        color:green;
        padding: 10px 35px 10px 10px;
        }
 
       button {
         padding: 0.5rem;
        margin: 0.5rem;
        position: relative;
        width: 10%;
        height: 50px;
        color:green;
        border-radius: 40px;
        }

    </style>
</head>
<body>
    <ul class="nav nav-pills nav-fill">
  <li class="nav-item">
    <a class="nav-link active" aria-current="page" href="dashboard.php">Dashboard</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="expenses.php"><i class="fas fa-money-bill-wave"></i> Expenses</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="budget.php"><i class="fas fa-wallet"></i> Budget</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="reports.php"><i class="fa-duotone fa-regular fa-bars-sort"></i></i> Reports</a>
  </li>
 
  </li>
  <li class="nav-item">
    <a class="nav-link" href="profile.php"><i class="fas fa-user"></i> Profile</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="signout.php" onclick="return confirmLogout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </li>
  
</ul>
<header>
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
    </header>
      <!-- Main Content -->
      <div class="content">
       
        <div class="stats">
            <div class="card">
                <i class="fas fa-coins"></i>
                <h2>shs. <?php echo number_format($totalExpenses, 2); ?></h2>
                <p>Total Expenses</p>
            </div>
            <div class="card">
                <i class="fas fa-wallet"></i>
                <h2>shs. <?php echo number_format($totalBudget, 2); ?></h2>
                <p>Total Budget</p>
            </div>
            <div class="card">
                <i class="fas fa-chart-line"></i>
                <h2><?php echo number_format($savingsRate, 2); ?>%</h2>
                <p>Savings Rate</p>
            </div>
        </div>


        
        <div class="logout">
        <button type="button" class="btn btn-danger"><a href="signout.php" onclick="return confirmLogout()">Logout</button>
        </div>

        <!-- <li class="nav-item">
    <a class="nav-link" href="signout.php" onclick="return confirmLogout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </li> -->
    </div>
</body>
</html>
