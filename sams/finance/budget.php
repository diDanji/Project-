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
    <style>
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
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

        
         <!-- Sidebar -->
         <div class="sidebar">
        <h2>User Dashboard</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="expenses.php">Expenses</a>
        <a href="budget.php">Budget</a>
        <a href="reports.php">Reports</a>
        <a href="profile.php">Profile</a>
        <a href="signout.php" onclick="return confirmLogout()">Logout</a>
    </div>

    <div class="container">
        <h1>Manage Budget</h1>

        <!-- Budget Form -->
        <form method="POST">
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
                <label for="limit">Set Budget Limit</label>
                <input type="number" step="0.01" name="limit" id="limit" required>
            </div>
            <button type="submit" name="set_budget" class="btn">Set Budget</button>
        </form>

        <!-- Display Budgets -->
        <h2>Current Budgets</h2>
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
                        <td colspan="4" style="text-align:center;">No budgets.</td>
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
                                        echo "<span style='color: red;'>Approaching Limit</span>";
                                    } else {
                                        echo "<span style='color: green;'>Within Budget</span>";
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
