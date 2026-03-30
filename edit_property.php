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

$id = $_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM properties WHERE id='$id'");
$row = mysqli_fetch_assoc($result);

if (!$row) {
    echo "Property not found";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Property</title>
</head>
<body>

<h2>Edit Property</h2>

<form action="update_property.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

    <label>Title:</label><br>
    <input type="text" name="title" value="<?php echo $row['title']; ?>" required><br><br>

    <label>Location:</label><br>
    <input type="text" name="location" value="<?php echo $row['location']; ?>" required><br><br>

    <label>Price:</label><br>
    <input type="number" name="price" value="<?php echo $row['price']; ?>" required><br><br>

    <label>Description:</label><br>
    <textarea name="description"><?php echo $row['description']; ?></textarea><br><br>

    <?php if (!empty($row['image'])) { ?>
        <img src="uploads/<?php echo $row['image']; ?>" width="200"><br><br>
    <?php } ?>

    <label>New Image (optional):</label><br>
    <input type="file" name="image"><br><br>

    <button type="submit">Update Property</button>
</form>

</body>
</html>