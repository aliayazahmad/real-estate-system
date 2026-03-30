<?php
session_start();
include 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

/* OPTIONAL: only admin can delete */
if ($_SESSION['role'] != 'admin') {
    echo "Access denied";
    exit();
}

$id = $_POST['id'];

mysqli_query($conn, "DELETE FROM properties WHERE id='$id'");

header("Location: properties.php");
exit();
?>