<?php
// Start the session
session_start();
// Destroy all session data
session_unset();
session_destroy();
// Redirect to the login page
header("Location: login.php");
exit();


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out</title>
</head>
<body>
    <h1>You have been logged out.</h1>
    <p><a href="login.php">Click here to log in again</a>.</p>
</body>
</html>







