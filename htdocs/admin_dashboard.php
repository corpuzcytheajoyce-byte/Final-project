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
        $_SESSION['success_message'] = "Order status updated successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to update order status.";
    }
}

// Handle order deletion
if (isset($_GET['delete_order'])) {
    $order_id = $_GET['delete_order'];
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Order deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to delete order.";
    }
    
    header("Location: admin_dashboard.php");
    exit();
}

// Determine current section
$current_section = $_GET['section'] ?? 'orders';

// Get orders from database
$orders_query = "
    SELECT o.*, u.username 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
";
$orders_result = $conn->query($orders_query);

// Get products summary
$products_query = "
    SELECT product_name, category, COUNT(*) as order_count, SUM(quantity) as total_quantity, SUM(total_price) as total_revenue
    FROM orders 
    WHERE status NOT IN ('cancelled')
    GROUP BY product_name, category
    ORDER BY total_revenue DESC
";
$products_result = $conn->query($products_query);

// Calculate statistics
$total_orders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
$pending_approval_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending_approval'")->fetch_assoc()['total'];
$payment_pending_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'payment_pending'")->fetch_assoc()['total'];
$preparing_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'preparing'")->fetch_assoc()['total'];
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
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.15);
            transform: translateX(5px);
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
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .badge-pending_approval { background-color: #ffc107; color: black; }
        .badge-approved { background-color: #17a2b8; color: white; }
        .badge-payment_pending { background-color: #6f42c1; color: white; }
        .badge-confirmed { background-color: #fd7e14; color: white; }
        .badge-preparing { background-color: #20c997; color: white; }
        .badge-ready { background-color: #28a745; color: white; }
        .badge-completed { background-color: #6f4e37; color: white; }
        .badge-cancelled { background-color: #dc3545; color: white; }
        .section-title {
            color: #6f4e37;
            border-bottom: 2px solid #6f4e37;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .btn-view {
            background-color: #6f4e37;
            color: white;
            border: none;
        }
        .btn-view:hover {
            background-color: #5a3c29;
            color: white;
        }
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
            <a class="nav-link <?php echo $current_section == 'dashboard' ? 'active' : ''; ?>" href="admin_dashboard.php?section=dashboard">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
            <a class="nav-link <?php echo $current_section == 'orders' ? 'active' : ''; ?>" href="admin_dashboard.php?section=orders">
                <i class="bi bi-cart me-2"></i>Order Management
            </a>
            <a class="nav-link <?php echo $current_section == 'products' ? 'active' : ''; ?>" href="admin_dashboard.php?section=products">
                <i class="bi bi-cup-hot me-2"></i>Product Analytics
            </a>
            <a class="nav-link" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Admin Dashboard</h2>
            <div class="text-muted">
                Welcome, <?php echo $_SESSION['admin_username']; ?>!
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <?php if ($current_section == 'dashboard' || !isset($_GET['section'])): ?>
        <!-- Dashboard Overview -->
        <h3 class="section-title">Business Overview</h3>
        
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
                            <h3><?php echo $pending_approval_orders; ?></h3>
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
                            <h5 class="text-muted">In Progress</h5>
                            <h3><?php echo $preparing_orders; ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-cup-straw fs-1 text-info"></i>
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

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="table-container">
                    <div class="p-3 border-bottom">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="p-4">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <a href="admin_dashboard.php?section=orders" class="btn btn-view btn-lg w-100 mb-2">
                                    <i class="bi bi-cart me-2"></i>Manage Orders
                                </a>
                                <small class="text-muted">View and process customer orders</small>
                            </div>
                            <div class="col-md-4">
                                <a href="admin_dashboard.php?section=products" class="btn btn-view btn-lg w-100 mb-2">
                                    <i class="bi bi-cup-hot me-2"></i>Product Analytics
                                </a>
                                <small class="text-muted">See popular products and sales</small>
                            </div>
                            <div class="col-md-4">
                                <a href="admin_dashboard.php?section=orders&filter=pending_approval" class="btn btn-warning btn-lg w-100 mb-2">
                                    <i class="bi bi-clock me-2"></i>Pending Orders
                                </a>
                                <small class="text-muted"><?php echo $pending_approval_orders; ?> orders awaiting approval</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php elseif ($current_section == 'orders'): ?>
        <!-- Orders Management -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="section-title">Order Management</h3>
            <div class="btn-group">
                <a href="admin_dashboard.php?section=orders" class="btn btn-outline-secondary">All Orders</a>
                <a href="admin_dashboard.php?section=orders&filter=pending_approval" class="btn btn-warning">Pending Approval</a>
                <a href="admin_dashboard.php?section=orders&filter=preparing" class="btn btn-info">Preparing</a>
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
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
                            <?php while ($order = $orders_result->fetch_assoc()): 
                                // Filter orders if specified
                                $filter = $_GET['filter'] ?? '';
                                if ($filter && $order['status'] != $filter) continue;
                            ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($order['username'] ?? 'Guest'); ?></td>
                                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                <td><?php echo $order['quantity']; ?></td>
                                <td>₱<?php echo number_format($order['total_price'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $order['status']; ?>">
                                        <?php 
                                        $status_display = [
                                            'pending_approval' => 'Pending Approval',
                                            'approved' => 'Approved - Awaiting Payment',
                                            'payment_pending' => 'Payment Verification',
                                            'confirmed' => 'Confirmed',
                                            'preparing' => 'Preparing',
                                            'ready' => 'Ready for Pickup',
                                            'completed' => 'Completed',
                                            'cancelled' => 'Cancelled'
                                        ];
                                        echo $status_display[$order['status']] ?? ucfirst($order['status']);
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order['id']; ?>">
                                            <i class="bi bi-pencil"></i> Update
                                        </button>
                                        <a href="?delete_order=<?php echo $order['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this order?')">
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
                                            <h5 class="modal-title">Manage Order #<?php echo $order['id']; ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Order Information</h6>
                                                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username'] ?? 'Guest'); ?></p>
                                                    <p><strong>Product:</strong> <?php echo htmlspecialchars($order['product_name']); ?></p>
                                                    <p><strong>Quantity:</strong> <?php echo $order['quantity']; ?></p>
                                                    <p><strong>Total Amount:</strong> ₱<?php echo number_format($order['total_price'], 2); ?></p>
                                                    <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                                                    <p><strong>Payment Method:</strong> <?php echo $order['payment_method'] ? htmlspecialchars($order['payment_method']) : 'Not specified'; ?></p>
                                                    
                                                    <!-- Payment Proof Section -->
                                                    <?php if (!empty($order['payment_screenshot'])): ?>
                                                    <div class="mt-3">
                                                        <h6>Payment Proof</h6>
                                                        <div class="border rounded p-3 bg-light">
                                                            <p class="text-muted mb-2"><small>Customer's payment proof:</small></p>
                                                            <img src="<?php echo htmlspecialchars($order['payment_screenshot']); ?>" 
                                                                 alt="Payment Proof" 
                                                                 class="img-fluid rounded" 
                                                                 style="max-height: 200px; cursor: pointer;" 
                                                                 onclick="openImageModal('<?php echo htmlspecialchars($order['payment_screenshot']); ?>')">
                                                            <div class="mt-2">
                                                                <small class="text-muted">Click image to view larger</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php elseif ($order['status'] == 'payment_pending'): ?>
                                                    <div class="mt-3">
                                                        <div class="alert alert-warning">
                                                            <i class="bi bi-exclamation-triangle"></i>
                                                            <strong>Payment proof not yet submitted</strong>
                                                            <p class="mb-0 mt-1"><small>Customer needs to submit payment proof before you can confirm payment.</small></p>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Order Status Management</h6>
                                                    <form method="POST">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label"><strong>Update Order Status:</strong></label>
                                                            <select name="status" class="form-select" required>
                                                                <?php if ($order['status'] == 'pending_approval'): ?>
                                                                    <option value="approved">✅ Approve Order</option>
                                                                    <option value="cancelled">❌ Cancel Order</option>
                                                                <?php elseif ($order['status'] == 'approved'): ?>
                                                                    <option value="payment_pending">⏳ Awaiting Payment</option>
                                                                <?php elseif ($order['status'] == 'payment_pending'): ?>
                                                                    <option value="confirmed" <?php echo !empty($order['payment_screenshot']) ? '' : 'disabled'; ?>>
                                                                        ✅ Confirm Payment <?php echo !empty($order['payment_screenshot']) ? '' : '(No Proof)'; ?>
                                                                    </option>
                                                                <?php elseif ($order['status'] == 'confirmed'): ?>
                                                                    <option value="preparing">👨‍🍳 Start Preparing</option>
                                                                <?php elseif ($order['status'] == 'preparing'): ?>
                                                                    <option value="ready">✅ Mark as Ready</option>
                                                                <?php elseif ($order['status'] == 'ready'): ?>
                                                                    <option value="completed">🎉 Complete Order</option>
                                                                <?php else: ?>
                                                                    <option value="<?php echo $order['status']; ?>" selected>Current: <?php echo $status_display[$order['status']] ?? $order['status']; ?></option>
                                                                <?php endif; ?>
                                                            </select>
                                                            <div class="form-text mt-2">
                                                                <?php if ($order['status'] == 'pending_approval'): ?>
                                                                    <span class="text-warning">⚠️ Customer is waiting for approval to proceed with payment</span>
                                                                <?php elseif ($order['status'] == 'payment_pending' && empty($order['payment_screenshot'])): ?>
                                                                    <span class="text-danger">❌ Waiting for customer to submit payment proof</span>
                                                                <?php elseif ($order['status'] == 'payment_pending' && !empty($order['payment_screenshot'])): ?>
                                                                    <span class="text-success">✅ Payment proof submitted - ready for verification</span>
                                                                <?php elseif ($order['status'] == 'preparing'): ?>
                                                                    <span class="text-info">ℹ️ Customer can see their order is being prepared</span>
                                                                <?php elseif ($order['status'] == 'ready'): ?>
                                                                    <span class="text-success">✅ Order is ready for customer pickup</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <button type="submit" name="update_status" class="btn btn-success w-100" 
                                                            <?php echo ($order['status'] == 'payment_pending' && empty($order['payment_screenshot'])) ? 'disabled' : ''; ?>>
                                                            <i class="bi bi-check-circle me-2"></i>
                                                            <?php echo ($order['status'] == 'payment_pending' && empty($order['payment_screenshot'])) ? 'Waiting for Payment Proof' : 'Update Status'; ?>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
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
                                    <a href="admin_dashboard.php?section=orders" class="btn btn-view">View All Orders</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php elseif ($current_section == 'products'): ?>
        <!-- Products Analytics -->
        <h3 class="section-title">Product Performance</h3>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Total Orders</th>
                            <th>Total Quantity</th>
                            <th>Total Revenue</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products_result->num_rows > 0): ?>
                            <?php while ($product = $products_result->fetch_assoc()): 
                                $performance = $product['total_revenue'] > 1000 ? 'High' : ($product['total_revenue'] > 500 ? 'Medium' : 'Low');
                                $performance_class = $performance == 'High' ? 'success' : ($performance == 'Medium' ? 'warning' : 'secondary');
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($product['product_name']); ?></strong></td>
                                <td><span class="badge bg-light text-dark"><?php echo ucfirst($product['category']); ?></span></td>
                                <td><?php echo $product['order_count']; ?></td>
                                <td><?php echo $product['total_quantity']; ?></td>
                                <td><strong>₱<?php echo number_format($product['total_revenue'], 2); ?></strong></td>
                                <td>
                                    <span class="badge bg-<?php echo $performance_class; ?>">
                                        <?php echo $performance; ?> Performance
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-cup fs-1 text-muted"></i>
                                    <p class="mt-2">No product data available</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Image Modal for Payment Proof -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Proof</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Payment Proof" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function openImageModal(imageSrc) {
        document.getElementById('modalImage').src = imageSrc;
        new bootstrap.Modal(document.getElementById('imageModal')).show();
    }
    </script>
</body>
</html>