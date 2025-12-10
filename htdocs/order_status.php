<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Get order ID from URL or session
$order_id = $_GET['order_id'] ?? $_SESSION['current_order_id'] ?? 0;
if (!$order_id) {
    header("Location: menu.php");
    exit();
}

// Get order details from database
function getOrderDetails($order_id) {
    global $conn;
    try {
        $sql = "SELECT o.*, u.username FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        return null;
    }
}

function getOrderStatus($order_id) {
    global $conn;
    try {
        $sql = "SELECT status FROM orders WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['status'];
        }
        return 'not_found';
    } catch (Exception $e) {
        return 'not_found';
    }
}

$order_details = getOrderDetails($order_id);
$order_status = getOrderStatus($order_id);

// If order not found, clear session and redirect
if (!$order_details || $order_status === 'not_found') {
    unset($_SESSION['current_order_id']);
    unset($_SESSION['current_order_number']);
    header("Location: menu.php?error=order_not_found");
    exit();
}

$order_number = 'ORD' . date('Ymd') . $order_id;

// Store in session for process_payment.php
$_SESSION['current_order_id'] = $order_id;
$_SESSION['current_order_number'] = $order_number;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status | The Other Side Cafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f9f6f1; font-family: 'Poppins', sans-serif; }
        .navbar { background-color: #6f4e37 !important; }
        .navbar-brand, .nav-link { color: #fff !important; font-weight: 500; }
        .container { margin-top: 50px; }
        .card { padding: 30px; border: none; background-color: #fff8f0; border-radius: 15px; }
        .btn-coffee { background-color: #6f4e37; color: white; border: none; padding: 12px 30px; }
        .btn-success { background-color: #28a745; color: white; border: none; padding: 12px 30px; }
        .status-pending { background: linear-gradient(135deg, #fff3cd, #ffeaa7); border: 2px solid #ffc107; color: #856404; }
        .status-approved { background: linear-gradient(135deg, #e7f3ff, #d4e7ff); border: 2px solid #007bff; color: #004085; }
        .status-payment { background: linear-gradient(135deg, #fff3cd, #ffeaa7); border: 2px solid #ffc107; color: #856404; }
        .status-confirmed { background: linear-gradient(135deg, #d1ecf1, #bee5eb); border: 2px solid #17a2b8; color: #0c5460; }
        .status-preparing { background: linear-gradient(135deg, #ffe5cc, #ffd8b3); border: 2px solid #fd7e14; color: #8c4500; }
        .status-ready { background: linear-gradient(135deg, #d4edda, #c3e6cb); border: 2px solid #28a745; color: #155724; }
        .status-completed { background: linear-gradient(135deg, #e8f5e8, #d4edd4); border: 2px solid #28a745; color: #155724; }
        .status-notfound { background: linear-gradient(135deg, #f8d7da, #f5c6cb); border: 2px solid #dc3545; color: #721c24; }
        .thank-you-message { background: linear-gradient(135deg, #f0f8ff, #e6f3ff); border: 2px solid #6f4e37; color: #2c1810; }
        .progress-bar-custom { background-color: #6f4e37; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">☕ The Other Side Cafe</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="menu.php">Menu</a></li>
        <?php if (isset($_SESSION['user_logged_in'])): ?>
          <li class="nav-item"><a class="nav-link" href="#">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card text-center">
        
        <div class="mb-4">
          <h2>Order Status</h2>
          <p class="text-muted">Order #<?php echo $order_number; ?></p>
        </div>

        <!-- Order Details -->
        <div class="order-details bg-light p-4 rounded mb-4 text-start">
          <h5>Order Information:</h5>
          <p><strong>Product:</strong> <?php echo htmlspecialchars($order_details['product_name']); ?></p>
          <p><strong>Quantity:</strong> <?php echo $order_details['quantity']; ?></p>
          <p><strong>Price per item:</strong> ₱<?php echo number_format($order_details['price'], 2); ?></p>
          <p><strong>Total Amount:</strong> ₱<?php echo number_format($order_details['total_price'], 2); ?></p>
          <p><strong>Category:</strong> <?php echo ucfirst($order_details['category']); ?></p>
          <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order_details['created_at'])); ?></p>
        </div>

        <!-- Order Progress -->
        <div class="progress mb-4" style="height: 10px;">
            <?php
            $progress_steps = [
                'pending_approval' => 0,
                'approved' => 20,
                'payment_pending' => 40,
                'confirmed' => 60,
                'preparing' => 80,
                'ready' => 90,
                'completed' => 100
            ];
            $progress = $progress_steps[$order_status] ?? 0;
            ?>
            <div class="progress-bar progress-bar-custom" role="progressbar" style="width: <?php echo $progress; ?>%" 
                 aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <!-- Status Display -->
        <?php if ($order_status === 'pending_approval'): ?>
        <div class="status-pending p-4 rounded mb-4">
          <h3><i class="bi bi-clock-history"></i> Waiting for Approval</h3>
          <p>Your order is pending admin approval. You'll be able to proceed with payment once approved.</p>
          <div class="spinner-border text-warning" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3"><small>Next: Proceed to payment after approval</small></p>
        </div>

        <?php elseif ($order_status === 'approved'): ?>
        <div class="status-approved p-4 rounded mb-4">
          <h3><i class="bi bi-check-circle"></i> Order Approved!</h3>
          <p>Your order has been approved! You can now proceed with the payment.</p>
          <a href="process_payment.php?order_id=<?php echo $order_id; ?>" class="btn btn-success btn-lg">
            <i class="bi bi-credit-card"></i> Proceed to Payment
          </a>
          <p class="mt-3"><small>Next: Submit your payment receipt for verification</small></p>
        </div>

        <?php elseif ($order_status === 'payment_pending'): ?>
        <div class="status-payment p-4 rounded mb-4">
          <h3><i class="bi bi-credit-card"></i> Payment Verification</h3>
          <p>Your payment receipt has been submitted and is awaiting verification by our team.</p>
          <div class="spinner-border text-warning" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3"><small>Next: Order confirmation after payment verification</small></p>
        </div>

        <?php elseif ($order_status === 'confirmed'): ?>
        <div class="status-confirmed p-4 rounded mb-4">
          <h3><i class="bi bi-cup-hot"></i> Payment Confirmed!</h3>
          <p>Your payment has been verified and your order is now in our preparation queue.</p>
          <p><strong>Thank you for your payment! We'll start preparing your order shortly.</strong></p>
          <p class="mt-3"><small>Next: Our baristas will start preparing your order</small></p>
        </div>

        <?php elseif ($order_status === 'preparing'): ?>
        <div class="status-preparing p-4 rounded mb-4">
          <h3><i class="bi bi-cup-straw"></i> Preparing Your Order</h3>
          <p>Our baristas are carefully crafting your <strong><?php echo htmlspecialchars($order_details['product_name']); ?></strong> with love and precision.</p>
          <div class="spinner-border text-warning" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3"><strong>Good things take time - your perfect drink is being made!</strong></p>
          <p class="mt-3"><small>Next: Your order will be ready for pickup soon</small></p>
        </div>

        <?php elseif ($order_status === 'ready'): ?>
        <div class="status-ready p-4 rounded mb-4">
          <h3><i class="bi bi-check-circle-fill"></i> Order Ready for Pickup!</h3>
          <p>Your order is ready! Please proceed to the counter to collect your items.</p>
          <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> 
            <strong>Please bring your order number (#<?php echo $order_number; ?>) when picking up.</strong>
          </div>
          <p><strong>We can't wait for you to enjoy your delicious <?php echo htmlspecialchars($order_details['product_name']); ?>!</strong></p>
        </div>

        <?php elseif ($order_status === 'completed'): ?>
        <div class="status-completed p-4 rounded mb-4">
          <div class="thank-you-message p-4 rounded mb-3">
            <h3><i class="bi bi-heart-fill" style="color: #e74c3c;"></i> Thank You for Your Purchase!</h3>
            <p class="lead">We hope you enjoyed every sip and bite of your experience at The Other Side Cafe!</p>
            <p>Your support means the world to us. We're already looking forward to serving you again soon!</p>
          </div>
          <div class="p-3">
            <h4>🎉 Order Successfully Completed</h4>
            <p>We appreciate you choosing us for your coffee break. Come back soon - your next favorite drink is waiting to be discovered!</p>
            <div class="mt-4">
              <p><strong>Want to enjoy another delicious experience?</strong></p>
              <a href="menu.php" class="btn btn-coffee btn-lg">
                <i class="bi bi-cup-hot"></i> Order Again
              </a>
            </div>
          </div>
        </div>

        <?php elseif ($order_status === 'cancelled'): ?>
        <div class="status-notfound p-4 rounded mb-4">
          <h3><i class="bi bi-x-circle"></i> Order Cancelled</h3>
          <p>This order has been cancelled. We'd love to have another chance to serve you!</p>
          <a href="menu.php" class="btn btn-coffee">Place New Order</a>
        </div>

        <?php else: ?>
        <div class="status-notfound p-4 rounded mb-4">
          <h3><i class="bi bi-exclamation-triangle"></i> Order Not Found</h3>
          <p>We couldn't find this order in our system. It may have been completed or removed.</p>
          <a href="menu.php" class="btn btn-coffee">Browse Menu</a>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="mt-4">
          <a href="menu.php" class="btn btn-coffee me-3">
            <i class="bi bi-cup-hot"></i> Order Again
          </a>
          <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-house"></i> Back to Home
          </a>
        </div>

        <!-- Auto-refresh notice -->
        <div class="mt-3">
          <small class="text-muted">Page auto-refreshes every 15 seconds for real-time updates</small>
        </div>

      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<script>
// Auto-reload every 15 seconds
setTimeout(function() {
    location.reload();
}, 15000);
</script>

</body>
</html>