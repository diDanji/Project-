<?php
session_start();


$conn = new mysqli('localhost', 'root', '', 'management');


if ($conn-> connect_error) {
    die("Error connecting to database: " . $connection->error);

}else {
    echo "Connection available ";
}

include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM expenses WHERE id = '$id'";

        if ($conn->query($sql) === TRUE) {
            echo "Record deleted succesfully";
            header("Location: expenses.php");
} else {

        echo "Error deleting record: " . $conn->error;
    }

}

    
?>

