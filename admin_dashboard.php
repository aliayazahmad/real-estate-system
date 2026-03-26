<?php
session_start();
if(!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1){
    header("Location: login.html");
    exit();
}

include 'php/db.php';

// Approve property
if(isset($_GET['approve_id'])){
    $id = intval($_GET['approve_id']);
    $sql = "UPDATE properties SET status='approved' WHERE id=$id";
    mysqli_query($conn, $sql);
    header("Location: admin_dashboard.php?success=Property approved!");
    exit();
}

// Reject property (delete)
if(isset($_GET['reject_id'])){
    $id = intval($_GET['reject_id']);
    // First delete image from server
    $res = mysqli_query($conn, "SELECT image FROM properties WHERE id=$id");
    if(mysqli_num_rows($res)){
        $row = mysqli_fetch_assoc($res);
        @unlink("uploads/".$row['image']);
    }
    mysqli_query($conn, "DELETE FROM properties WHERE id=$id");
    header("Location: admin_dashboard.php?success=Property rejected/deleted!");
    exit();
}

// Fetch all pending properties
$result = mysqli_query($conn, "SELECT p.*, u.email FROM properties p JOIN users u ON p.user_id=u.id WHERE status='pending' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f7fb; margin:0; padding:0; }
header { background: #007BFF; color: #fff; padding: 15px 30px; display:flex; justify-content:space-between; align-items:center; }
header h1 { font-size:22px; }
header a { color: #fff; text-decoration:none; font-weight:bold; }

.container { padding:30px; max-width:1200px; margin:auto; display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:20px; }
.property-card { background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 8px 25px rgba(0,0,0,0.1); position:relative; transition:0.2s; }
.property-card:hover { transform:translateY(-5px); }
.property-card img { width:100%; height:180px; object-fit:cover; border-top-left-radius:10px; border-top-right-radius:10px; }
.property-card .info { padding:15px; }
.property-card .info h3 { margin-bottom:10px; color:#333; }
.property-card .info p { color:#555; font-size:14px; margin-bottom:5px; }
.property-card .info span { font-weight:bold; color:#007BFF; }

button { margin-top:5px; border:none; padding:6px 10px; border-radius:5px; cursor:pointer; font-size:12px; color:#fff; transition:0.3s; margin-right:5px; }
.btn-approve { background:#28a745; }
.btn-approve:hover { background:#218838; }
.btn-reject { background:#dc3545; }
.btn-reject:hover { background:#b02a37; }

.toast { position: fixed; top: 20px; right: 20px; min-width: 200px; padding: 15px 20px; border-radius: 8px; color: #fff; font-weight: bold; display: none; z-index: 9999; opacity: 0; transition: opacity 0.5s, transform 0.5s; }
.toast.show { display: block; opacity: 1; transform: translateY(0); }
.toast.success { background: #28a745; }
.toast.error { background: #dc3545; }
</style>
</head>
<body>

<header>
  <h1>Admin Dashboard</h1>
  <a href="logout.php">Logout</a>
</header>

<div id="toast" class="toast"></div>

<div class="container">
<?php
if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        ?>
        <div class="property-card">
            <img src="uploads/<?php echo $row['image']; ?>" alt="Property">
            <div class="info">
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p>Location: <?php echo htmlspecialchars($row['location']); ?></p>
                <p>Price: <span>₹<?php echo htmlspecialchars($row['price']); ?></span></p>
                <p>Uploaded by: <?php echo htmlspecialchars($row['email']); ?></p>
                <button class="btn-approve" onclick="window.location.href='admin_dashboard.php?approve_id=<?php echo $row['id']; ?>'">Approve</button>
                <button class="btn-reject" onclick="if(confirm('Are you sure?')) window.location.href='admin_dashboard.php?reject_id=<?php echo $row['id']; ?>'">Reject</button>
            </div>
        </div>
        <?php
    }
} else {
    echo '<p style="grid-column:1/-1;text-align:center;color:#555;">No pending properties.</p>';
}
?>
</div>

<script>
const params = new URLSearchParams(window.location.search);
const toast = document.getElementById("toast");

if(params.get("success")){
    toast.textContent = params.get("success");
    toast.className = "toast success show";
    setTimeout(()=>{ toast.classList.remove("show"); },4000);
}
if(params.get("error")){
    toast.textContent = params.get("error");
    toast.className = "toast error show";
    setTimeout(()=>{ toast.classList.remove("show"); },4000);
}
</script>

</body>
</html>