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

// Get order details
function getOrderDetails($order_id) {
    global $conn;
    try {
        $sql = "SELECT * FROM orders WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        return [];
    }
}

function updateOrderPayment($order_id, $payment_method, $screenshot_path) {
    global $conn;
    try {
        $sql = "UPDATE orders SET payment_method = ?, payment_screenshot = ?, status = 'payment_pending' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $payment_method, $screenshot_path, $order_id);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment'])) {
    $order_id = $_POST['order_id'];
    $payment_method = $_POST['payment_method'] ?? 'GCash';
    
    // Handle file upload
    $screenshot_path = '';
    if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'payment_screenshots/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['payment_screenshot']['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        
        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            if ($_FILES['payment_screenshot']['size'] <= 5 * 1024 * 1024) {
                $filename = uniqid() . '_' . time() . '.' . $file_extension;
                $screenshot_path = $upload_dir . $filename;
                move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], $screenshot_path);
            }
        }
    }
    
    // Update order with payment details
    if (updateOrderPayment($order_id, $payment_method, $screenshot_path)) {
        $_SESSION['payment_submitted'] = true;
        header("Location: order_status.php?order_id=" . $order_id);
        exit();
    } else {
        $error_message = "Failed to submit payment. Please try again.";
    }
}

$order_details = getOrderDetails($order_id);

// Check if order exists and is approved
if (!$order_details) {
    header("Location: menu.php");
    exit();
}

if ($order_details['status'] !== 'approved') {
    header("Location: order_status.php?order_id=" . $order_id);
    exit();
}

$order_number = $_SESSION['current_order_number'] ?? 'ORD' . date('Ymd') . $order_id;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Proceed to Payment | The Other Side Cafe</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f9f6f1; font-family: 'Poppins', sans-serif; }
.navbar { background-color: #6f4e37 !important; }
.navbar-brand, .nav-link { color: #fff !important; font-weight: 500; }
.container { margin-top: 50px; }
.card { padding: 30px; border: none; background-color: #fff8f0; border-radius: 15px; }
.btn-coffee { background-color: #6f4e37; color: white; border: none; padding: 12px 30px; }
.btn-coffee:hover { background-color: #5a3c29; }
.upload-area { border: 2px dashed #6f4e37; border-radius: 10px; padding: 20px; text-align: center; cursor: pointer; }
.upload-area:hover { background-color: #f8f9fa; }
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
      <div class="card">
        
        <div class="text-center mb-4">
          <h2>Proceed to Payment</h2>
          <p class="text-muted">Order #<?php echo $order_number; ?></p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Order Summary -->
        <div class="order-summary bg-light p-4 rounded mb-4">
          <h5>Order Summary:</h5>
          <p><strong>Product:</strong> <?php echo htmlspecialchars($order_details['product_name']); ?></p>
          <p><strong>Quantity:</strong> <?php echo $order_details['quantity']; ?></p>
          <p><strong>Total Amount:</strong> ₱<?php echo number_format($order_details['total_price'], 2); ?></p>
        </div>

        <!-- Payment Form -->
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
          
          <div class="mb-4">
            <h5>Payment Method</h5>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="payment_method" value="GCash" id="gcash" checked>
              <label class="form-check-label" for="gcash">
                GCash
              </label>
            </div>
          </div>

          <!-- GCash Instructions -->
          <div class="alert alert-info mb-4">
            <h6><i class="bi bi-info-circle"></i> GCash Payment Instructions:</h6>
            <p class="mb-1">1. Open your GCash app</p>
            <p class="mb-1">2. Send payment to: <strong>09123456789</strong></p>
            <p class="mb-1">3. Account Name: <strong>The Other Side Cafe</strong></p>
            <p class="mb-1">4. Amount: <strong>₱<?php echo number_format($order_details['total_price'], 2); ?></strong></p>
            <p class="mb-0">5. Take a screenshot of the payment confirmation</p>
          </div>

          <!-- File Upload -->
          <div class="mb-4">
            <label class="form-label"><strong>Upload Payment Screenshot:</strong></label>
            <div class="upload-area mb-2" onclick="document.getElementById('payment_screenshot').click()">
              <i class="bi bi-cloud-upload display-4 text-muted"></i>
              <p class="mt-2 mb-1"><strong>Click to upload GCash receipt</strong></p>
              <p class="text-muted small">JPG, PNG - max 5MB</p>
            </div>
            <input type="file" class="form-control d-none" id="payment_screenshot" name="payment_screenshot" accept="image/*" required onchange="previewImage(this)">
            <div class="preview-container mt-2"></div>
            <div class="form-text">Upload a clear screenshot of your GCash payment confirmation</div>
          </div>

          <button type="submit" name="submit_payment" class="btn btn-coffee btn-lg w-100">
            <i class="bi bi-send-check"></i> Submit Payment Receipt
          </button>
        </form>

        <div class="text-center mt-3">
          <a href="order_status.php?order_id=<?php echo $order_id; ?>" class="btn btn-outline-secondary">Back to Order Status</a>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
function previewImage(input) {
    const previewContainer = document.querySelector('.preview-container');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewContainer.innerHTML = '<img src="' + e.target.result + '" class="img-fluid rounded mt-2" style="max-height: 200px;">';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>