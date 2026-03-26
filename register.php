<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'php/db.php';

$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO users (name, email, password, role) 
        VALUES ('$name', '$email', '$password', 'user')";

if(mysqli_query($conn, $sql)){
    echo "Registration Successful!";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>