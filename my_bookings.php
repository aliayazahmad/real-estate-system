<?php
session_start();
include 'php/db.php';

$user_id = $_SESSION['user_id'];

$sql = "SELECT properties.title, bookings.booking_date 
        FROM bookings 
        JOIN properties ON bookings.property_id = properties.id
        WHERE bookings.user_id = '$user_id'";

$result = mysqli_query($conn, $sql);
?>

<h2>My Bookings</h2>

<?php
while($row = mysqli_fetch_assoc($result)){
    echo "<p>Property: " . $row['title'] . " | Date: " . $row['booking_date'] . "</p>";
}
?>