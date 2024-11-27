<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include the database connection
require_once 'config.php';

// Fetch expenses for the logged-in user
try {
    $query = "SELECT * FROM expenses WHERE user_id = :user_id ORDER BY date DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching expenses: " . $e->getMessage());
}

// Handle adding a new expense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $date = $_POST['date'];

    try {
        $query = "INSERT INTO expenses (user_id, amount, description, category, date) VALUES (:user_id, :amount, :description, :category, :date)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'amount' => $amount,
            'description' => $description,
            'category' => $category,
            'date' => $date,
        ]);
        header("Location: expenses.php");
        exit;
    } catch (PDOException $e) {
        die("Error adding expense: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .delete-btn {
            color: red;
            cursor: pointer;
            text-decoration: underline;
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
        <h1>Expenses</h1>

        <!-- Display Expenses -->
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($expenses)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No pending expenses.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($expenses as $expense): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($expense['date']); ?></td>
                            <td><?php echo htmlspecialchars($expense['description']); ?></td>
                            <td><?php echo htmlspecialchars($expense['category']); ?></td>
                            <td><?php echo number_format($expense['amount'], 2); ?></td>
                           
                            <td>
                                <a href="delete_expense.php?id=<?php echo $expense['id']; ?>"class="delete-btn" onclick="return confirm('Are you sure you want to delete this expense?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Add Expense Form -->
        <form method="POST">
        <div class="form-group">
                <label for="description">Description</label>
                <input type="text" name="description" id="description" required>
            </div>
            <div class="form-group">
            <label for="amount">Amount</label>
                <input type="number" step="0.01" name="amount" id="amount" required>
            </div>
            
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" name="date" id="date" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <select name="category"  style="background-color:#789DE5" id="category" required>
                   
                    <option value="Groceries">Groceries</option>
                    <option value="Tuition">Tuition</option>
                    <option value="Entertainment">Entertainment</option>
                </select>
            </div>
           
            </div>
            <button type="submit" name="add_expense" class="btn">Expense</button>
        </form>

        
    </div>
</body>
</html>
