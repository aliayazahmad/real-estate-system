<?php
session_start();
include 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "Access denied";
    exit();
}

$id = $_POST['id'];
$title = trim($_POST['title']);
$location = trim($_POST['location']);
$price = trim($_POST['price']);
$description = trim($_POST['description']);

if (empty($title) || empty($location) || empty($price)) {
    echo "Please fill all required fields!";
    exit();
}

/* normalize title: lowercase + remove extra spaces */
$normalizedTitle = strtolower(preg_replace('/\s+/', ' ', $title));
$normalizedTitleEscaped = mysqli_real_escape_string($conn, $normalizedTitle);

/* get current property */
$current = mysqli_query($conn, "SELECT * FROM properties WHERE id='$id'");
$currentRow = mysqli_fetch_assoc($current);

if (!$currentRow) {
    echo "Property not found";
    exit();
}

/* check duplicate title excluding current property */
$sqlCheck = "SELECT id, title FROM properties WHERE id != '$id'";
$allRows = mysqli_query($conn, $sqlCheck);

while ($row = mysqli_fetch_assoc($allRows)) {
    $dbTitleNormalized = strtolower(preg_replace('/\s+/', ' ', trim($row['title'])));

    if ($dbTitleNormalized === $normalizedTitle) {
        echo "Another property with this name already exists!";
        exit();
    }
}

/* keep old image unless new one uploaded */
$image = $currentRow['image'];

if (!empty($_FILES['image']['name'])) {
    $newImage = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];

    if (move_uploaded_file($tmp, "uploads/" . $newImage)) {
        $image = $newImage;
    }
}

/* update */
$sql = "UPDATE properties 
        SET title='$title',
            location='$location',
            price='$price',
            description='$description',
            image='$image'
        WHERE id='$id'";

if (mysqli_query($conn, $sql)) {
    header("Location: properties.php");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>