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
            font-family: Poppins, sans-serif;
            background-image: linear-gradient(rgba(0,0,0,0.7),rgba(0,0,0,0.7)),url(images/pic4.jfif);
            background-size: cover;
            background-position: center;
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
            background-color: #ea1538;
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
    color:black;
    font-size: 25px;
    text-align:center;
}

h2{
   
}

    </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
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

    <div class="container">
    <h1>Student Budgets</h1>

        <!-- Budget Form -->
        <form method="POST">
            <div class="form-group">
                <label for="category">Category</label>
                <select name="category" id="category" required style="background-color:#789DE5">
                <option value="Transportation">Transportation</option>
                    <option value="Meals">Meals</option>
                    <option value="Accommodation">Accommodation</option>
                    <option value="Tuition">Tuition</option>
                    <option value="Personal Spending">Personal Business</option>
                </select>
            </div>
            <div class="form-group">
                <label for="limit">Set Budget Limit</label>
                <input type="number" step="0.01" name="limit" id="limit" required>
            </div>
            <button type="submit"  name="set_budget" class="btn btn-success">Set Budget</button>
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
                        <td colspan="4" style="text-align:center;">No budgets set.</td>
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
