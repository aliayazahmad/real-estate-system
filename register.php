<?php
include 'php/db.php';

$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password_raw = $_POST['password'];

if (empty($name) || empty($email) || empty($password_raw)) {
    echo "Please fill all fields!";
    exit();
}

$check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

if (mysqli_num_rows($check) > 0) {
    echo "Email already registered!";
    exit();
}

$password = password_hash($password_raw, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (name, email, password, role)
        VALUES ('$name', '$email', '$password', 'user')";

if (mysqli_query($conn, $sql)) {
    header("Location: login.html");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>