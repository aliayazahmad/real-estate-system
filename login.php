<?php
session_start();
include 'php/db.php';

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email='$email'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    if (password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        header("Location: properties.php");
        exit();
    } else {
        echo "Wrong Password!";
    }
} else {
    echo "User not found!";
}
?>