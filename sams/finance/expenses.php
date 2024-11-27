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
            'date' => $date
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


        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #007BFF;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #007BFF;
            color: white;
        }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn {
            background-color: #007BFF;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .delete-btn {
            color: red;
            cursor: pointer;
            text-decoration: underline;
        }
    </style>
</head>
<body>


         <!-- Sidebar -->
    <div class="sidebar">
        <h2>User Dashboard</h2>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="expenses.php"><i class="fas fa-money-bill-wave"></i> Expenses</a>
        <a href="budget.php"><i class="fas fa-wallet"></i> Budget</a>
        <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
        <!-- <a href="settings.php"><i class="fas fa-cog"></i> Settings</a> -->
        <a href="signout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

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
                        <td colspan="5" style="text-align:center;">No expenses.</td>
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
                <label for="amount">Amount</label>
                <input type="number" step="0.01" name="amount" id="amount" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <input type="text" name="description" id="description" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <select name="category" id="category" required>
                    <option value="Tuition">Tuition</option>
                    <option value="Accommodation">Accommodation</option>
                    <option value="Meals">Meals</option>
                    <option value="Transportation">Transportation</option>
                    <option value="Personal Spending">Personal Spending</option>
                </select>
            </div>
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" name="date" id="date" required>
            </div>
            <button type="submit" name="add_expense" class="btn">Add Expense</button>
        </form>

      
    </div>
</body>
</html>
