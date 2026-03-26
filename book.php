<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}

include 'php/db.php';

$property_id = $_POST['property_id'];
$user_id = $_SESSION['user_id'];

$date = date("Y-m-d");

$sql = "INSERT INTO bookings(user_id, property_id, booking_date)
        VALUES('$user_id', '$property_id', '$date')";

if(mysqli_query($conn, $sql)){
    header("Location: my_bookings.php");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>