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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    padding: 0 50px;
    background-image: linear-gradient(rgba(0,0,0,0.7),rgba(0,0,0,0.7));
    background-repeat: no-repeat;
    background-position: center;
    background-size: cover;
  }

  .container{
    position: relative;
    max-width: 100%;
    width: 100%;
    background-color:#FFF;
    padding: 80px;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(0, 0, 0.1);

  }
  .container header{
    font-size: 1.5rem;
    color: #333;
    font-weight: 500;
    text-align: center;

  }
        .facts {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .card {
            flex: 1;
            background: green;
            color: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .card h2 {
            margin: 10px 0;
        }
    </style>
</head>
<body>


<ul class="nav justify-content-end">
  <li class="nav-item">
    <a class="nav-link active" aria-current="page" href="dashboard.php">Dashboard</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="budget.php">Budget</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="expenses.php">Expenses</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="status.html">Report</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="signout.php">Logout</a>
  </li>
</ul>


    
    <div class="container">
       
    <h1>Hi, <?php echo htmlspecialchars($user['username']); ?></h1>

  
        <div class="facts">
            <div class="card">
                <h2>shs. <?php echo number_format($totalExpenses, 2); ?></h2>
                <p>Your Expenses</p>
            </div>
           
            
        </div>
        
    </div>


</body>
</html>
