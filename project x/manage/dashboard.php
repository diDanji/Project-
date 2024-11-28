<?php 
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include the database connection
require_once 'config.php';

// Initialize variables for totals
$totalExpenses = 0;
$totalBudget = 0;
$savingsRate = 0;

try {
    // Fetch user data
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Fetch total expenses
        $expenseQuery = "SELECT SUM(amount) AS total_expenses FROM expenses WHERE user_id = :user_id";
        $expenseStmt = $pdo->prepare($expenseQuery);
        $expenseStmt->execute(['user_id' => $_SESSION['user_id']]);
        $totalExpenses = $expenseStmt->fetchColumn() ?? 0;

        // Calculate total budget
try {
  $query = "SELECT SUM(limit_amount) AS total_budget FROM budgets WHERE user_id = :user_id";
  $stmt = $pdo->prepare($query);
  $stmt->execute(['user_id' => $_SESSION['user_id']]);
  $totalBudget = $stmt->fetch(PDO::FETCH_ASSOC)['total_budget'];
} catch (PDOException $e) {
  die("Error calculating total budget: " . $e->getMessage());
}


        // Fetch detailed budget information
        $budgetDetailsQuery = "
            SELECT category, limit_amount, amount, remaining_amount, start_date, end_date 
            FROM budgets 
            WHERE user_id = :user_id
        ";
        $budgetDetailsStmt = $pdo->prepare($budgetDetailsQuery);
        $budgetDetailsStmt->execute(['user_id' => $_SESSION['user_id']]);
        $budgetDetails = $budgetDetailsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate savings rate
        if ($totalBudget > 0) {
            $savingsRate = (($totalBudget - $totalExpenses) / $totalBudget) * 100;
        }
    } else {
        throw new Exception("User not found.");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
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
            margin: 0;
            font-family: Poppins, sans-serif;
            background-image: linear-gradient(rgba(0,0,0,0.7),rgba(0,0,0,0.7)),url(images/pic4.jfif);
            background-size: cover;
            background-position: center;
        }
        .content {
            padding: 20px;
           
        }
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .card {
            flex: 1;
            background:  #ea1538;
            color: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .card:hover {
            background: #007BFF;
           
        }
        .recent-activities {
            background:azure;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 10px;
            border: 1px solid grey;
            text-align: center;
        }
        table th {
            background:#ea1538 ;
            color: white;  
        }

        table td{
            color: #ea1538;
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
<header>
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
    </header>

    <br>

<!-- Main Content -->
<div class="content">
    <!-- Statistics Section -->
    <div class="stats">
        <div class="card" onclick="window.location.href='expenses.php'">
            <i class="fas fa-coins"></i>
            <h2>shs. <?php echo number_format($totalExpenses, 2); ?></h2>
            <p>Total Expenses</p>
        </div>
        <div class="card" onclick="window.location.href='budget.php'">
            <i class="fas fa-wallet"></i>
            <h2>shs. <?php echo number_format($totalBudget ?? 0, 2); ?></h2>

            <p>Total Budget</p>
        </div>
        <div class="card">
            <i class="fas fa-chart-line"></i>
            <h2><?php echo number_format($savingsRate, 2); ?>%</h2>
            <p>Savings Rate</p>
        </div>
    </div>

    <!-- Budget Details Section -->
    <div class="recent-activities">
        <h2   style="color: #ea1538">Budget Details</h2>
        <?php if (!empty($budgetDetails)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Limit (shs.)</th>
                        <th>Remaining (shs.)</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($budgetDetails as $budget): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($budget['category']); ?></td>
                            <td><?php echo number_format($budget['limit_amount'], 2); ?></td>
                            <td><?php echo number_format($budget['remaining_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($budget['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($budget['end_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No budget details found. Start creating budgets now!</p>
        <?php endif; ?>
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
