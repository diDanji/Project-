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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">Dashboard</a>
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
            <li><a class="dropdown-item" href="signout.php">logout</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link disabled" aria-disabled="true" href="#"></a>
        </li>
      </ul>
      <form class="d-flex search-form" role="search">
        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>
    </div>
  </div>
</nav>

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
    <h3>Expense vs. Budget</h3>
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

</body>
</html>
