<?php
session_start();
include 'db_connect.php';

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, category) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $name, $description, $price, $category);
        $stmt->execute();
    }
    
    if (isset($_POST['update_product'])) {
        $id = $_POST['product_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category=?, is_available=? WHERE id=?");
        $stmt->bind_param("ssdsii", $name, $description, $price, $category, $is_available, $id);
        $stmt->execute();
    }
}

// Handle product deletion
if (isset($_GET['delete_product'])) {
    $id = $_GET['delete_product'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

$products_result = $conn->query("SELECT * FROM products ORDER BY category, name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management | The Other Side Cafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .sidebar { background: linear-gradient(135deg, #6f4e37, #8b6b4f); color: white; height: 100vh; position: fixed; padding-top: 20px; width: 250px; }
        .sidebar .nav-link { color: white; padding: 15px 20px; margin: 5px 0; border-radius: 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.1); }
        .main-content { margin-left: 250px; padding: 20px; }
        .table-container { background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="text-center mb-4">
            <h4>☕ Admin Panel</h4>
            <p class="small">The Other Side Cafe</p>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link" href="admin_dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <a class="nav-link" href="admin_orders.php"><i class="bi bi-cart me-2"></i>Orders</a>
            <a class="nav-link active" href="admin_products.php"><i class="bi bi-cup-hot me-2"></i>Products</a>
            <a class="nav-link" href="admin_customers.php"><i class="bi bi-people me-2"></i>Customers</a>
            <a class="nav-link" href="admin_reports.php"><i class="bi bi-graph-up me-2"></i>Reports</a>
            <a class="nav-link" href="admin_settings.php"><i class="bi bi-gear me-2"></i>Settings</a>
            <a class="nav-link" href="admin_logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Product Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-circle"></i> Add Product
            </button>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $products_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['description']); ?></td>
                            <td>₱<?php echo number_format($product['price'], 2); ?></td>
                            <td><span class="badge bg-secondary"><?php echo ucfirst($product['category']); ?></span></td>
                            <td>
                                <?php if ($product['is_available']): ?>
                                    <span class="badge bg-success">Available</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Unavailable</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProductModal<?php echo $product['id']; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="?delete_product=<?php echo $product['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this product?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Product Modal -->
                        <div class="modal fade" id="editProductModal<?php echo $product['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Product</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Product Name</label>
                                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Price</label>
                                                <input type="number" name="price" step="0.01" class="form-control" value="<?php echo $product['price']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Category</label>
                                                <select name="category" class="form-select" required>
                                                    <option value="drink" <?php echo $product['category'] == 'drink' ? 'selected' : ''; ?>>Drink</option>
                                                    <option value="pastry" <?php echo $product['category'] == 'pastry' ? 'selected' : ''; ?>>Pastry</option>
                                                    <option value="meal" <?php echo $product['category'] == 'meal' ? 'selected' : ''; ?>>Meal</option>
                                                </select>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_available" id="available<?php echo $product['id']; ?>" <?php echo $product['is_available'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="available<?php echo $product['id']; ?>">
                                                    Available for ordering
                                                </label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" name="price" step="0.01" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select" required>
                                <option value="drink">Drink</option>
                                <option value="pastry">Pastry</option>
                                <option value="meal">Meal</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>