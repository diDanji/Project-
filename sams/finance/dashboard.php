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
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            color: white;
            padding: 20px 0;
            overflow-y: auto;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            margin: 5px 0;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            margin-left: 260px;
            padding: 20px;
        }
        .content h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .card {
            flex: 1;
            background: #007BFF;
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
        .recent-activities {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>User Dashboard</h2>
        <a href="dashboard.php">Home</a>
        <a href="expenses.php">Expenses</a>
        <a href="reports.php">Reports</a>
        <a href="budget.php">Budget</a>
        <a href="profile.php">Profile</a>
        <a href="signout.php" onclick="return confirmLogout()">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h1>Hello, <?php echo htmlspecialchars($user['username']); ?>✌️</h1>

        <div class="recent-activities">
            <h2>Recent Activities</h2>
            <p>No recent activities to display. Start tracking your expenses now!</p>
        </div>
        <br>
        <!-- Statistics Section -->
        <div class="stats">
            <div class="card">
                <p>Total Expenses</p>
                <h2>shs. <?php echo number_format($totalExpenses, 2); ?></h2> 
            </div>
            <!-- <div class="card">
                <p>Total Budget</p>
                <h2>shs. <?php echo number_format($totalBudget, 2); ?></h2>  
            </div>
            <div class="card">
                <p>Savings Rate</p>
                <h2><?php echo number_format($savingsRate, 2); ?>%</h2>
            </div>
        </div> -->

        <!-- Recent Activities Section -->
        
    </div>
</body>
</html>
