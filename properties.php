<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

include 'php/db.php';

$result = mysqli_query($conn, "SELECT * FROM properties");
?>
<!DOCTYPE html>
<html>
<head>
<title>Properties</title>

<style>
body {
    font-family: Arial;
    background: #f4f4f4;
}

/* NAVBAR */
.nav {
    background: #111;
    padding: 15px;
    text-align: center;
}

.nav a {
    color: white;
    margin: 15px;
    text-decoration: none;
    font-weight: bold;
}

/* CARD DESIGN */
.card {
    background: white;
    width: 300px;
    display: inline-block;
    margin: 15px;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 0 10px gray;
    text-align: center;
}

.card img {
    width: 100%;
    border-radius: 10px;
}

button {
    background: green;
    color: white;
    padding: 10px;
    border: none;
    margin-top: 10px;
    cursor: pointer;
}
</style>

</head>

<body>

<!-- NAVBAR -->
<div class="nav">
<a href="index.html">Home</a>
<a href="properties.php">Properties</a>
<a href="my_bookings.php">My Bookings</a>
<a href="logout.php">Logout</a>
</div>

<h2 style="text-align:center;">Available Properties</h2>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<div class="card">

<?php if(!empty($row['image'])){ ?>
<img src="uploads/<?php echo $row['image']; ?>">
<?php } ?>

<h3><?php echo $row['title']; ?></h3>
<p><?php echo $row['location']; ?></p>
<p>₹<?php echo $row['price']; ?></p>

<form action="book.php" method="POST">
<input type="hidden" name="property_id" value="<?php echo $row['id']; ?>">
<button>Book Now</button>
</form>

</div>

<?php } ?>

</body>
</html>