<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Real Estate Hub</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        .hero {
            min-height: 88vh;
            background: linear-gradient(rgba(17,24,39,0.6), rgba(17,24,39,0.6)),
                        url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 20px;
        }

        .hero-content {
            max-width: 700px;
            color: white;
        }

        .hero-content h1 {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .hero-content p {
            font-size: 20px;
            margin-bottom: 24px;
            color: #e5e7eb;
        }

        .hero-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-light {
            background: white;
            color: #111827;
        }

        .btn-light:hover {
            background: #e5e7eb;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<div class="nav">
    <a href="index.php">Home</a>
    <a href="properties.php">Properties</a>

    <?php if (isset($_SESSION['user_id'])) { ?>
        <a href="my_bookings.php">My Bookings</a>
        <a href="logout.php">Logout</a>
    <?php } else { ?>
        <a href="login.html">Login</a>
        <a href="register.html">Register</a>
    <?php } ?>
</div>

<!-- HERO SECTION -->
<section class="hero">
    <div class="hero-content">
        <h1>Find a property that feels like home</h1>
        <p>Browse listings, compare options, and book properties easily.</p>

        <div class="hero-actions">
            <a href="properties.php" class="btn">Browse Properties</a>

            <?php if (!isset($_SESSION['user_id'])) { ?>
                <a href="register.html" class="btn btn-light">Create Account</a>
            <?php } ?>
        </div>
    </div>
</section>

</body>
</html>