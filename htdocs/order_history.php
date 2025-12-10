<?php
session_start();
include 'db_connect.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Assuming you store user_id in session

// Fetch orders for this user
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY time_started DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | The Other Side Cafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f9f6f1; font-family: 'Poppins', sans-serif; }
        .navbar { background-color: #6f4e37 !important; }
        .navbar-brand, .nav-link { color: #fff !important; font-weight: 500; }
        .navbar-brand:hover, .nav-link:hover { color: #f3e5ab !important; }
        footer { text-align: center; padding: 20px; background-color: #6f4e37; color: white; margin-top: 40px; }
        .status-in-progress { color: #ff9800; font-weight: bold; }
        .status-ready { color: #4caf50; font-weight: bold; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">☕ The Other Side Cafe</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <h2 class="text-center mb-4 fw-bold">Your Orders & Prep Time</h2>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if($result->num_rows > 0): ?>
                <table class="table table-bordered bg-white">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Prep Time Left</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = $result->fetch_assoc()): 
                            // Calculate remaining prep time
                            $prep_time = $order['prep_time']; // in minutes
                            $time_started = strtotime($order['time_started']);
                            $time_now = time();
                            $time_left = ($prep_time*60) - ($time_now - $time_started);
                            $time_left_display = $time_left > 0 ? gmdate("i\m s\s", $time_left) : "Ready";
                            $status = $time_left > 0 ? "In Progress" : "Ready";
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                            <td><?php echo $order['quantity']; ?></td>
                            <td><?php echo $time_left_display; ?></td>
                            <td class="<?php echo $status=="Ready"?"status-ready":"status-in-progress"; ?>">
                                <?php echo $status; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">You have no orders yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer>
    © 2025 The Other Side Cafe | Online Ordering System
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
