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
         *{
            font-family: Poppins, sans-serif;
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
            font-size: 1rem;
            margin-bottom: 20px;
            color:#789DE5
        }


        body {
            font-family: Poppins, sans-serif;
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
            color: #6B2346;f;
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
            background-color: #6B2346;
            color: white;
        }
        .form-group {
            margin: 15px 0;
            color: #6B2346
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #6B2346;
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
   <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>
<body>

<ul class="nav nav-tabs">
  <li class="nav-item">
    <a class="nav-link active" aria-current="page" href="dashboard.php">Dashboard</a>
  </li>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">More</a>
    <ul class="dropdown-menu">
      <li><a class="dropdown-item" href="expenses.php">Expenses</a></li>
      <li><a class="dropdown-item" href="budget.php">Budget</a></li>
      <li><a class="dropdown-item" href="reports.php">Reports</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item" href="signout.php">Logout</a></li>
    </ul>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="profile.php">Profile</a></li>
  </li>
</ul>
    <div class="container">
        <h1>Student Expenses</h1>

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
                <select name="category"  style="background-color:#789DE5" id="category" required>
                   
                    <option value="Transportation">Transportation</option>
                    <option value="Meals">Meals</option>
                    <option value="Accommodation">Accommodation</option>
                    <option value="Tuition">Tuition</option>
                    <option value="Personal Spending">Personal Business</option>
                </select>
            </div>
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" name="date" id="date" required>
            </div>
            <button type="submit"  name="add_expense" class="btn btn-success">Add Expense</button>
            <!-- <button type="submit" name="add_expense" class="btn">Add Expense</button> -->
        </form>

        <!-- Display Expenses -->
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    
                    <th>Amount</th>
                    <th>Category</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($expenses)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No expenses recorded yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($expenses as $expense): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($expense['date']); ?></td>
                            <td><?php echo htmlspecialchars($expense['description']); ?></td>
                            
                            <td><?php echo number_format($expense['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($expense['category']); ?></td>
                           
                            <td>
                                <a href="delete.php?id=<?php echo $expense['id']; ?>"class="delete-btn" onclick="return confirm('Are you sure you want to delete this expense?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
