<?php  
session_start();
include 'config.php';

// Initialize filters
$period = $_GET['period'] ?? 'all';
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

// Calculate the start date for predefined periods
if ($period !== 'custom') {
    $start = new DateTime();
    switch ($period) {
        case 'daily':
            $start->modify('-1 day');
            break;
        case 'weekly':
            $start->modify('-7 days');
            break;
        case 'monthly':
            $start->modify('-1 month');
            break;
        default: // 'all'
            $start = null;
    }
    $startDate = $start ? $start->format('Y-m-d') : null;
    $endDate = date('Y-m-d');
}

// Fetch budget data for the logged-in user
try {
    $query = "SELECT category, limit_amount 
              FROM budgets
              WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $budgetData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $budget = [];
    foreach ($budgetData as $row) {
        $budget[$row['category']] = $row['limit_amount'];
    }

} catch (PDOException $e) {
    $error = "Error fetching budget data: " . $e->getMessage();
    $budget = [];
}

// Fetch expense data based on the selected period
try {
    $query = "SELECT category, SUM(amount) AS total
              FROM expenses
              WHERE user_id = :user_id";
    if ($startDate) {
        $query .= " AND date >= :start_date";
    }
    if ($endDate) {
        $query .= " AND date <= :end_date";
    }
    $query .= " GROUP BY category ORDER BY category ASC";

    $stmt = $pdo->prepare($query);

    $params = ['user_id' => $_SESSION['user_id']];
    if ($startDate) $params['start_date'] = $startDate;
    if ($endDate) $params['end_date'] = $endDate;

    $stmt->execute($params);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $actualExpenses = [];
    foreach ($expenses as $expense) {
        $actualExpenses[$expense['category']] = $expense['total'];
    }

} catch (PDOException $e) {
    $error = "Error fetching expense data: " . $e->getMessage();
    $actualExpenses = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">


    <style>
        /* Print styles */
        @media print {
            body * {
                visibility: hidden;
            }
            #report-content, #report-content * {
                visibility: visible;
            }
            #report-content {
                position: absolute;
                top: 0;
                left: 0;
            }
            .navbar, .footer, .dropdown-menu, .search-form, .nav-link.disabled {
                display: none !important;
            }
        }

        body {
            font-family: Poppins, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            font-family: Poppins, sans-serif;
            background-image: linear-gradient(rgba(0,0,0,0.7),rgba(0,0,0,0.7)),url(images/pic6.jfif);
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

<div class="container mt-5" id="report-content">
    <h1 class="mb-4">Expense Report</h1>

    <!-- Filter Form -->
    <form method="get" class="mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="period" class="form-label">Select Period:</label>
                <select name="period" id="period" class="form-select">
                    <option value="all" <?= $period == 'all' ? 'selected' : '' ?>>All</option>
                    <option value="daily" <?= $period == 'daily' ? 'selected' : '' ?>>Daily</option>
                    <option value="weekly" <?= $period == 'weekly' ? 'selected' : '' ?>>Weekly</option>
                    <option value="monthly" <?= $period == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                    <option value="custom" <?= $period == 'custom' ? 'selected' : '' ?>>Custom</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date:</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date:</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="col-md-3 align-self-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </div>
    </form>

    <!-- Expense Table -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Category</th>
                <th>Budget (Shs.)</th>
                <th>Actual Spent (Shs.)</th>
                <th>Difference (Shs.)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($budget as $category => $budgetAmount): ?>
                <tr>
                    <td><?= htmlspecialchars($category) ?></td>
                    <td><?= number_format($budgetAmount, 2) ?></td>
                    <td><?= number_format($actualExpenses[$category] ?? 0, 2) ?></td>
                    <td><?= number_format(($budgetAmount - ($actualExpenses[$category] ?? 0)), 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Expense Comparison Chart -->
    <h3 style="color:#fff">Expense vs. Budget</h3>
    <canvas id="expenseChart"></canvas>


    <!-- Print Button -->
    <button class="btn btn-success mt-4" onclick="window.print();">Print Report</button>
    <button class="btn btn-success mt-4" id="exportCSV">Export to CSV</button>
</div>

<script>
    const ctx = document.getElementById('expenseChart').getContext('2d');
    const chartData = {
        labels: <?= json_encode(array_keys($budget)) ?>,
        datasets: [{
            label: 'Budget',
            data: <?= json_encode(array_values($budget)) ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        },
        {
            label: 'Actual Expenses',
            data: <?= json_encode(array_values($actualExpenses)) ?>,
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    };

    const expenseChart = new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    document.getElementById('exportCSV').addEventListener('click', function () {
        const table = document.querySelector("table");
        let csvContent = "";

        // Get table headers
        const headers = Array.from(table.querySelectorAll("thead th")).map(th => th.textContent).join(",");
        csvContent += headers + "\n";

        // Get table rows
        table.querySelectorAll("tbody tr").forEach(row => {
            const rowData = Array.from(row.querySelectorAll("td")).map(td => td.textContent.trim()).join(",");
            csvContent += rowData + "\n";
        });

        // Download CSV
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.setAttribute('href', url);
        a.setAttribute('download', 'Expense_Report.csv');
        a.click();
    });
</script>

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
