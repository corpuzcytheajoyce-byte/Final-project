<?php
session_start();
include 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    if ($stmt->execute()) {
        $success_message = "Order #$order_id status updated to $new_status";
    }
}

// Handle order deletion
if (isset($_GET['delete_order'])) {
    $order_id = $_GET['delete_order'];
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
}

// Get orders from database
$orders_query = "
    SELECT o.*, u.username 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
";
$orders_result = $conn->query($orders_query);

// Calculate statistics
$total_orders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
$pending_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending_approval'")->fetch_assoc()['total'];
$completed_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'];
$total_revenue_result = $conn->query("SELECT SUM(total_price) as revenue FROM orders WHERE status = 'completed'");
$total_revenue = $total_revenue_result->fetch_assoc()['revenue'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | The Other Side Cafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .sidebar {
            background: linear-gradient(135deg, #6f4e37, #8b6b4f);
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: white;
            padding: 15px 20px;
            margin: 5px 0;
            border-radius: 8px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-left: 4px solid #6f4e37;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .badge-pending_approval { background-color: #ffc107; color: black; }
        .badge-approved { background-color: #17a2b8; color: white; }
        .badge-payment_pending { background-color: #fd7e14; color: white; }
        .badge-confirmed { background-color: #28a745; color: white; }
        .badge-completed { background-color: #6f4e37; color: white; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" style="width: 250px;">
        <div class="text-center mb-4">
            <h4>☕ Admin Panel</h4>
            <p class="small">The Other Side Cafe</p>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link active" href="admin_dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
            <a class="nav-link" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dashboard Overview</h2>
            <div class="text-muted">
                Welcome, <?php echo $_SESSION['admin_username']; ?>!
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted">Total Orders</h5>
                            <h3><?php echo $total_orders; ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-cart fs-1 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted">Pending Approval</h5>
                            <h3><?php echo $pending_orders; ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-clock fs-1 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted">Completed</h5>
                            <h3><?php echo $completed_orders; ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-check-circle fs-1 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted">Revenue</h5>
                            <h3>₱<?php echo number_format($total_revenue, 2); ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-currency-dollar fs-1 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="table-container">
            <div class="p-3 border-bottom">
                <h5 class="mb-0">Recent Orders</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders_result->num_rows > 0): ?>
                            <?php while ($order = $orders_result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['username'] ?? 'Unknown'); ?></td>
                                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                <td><?php echo $order['quantity']; ?></td>
                                <td>₱<?php echo number_format($order['total_price'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order['id']; ?>">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <a href="?delete_order=<?php echo $order['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Delete order #<?php echo $order['id']; ?>?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Order Details Modal -->
                            <div class="modal fade" id="orderModal<?php echo $order['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Order #<?php echo $order['id']; ?> Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Order Information</h6>
                                                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username'] ?? 'Unknown'); ?></p>
                                                    <p><strong>Product:</strong> <?php echo htmlspecialchars($order['product_name']); ?></p>
                                                    <p><strong>Quantity:</strong> <?php echo $order['quantity']; ?></p>
                                                    <p><strong>Price per item:</strong> ₱<?php echo number_format($order['price'], 2); ?></p>
                                                    <p><strong>Total:</strong> ₱<?php echo number_format($order['total_price'], 2); ?></p>
                                                    <p><strong>Category:</strong> <?php echo ucfirst($order['category']); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Payment & Status</h6>
                                                    <p><strong>Payment Method:</strong> <?php echo $order['payment_method'] ?: 'Not set'; ?></p>
                                                    <p><strong>Current Status:</strong> <span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span></p>
                                                    
                                                    <form method="POST" class="mt-3">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label"><strong>Update Status:</strong></label>
                                                            <select name="status" class="form-select">
                                                                <option value="pending_approval" <?php echo $order['status'] == 'pending_approval' ? 'selected' : ''; ?>>Pending Approval</option>
                                                                <option value="approved" <?php echo $order['status'] == 'approved' ? 'selected' : ''; ?>>Approved (Ready for Payment)</option>
                                                                <option value="payment_pending" <?php echo $order['status'] == 'payment_pending' ? 'selected' : ''; ?>>Payment Pending</option>
                                                                <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed (Preparing)</option>
                                                                <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                            </select>
                                                        </div>
                                                        <button type="submit" name="update_status" class="btn btn-primary w-100">Update Status</button>
                                                    </form>
                                                </div>
                                            </div>
                                            <?php if (!empty($order['payment_screenshot'])): ?>
                                            <div class="mt-3">
                                                <h6>Payment Proof</h6>
                                                <img src="<?php echo $order['payment_screenshot']; ?>" alt="Payment Proof" class="img-fluid rounded" style="max-height: 200px;">
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-cart-x fs-1 text-muted"></i>
                                    <p class="mt-2">No orders found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>