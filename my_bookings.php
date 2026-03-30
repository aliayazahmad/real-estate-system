<?php
session_start();
include 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT properties.title, properties.location, properties.price, properties.image, bookings.booking_date
        FROM bookings
        JOIN properties ON bookings.property_id = properties.id
        WHERE bookings.user_id = '$user_id'
        ORDER BY bookings.id DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Bookings</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="nav">
    <a href="index.html">Home</a>
    <a href="properties.php">Properties</a>
    <a href="my_bookings.php">My Bookings</a>
    <a href="logout.php">Logout</a>
</div>

<h2 class="page-title">My Bookings</h2>

<div class="section card-grid">

<?php if (mysqli_num_rows($result) > 0) { ?>

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <div class="card">

            <?php if (!empty($row['image'])) { ?>
                <img src="uploads/<?php echo $row['image']; ?>">
            <?php } ?>

            <h3><?php echo $row['title']; ?></h3>
            <p><?php echo $row['location']; ?></p>
            <p>₹<?php echo $row['price']; ?></p>
            <p><strong>Booked on:</strong> <?php echo $row['booking_date']; ?></p>

        </div>
    <?php } ?>

<?php } else { ?>

    <div style="text-align:center; width:100%; margin-top:40px;">
        <h3>No bookings yet</h3>
        <p style="color:gray;">Explore properties and book your first one.</p>
        <a href="properties.php" class="btn" style="margin-top:10px;">Browse Properties</a>
    </div>

<?php } ?>

</div>

</body>
</html>