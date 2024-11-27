<?php
// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php"); // Redirect to the profile page if logged in
    exit;
}

// Redirect to login page
header("Location: login.php");
exit;
?>
