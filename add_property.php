<?php
session_start();
include 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$title = trim($_POST['title']);
$location = trim($_POST['location']);
$price = trim($_POST['price']);
$description = trim($_POST['description']);

if (empty($title) || empty($location) || empty($price)) {
    echo "Please fill all required fields!";
    exit();
}

/* check duplicate property */
$check = mysqli_query($conn, "SELECT * FROM properties WHERE title='$title'");
if (mysqli_num_rows($check) > 0) {
    echo "Property with this name already exists!";
    exit();
}

/* image upload */
$image = $_FILES['image']['name'];
$tmp = $_FILES['image']['tmp_name'];

if (!empty($image)) {
    move_uploaded_file($tmp, "uploads/" . $image);
}

/* insert */
$sql = "INSERT INTO properties (title, location, price, description, image)
        VALUES ('$title', '$location', '$price', '$description', '$image')";

if (mysqli_query($conn, $sql)) {
    header("Location: properties.php");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>