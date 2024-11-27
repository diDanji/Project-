<?php
session_start();
include 'config.php';
// Fetch summarized data based on a selected period
$period = $_GET['period'] ?? 'daily'; // Default to 'daily'

switch ($period) {
    case 'daily':
        $groupBy = "DATE(date)";
        $periodFormat = "DATE_FORMAT(date, '%Y-%m-%d')";
        break;
    case 'weekly':
        $groupBy = "YEARWEEK(date)";
        $periodFormat = "CONCAT(YEAR(date), '-W', WEEK(date))";
        break;
    case 'monthly':
        $groupBy = "DATE_FORMAT(date, '%Y-%m')";
        $periodFormat = "DATE_FORMAT(date, '%Y-%m')";
        break;
    default:
        $groupBy = "DATE(date)";
        $periodFormat = "DATE_FORMAT(date, '%Y-%m-%d')";
}

try {
    // Query to fetch summarized expense data
    $query = "SELECT $periodFormat AS period, category, SUM(amount) AS total
              FROM expenses
              WHERE user_id = :user_id
              GROUP BY $groupBy, category
              ORDER BY $groupBy DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching report data: " . $e->getMessage());
}

try {
    // Query to fetch budget data
    $budgetQuery = "SELECT b.category, b.limit_amount, 
                           IFNULL(b.limit_amount - SUM(e.amount), b.limit_amount) AS remaining_budget
                    FROM budgets b
                    LEFT JOIN expenses e 
                    ON b.user_id = e.user_id AND b.category = e.category
                    WHERE b.user_id = :user_id
                    GROUP BY b.category, b.limit_amount";
    $stmt = $pdo->prepare($budgetQuery);
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error calculating remaining budget: " . $e->getMessage());
}

// Export to CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=report.csv');
    
    $output = fopen("php://output", "w");
    fputcsv($output, ['Period', 'Category', 'Total Spent']);
    foreach ($expenses as $expense) {
        fputcsv($output, [$expense['period'], $expense['category'], $expense['total']]);
    }
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
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

        h1{
            text-align:center;
            font-size: 20px;
        }

        option{
            background: transparent;
            border: none;
            outline: none;
            border: 2px solid #3b5998;
            border-radius: 40px;
            font-size: 16px;
            color:green;
            padding: 10px 35px 10px 10px; 
        }
    </style>
</head>
<body>

        <nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Access</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="expenses.php">Expenses</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            more
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="budget.php">Budget</a></li>
            <li><a class="dropdown-item" href="reports.php">Reports</a></li>
            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#">logout</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link disabled" aria-disabled="true" href="#"></a>
        </li>
      </ul>
      <form class="d-flex" role="search">
        <input class="form-control me-2" type="search" placeholder="filter" aria-label="Search">
        <button class="btn btn-outline-success" type="submit">Filter</button>
      </form>
    </div>
  </div>
</nav>

<h1 class="display-5">Expenditure</h1>



    <div class="container text-center">
  <div class="row align-items-center">
        
        <div class="col">
        <p class="h6"><label for="period">Select Period:</label></p>
        </div>

    <div class="col">
    <!-- Period Selection -->
    <form method="get" action="">
        <select name="period" id="period" aria-label="Default select example">
        <option value="daily" <?= $period === 'daily' ? 'selected' : '' ?>>Daily</option>
        <option value="weekly" <?= $period === 'weekly' ? 'selected' : '' ?>>Weekly</option>
        <option value="monthly" <?= $period === 'monthly' ? 'selected' : '' ?>>Monthly</option>
        </select>
        <button type="submit"  name="set_budget" class="btn btn-success">Display</button>
        <!-- <div class="input-group">
  <input type="file" class="form-control" id="inputGroupFile04" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
  <button class="btn btn-outline-secondary" type="button" id="inputGroupFileAddon04">Button</button> -->
</div>

<div class="col">
<a href="?period=<?= htmlspecialchars($period) ?>&export=csv">Export to CSV</a>
    </div>


    </form>
    </div>
  </div>
</div>

        <table class="table table-striped">
        <thead>
            <tr>
                <th>Period</th>
                <th>Category</th>
                <th>Total Spent</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($expenses): ?>
                <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?= htmlspecialchars($expense['period']) ?></td>
                        <td><?= htmlspecialchars($expense['category']) ?></td>
                        <td>shs.<?= htmlspecialchars(number_format($expense['total'], 2)) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3">No expenses found.</td></tr>
            <?php endif; ?>
        </tbody>

        </table>
         


    <!-- Chart -->
    <canvas id="expenseChart"></canvas>
    <script>
        const ctx = document.getElementById('expenseChart').getContext('2d');
        const chartData = {
            labels: <?= json_encode(array_column($expenses, 'category')) ?>,
            datasets: [{
                label: 'Expenses',
                data: <?= json_encode(array_column($expenses, 'total')) ?>,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4CAF50', '#F44336']
            }]
        };

        new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

<
</body>
</html>










<section id="budget-section">
        <h2>Budgeting</h2>
        <div>
            <input type="number" id="budget-input" placeholder="Enter monthly budget">
            <button onclick="setBudget()">Set Budget</button>
        </div>
        <div id="budget-progress">
            <p>Budget Status:</p>
            <canvas id="budgetChart"></canvas>
        </div>
    </section>

    <section id="expense-section">
        <h2>Expense Tracking</h2>
        <input type="text" id="expense-category" placeholder="Category">
        <input type="number" id="expense-amount" placeholder="Amount">
        <button onclick="addExpense()">Add Expense</button>
        <div>
            <canvas id="expenseChart"></canvas>
        </div>
    </section>

    <section id="savings-section">
        <h2>Savings Goals</h2>
        <input type="text" id="goal-name" placeholder="Goal">
        <input type="number" id="goal-amount" placeholder="Amount">
        <button onclick="setGoal()">Add Goal</button>
        <canvas id="goalChart"></canvas>
    </section>

    <!-- <section id="investment-section">
        <h2>Investment Tracking</h2>
        <canvas id="investmentChart"></canvas>
    </section> -->

    <!-- <section id="insights">
        <h2>Financial Insights</h2>
    </section> -->


    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js" defer></script>
    <script type="module" src="chart.js" defer></script>
    <script type="module" src="app.js" defer></script>