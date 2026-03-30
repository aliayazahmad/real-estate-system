<?php
session_start();
include 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($search)) {
    $result = mysqli_query($conn, 
        "SELECT * FROM properties 
         WHERE location LIKE '%$search%' 
         ORDER BY id DESC");
} else {
    $result = mysqli_query($conn, 
        "SELECT * FROM properties ORDER BY id DESC");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Properties</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<!-- NAVBAR -->
<div class="nav">
    <a href="index.html">Home</a>
    <a href="properties.php">Properties</a>
    <a href="my_bookings.php">My Bookings</a>
    <a href="logout.php">Logout</a>
</div>

<!-- TITLE -->
<h2 class="page-title">Available Properties</h2>

<!-- SEARCH -->
<div class="search-bar">
    <form method="GET">
        <input type="text" name="search" placeholder="Search by location">
        <button type="submit">Search</button>
    </form>
</div>

<!-- CARDS -->
<div class="section card-grid">

<?php while ($row = mysqli_fetch_assoc($result)) { ?>

    <div class="card">

        <?php if (!empty($row['image'])) { ?>
            <img src="uploads/<?php echo $row['image']; ?>">
        <?php } ?>

        <h3><?php echo $row['title']; ?></h3>
        <p><?php echo $row['location']; ?></p>
        <p>₹<?php echo $row['price']; ?></p>

        <!-- ACTION BUTTONS -->
        <div class="action-group">

            <!-- LEFT -->
            <div class="action-left">
                <form action="book.php" method="POST">
                    <input type="hidden" name="property_id" value="<?php echo $row['id']; ?>">
                    <button class="btn">Book Now</button>
                </form>
            </div>

            <!-- RIGHT -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') { ?>
            <div class="action-right">

                <a href="edit_property.php?id=<?php echo $row['id']; ?>" 
                   class="btn btn-warning">Edit</a>

                <form action="delete.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <button class="btn btn-danger"
                    onclick="return confirm('Are you sure you want to delete this property?')">
                    Delete
                    </button>
                </form>

            </div>
            <?php } ?>

        </div>

    </div>

<?php } ?>

</div>

</body>
</html>