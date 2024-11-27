<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include the database connection
require_once 'config.php';

// Handle setting a new budget
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_budget'])) {
    $category = $_POST['category'];
    $limit = $_POST['limit'];

    try {
        // Check if a budget already exists for this category
        $query = "SELECT * FROM budgets WHERE user_id = :user_id AND category = :category";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['user_id' => $_SESSION['user_id'], 'category' => $category]);
        $existingBudget = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingBudget) {
            // Update existing budget
            $query = "UPDATE budgets SET limit_amount = :limit, remaining_amount = :limit WHERE user_id = :user_id AND category = :category";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['user_id' => $_SESSION['user_id'], 'category' => $category, 'limit' => $limit]);
        } else {
            // Insert new budget
            $query = "INSERT INTO budgets (user_id, category, limit_amount, remaining_amount) VALUES (:user_id, :category, :limit, :limit)";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['user_id' => $_SESSION['user_id'], 'category' => $category, 'limit' => $limit]);
        }
        header("Location: budget.php");
        exit;
    } catch (PDOException $e) {
        die("Error setting budget: " . $e->getMessage());
    }
}

// Fetch budgets for the logged-in user
try {
    $query = "SELECT * FROM budgets WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching budgets: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Management</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
   <link rel="stylesheet" href="style.css">
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

    <h2>Up-to-Budgets</h2>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Limit</th>
                    <th>Remaining</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($budgets)): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">No pending budgets</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($budgets as $budget): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($budget['category']); ?></td>
                            <td><?php echo number_format($budget['limit_amount'], 2); ?></td>
                            <td><?php echo number_format($budget['remaining_amount'], 2); ?></td>
                            <td>
                                <?php
                                    $percentage = ($budget['remaining_amount'] / $budget['limit_amount']) * 100;
                                    if ($percentage <= 20) {
                                        echo "<span style='color: red;'>overbudget</span>";
                                    } else {
                                        echo "<span style='color: green;'>on track budget</span>";
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <h1>Budget</h1>

        <form method="POST">
            <div class="form-group">
                <label for="category">Category</label>
                <select name="category" id="category" required style="background-color:#789DE5">
                    <option value="Groceries">Groceries</option>
                    <option value="Tuition">Tuition</option>
                    <option value="Entertainment">Entertainment</option>
                </select>
            </div>
            <div class="form-group">
                <label for="limit">Set Budget Limit</label>
                <input type="number" step="0.01" name="limit" id="limit" required>
            </div>
            <button type="submit"  name="set_budget" class="btn btn-success">Budget</button>
        </form>

      
        
    </div>
</body>
</html>
