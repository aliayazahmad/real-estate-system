<?php
include 'php/db.php';

$title = $_POST['title'];
$location = $_POST['location'];
$price = $_POST['price'];
$description = $_POST['description'];

$image = $_FILES['image']['name'];
$tmp = $_FILES['image']['tmp_name'];

// ❌ VALIDATION (VERY IMPORTANT)
if(empty($title) || empty($location) || empty($price)){
    echo "Please fill all required fields!";
    exit();
}

// Upload image if exists
if(!empty($image)){
    move_uploaded_file($tmp, "uploads/" . $image);
}

// Check duplicate
$check = mysqli_query($conn, "SELECT * FROM properties WHERE title='$title'");

if(mysqli_num_rows($check) > 0){
    echo "Property with this name already exists!";
} else {

    $sql = "INSERT INTO properties(title, location, price, description, image)
            VALUES('$title', '$location', '$price', '$description', '$image')";

    if(mysqli_query($conn, $sql)){
        echo "Property Added!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>