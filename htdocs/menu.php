<?php
session_start();
include 'db_connect.php';

// Check if owner user is logged in and redirect to admin dashboard
if (isset($_SESSION['user_logged_in']) && isset($_SESSION['username']) && $_SESSION['username'] === 'owner') {
    header("Location: admin_dashboard.php");
    exit();
}

// Check if current order still exists in database
if (isset($_SESSION['current_order_id'])) {
    $check_order = $conn->prepare("SELECT id FROM orders WHERE id = ?");
    $check_order->bind_param("i", $_SESSION['current_order_id']);
    $check_order->execute();
    $check_order->store_result();
    
    if ($check_order->num_rows === 0) {
        // Order was deleted by admin, clear session
        unset($_SESSION['current_order_id']);
        unset($_SESSION['current_order_number']);
    }
    $check_order->close();
}

// DATABASE FUNCTION FOR ORDER PROCESSING
function saveOrderToDatabase($product_name, $price, $quantity, $total, $category) {
    global $conn;
    $user_id = $_SESSION['user_id'];
    
    try {
        $sql = "INSERT INTO orders (user_id, product_name, quantity, price, total_price, category, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending_approval')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isiids", $user_id, $product_name, $quantity, $price, $total, $category);
        
        if ($stmt->execute()) {
            return $conn->insert_id;
        } else {
            error_log("Failed to save order: " . $conn->error);
            return false;
        }
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $product_name = $_POST['product_name'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    $category = $_POST['category'] ?? 'drink';
    $total = $price * $quantity;
    
    if (!empty($product_name)) {
        $order_id = saveOrderToDatabase($product_name, $price, $quantity, $total, $category);
        if ($order_id) {
            $_SESSION['current_order_id'] = $order_id;
            $_SESSION['current_order_number'] = 'ORD' . date('Ymd') . $order_id;
            header("Location: order_status.php?order_id=" . $order_id);
            exit();
        } else {
            echo "<div class='alert alert-danger'>Failed to save order. Please try again.</div>";
        }
    }
}

// REMOVED LOGIN REQUIREMENT - anyone can view menu
// Drink categories with actual image URLs from your uploaded images
$drink_categories = [
    'Coffee' => [
        ["name" => "Barista's Drink", "price" => 75, "image" => "Drinks/barista's drink.jpeg", "category" => "drink"],
        ["name" => "Caramel Macchiato", "price" => 75, "image" => "Drinks/caramel macchiato.jpeg", "category" => "drink"],
        ["name" => "White Chocolate Mocha", "price" => 75, "image" => "Drinks/white choco mocha.jpeg", "category" => "drink"],
        ["name" => "Iced Mocha", "price" => 70, "image" => "Drinks/ice mocha latte.jpg", "category" => "drink"],
        ["name" => "Caramel Latte", "price" => 70, "image" => "Drinks/caramel latte.jpeg", "category" => "drink"],
        ["name" => "Hazelnut Latte", "price" => 75, "image" => "Drinks/hazelnut latte.jpg", "category" => "drink"],
        ["name" => "Iced Latte", "price" => 75, "image" => "Drinks/iced latte.jpeg", "category" => "drink"],
        ["name" => "Spanish Latte", "price" => 80, "image" => "Drinks/spanish latte.jpeg", "category" => "drink"],
        ["name" => "Chocolate Spanish Latte", "price" => 65, "image" => "Drinks/chocolate spanish latte.jpeg", "category" => "drink"],
        ["name" => "Cappuccino", "price" => 65, "image" => "Drinks/cappuccino.jpeg", "category" => "drink"],
        ["name" => "Americano", "price" => 65, "image" => "Drinks/americano.jpeg", "category" => "drink"]
    ],
    'Non-Coffee' => [
        ["name" => "Vanilla Milk", "price" => 65, "image" => "Drinks/vanilla milk.jpg", "category" => "drink"],
        ["name" => "Strawberry Milk", "price" => 65, "image" => "Drinks/strawberry milk.jpg", "category" => "drink"],
        ["name" => "Signature Chocolate", "price" => 75, "image" => "Drinks/signature chocolate.jpeg", "category" => "drink"]
    ],
    'Matcha Series' => [
        ["name" => "Matcha Latte", "price" => 80, "image" => "Drinks/matcha latte.jpeg", "category" => "drink"],
        ["name" => "Matcha Creamy Latte", "price" => 80, "image" => "Drinks/matcha creamy latte.jpeg", "category" => "drink"],
        ["name" => "Matcha Caramel", "price" => 80, "image" => "Drinks/matcha caramel.jpeg", "category" => "drink"],
        ["name" => "Matcha Berry", "price" => 80, "image" => "Drinks/matcha berry.jpeg", "category" => "drink"],
        ["name" => "Forest Matcha Oreo", "price" => 80, "image" => "Drinks/forest matcha oreo.jpeg", "category" => "drink"],
        ["name" => "Matcha Coffee", "price" => 80, "image" => "Drinks/matcha coffee.jpeg", "category" => "drink"]
    ],
    'Mocktails' => [
        ["name" => "Blueberry", "price" => 35, "image" => "Drinks/blueberry.jpeg", "category" => "drink"],
        ["name" => "Strawberry", "price" => 35, "image" => "Drinks/strawberry.jpeg", "category" => "drink"],
        ["name" => "Green Apple", "price" => 35, "image" => "Drinks/green apple.jpeg", "category" => "drink"],
        ["name" => "Lemonade", "price" => 35, "image" => "Drinks/lemonade.jpeg", "category" => "drink"],
        ["name" => "Lychee", "price" => 35, "image" => "Drinks/lychee.jpeg", "category" => "drink"]
    ]
];

// Snack items (your existing snacks)
$snacks = [
    ["name" => "Nachos", "price" => 39, "image" => "https://w7.pngwing.com/pngs/617/606/png-transparent-nachos-with-cheese-nachos-chile-con-queso-taco-salsa-tostada-french-fries-food-recipe-cheese-thumbnail.png", "category" => "pastry"],
    ["name" => "Waffle", "price" => 30, "image" => "https://w7.pngwing.com/pngs/559/5/png-transparent-belgian-waffle-chicken-and-waffles-corn-dog-belgian-cuisine-waffles-thumbnail.png", "category" => "pastry"],
    ["name" => "Churros", "price" => 39, "image" => "https://w7.pngwing.com/pngs/679/451/png-transparent-churro-mexican-cuisine-spanish-cuisine-street-food-fast-food-churro-food-baking-recipe-thumbnail.png", "category" => "pastry"],
    ["name" => "Apple / Banana Cake", "price" => 30, "image" => "https://w7.pngwing.com/pngs/186/667/png-transparent-banana-bread-loaf-pumpkin-bread-bara-brith-pineapple-slice-baked-goods-baking-whole-grain-thumbnail.png", "category" => "pastry"],
    ["name" => "Siomai (4 pcs)", "price" => 25, "image" => "https://w7.pngwing.com/pngs/134/168/png-transparent-shumai-dim-sum-recipe-pork-siomai-food-recipe-cuisine-thumbnail.png", "category" => "pastry"],
    ["name" => "Cookies", "price" => 25, "image" => "https://scontent.fdvo6-1.fna.fbcdn.net/v/t39.30808-6/577878611_122104549053093797_683314773288821584_n.jpg?stp=cp6_dst-jpg_tt6&_nc_cat=101&ccb=1-7&_nc_sid=833d8c&_nc_ohc=6JjjOravTB8Q7kNvwGTcRpw&_nc_oc=AdnLq-WMHC0TlPWx2Rac03h3RTL8Wlw2PuMqqRp4_KhzMdZKy4LfOHmd-5K9RzmVQvw&_nc_zt=23&_nc_ht=scontent.fdvo6-1.fna&_nc_gid=GBPf_Gtp-XcRIdqbPLkt2w&oh=00_AfiVmjwAkDsGtpTFnmd0zlc-SVALI0U3xCTUH_2p9gYHvg&oe=6921DBBB", "category" => "pastry"],
    ["name" => "Overload Fries", "price" => 49, "image" => "https://w7.pngwing.com/pngs/94/282/png-transparent-french-fries-fried-chicken-potato-chip-frying-fried-chicken-food-recipe-cooking-thumbnail.png", "category" => "pastry"],
    ["name" => "Cheesy Fries", "price" => 45, "image" => "https://w7.pngwing.com/pngs/580/193/png-transparent-french-fries-with-cheese-french-fries-cheese-fries-chili-con-carne-hamburger-food-fries-cheese-recipe-american-food-thumbnail.png", "category" => "pastry"],
    ["name" => "Regular Fries", "price" => 29, "image" => "https://w7.pngwing.com/pngs/363/16/png-transparent-fried-fries-illustration-hamburger-french-fries-fast-food-onion-ring-fried-chicken-hd-fries-food-cooking-american-food-thumbnail.png", "category" => "pastry"],
    ["name" => "Croissant", "price" => 35, "image" => "https://w7.pngwing.com/pngs/979/375/png-transparent-croissant-french-cuisine-bagel-pain-au-chocolat-breakfast-croissant-baked-goods-food-breakfast-thumbnail.png", "category" => "pastry"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Menu ☕ | The other Side Cafe</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { background-color: #f9f6f1; font-family: 'Poppins', sans-serif; }
    .navbar { background-color: #6f4e37 !important; }
    .navbar-brand, .nav-link { color: #fff !important; font-weight: 500; }
    .navbar-brand:hover, .nav-link:hover { color: #f3e5ab !important; }
    .card { border: none; background-color: #fff8f0; transition: 0.3s; }
    .card:hover { transform: translateY(-5px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .btn-coffee { background-color: #6f4e37; color: white; }
    .btn-coffee:hover { background-color: #5a3c29; }
    .btn-warning { background-color: #ffc107; color: black; border: none; }
    .btn-warning:hover { background-color: #e0a800; }
    .btn-success { background-color: #28a745; color: white; border: none; }
    .btn-success:hover { background-color: #218838; }
    .btn-login { background-color: #c69c6d; color: white; border: none; }
    .btn-login:hover { background-color: #b08962; }
    footer { text-align: center; padding: 20px; background-color: #6f4e37; color: white; margin-top: 40px; }
    .category-title { 
        border-bottom: 3px solid #6f4e37; 
        padding-bottom: 10px; 
        margin: 40px 0 20px 0;
        color: #6f4e37;
    }
    .login-prompt { 
        background-color: #fff3cd; 
        border: 1px solid #ffeaa7; 
        border-radius: 5px; 
        padding: 10px; 
        margin-top: 10px;
        text-align: center;
    }
    .order-alert {
        background: linear-gradient(135deg, #fff3cd, #ffeaa7);
        border: 2px solid #ffc107;
        border-radius: 10px;
        padding: 15px;
        margin: 10px 0;
    }
    
    /* Login Modal Styles */
    .modal-content {
        background-color: #fff8f0;
        border: none;
        border-radius: 10px;
    }
    .modal-header {
        background-color: #6f4e37;
        color: white;
        border-bottom: none;
        border-radius: 10px 10px 0 0;
    }
    .modal-header .btn-close {
        filter: invert(1);
    }
    .nav-tabs .nav-link.active {
        background-color: #6f4e37;
        color: white;
        border: none;
    }
    .nav-tabs .nav-link {
        color: #6f4e37;
        border: none;
    }
    .nav-tabs {
        border-bottom: 2px solid #6f4e37;
    }
    .form-control:focus {
        border-color: #6f4e37;
        box-shadow: 0 0 0 0.2rem rgba(111, 78, 55, 0.25);
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">☕ The other Side Cafe</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link active" href="menu.php">Menu</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
        <?php if (isset($_SESSION['user_logged_in'])): ?>
          <li class="nav-item"><a class="nav-link" href="#">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Login / Sign Up</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- MENU CONTENT -->
<div class="container my-5">
  <h2 class="text-center mb-4 fw-bold">Our Coffee & Snacks Menu</h2>
  
  <?php if (!isset($_SESSION['user_logged_in'])): ?>
    <div class="alert alert-info text-center">
      <strong>👋 Welcome!</strong> Browse our menu freely. Login is only required when you're ready to order.
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['current_order_id'])): ?>
    <div class="order-alert text-center">
      <h5><i class="bi bi-clock-history"></i> Order Submitted!</h5>
      <p class="mb-2">Your order #<?php echo $_SESSION['current_order_number']; ?> is waiting for admin approval.</p>
      <a href="order_status.php?order_id=<?php echo $_SESSION['current_order_id']; ?>" class="btn btn-warning btn-sm">
        Check Order Status
      </a>
    </div>
  <?php endif; ?>
  
  <!-- DRINKS CATEGORIES -->
  <?php foreach ($drink_categories as $category_name => $drinks): ?>
    <h3 class="category-title"><?php echo $category_name; ?></h3>
    <div class="row">
      <?php foreach ($drinks as $index => $drink): ?>
        <div class="col-md-4 mb-4">
          <div class="card text-center">
            <img src="<?php echo $drink['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($drink['name']); ?>" style="height:250px; object-fit:cover;">
            <div class="card-body">
              <h5 class="card-title"><?php echo htmlspecialchars($drink['name']); ?></h5>
              <p class="text-muted">₱<?php echo number_format($drink['price'], 2); ?></p>
              
              <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                <!-- LOGGED IN USER: Show order form -->
                <form method="POST" class="order-form">
                  <input type="hidden" name="place_order" value="1">
                  <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($drink['name']); ?>">
                  <input type="hidden" name="price" value="<?php echo $drink['price']; ?>">
                  <input type="hidden" name="category" value="<?php echo $drink['category']; ?>">
                  
                  <div class="mb-3">
                    <label class="form-label">Quantity:</label>
                    <input type="number" name="quantity" class="form-control text-center" value="1" min="1" required>
                  </div>

                  <div class="order-summary bg-light p-3 rounded mb-3">
                    <p class="fw-bold mb-2">Order Summary:</p>
                    <p class="m-0">Item: <?php echo htmlspecialchars($drink['name']); ?></p>
                    <p class="m-0">Price: ₱<span class="summary-price"><?php echo number_format($drink['price'], 2); ?></span></p>
                    <p class="m-0">Quantity: <span class="summary-qty">1</span></p>
                    <p class="m-0 fw-bold">Total: ₱<span class="summary-total"><?php echo number_format($drink['price'], 2); ?></span></p>
                  </div>

                  <button type="submit" class="btn btn-warning w-100">
                    <i class="bi bi-send-check"></i> Submit Order for Approval
                  </button>
                  <small class="text-muted d-block mt-2">Admin must approve your order before you can proceed to payment</small>
                </form>
              <?php else: ?>
                <!-- NOT LOGGED IN: Show login prompt -->
                <button type="button" class="btn btn-login w-100 open-login-modal" 
                        data-product-name="<?php echo htmlspecialchars($drink['name']); ?>"
                        data-product-price="<?php echo $drink['price']; ?>"
                        data-product-category="<?php echo $drink['category']; ?>">
                  <i class="bi bi-lock"></i> Login to Order
                </button>
                <div class="login-prompt">
                  <small class="text-muted">Login required to place orders</small>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>

  <!-- SNACKS SECTION -->
  <h3 class="category-title">Snacks & Pastries</h3>
  <div class="row">
    <?php foreach ($snacks as $index => $snack): ?>
      <div class="col-md-4 mb-4">
        <div class="card text-center">
          <img src="<?php echo $snack['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($snack['name']); ?>" style="height:250px; object-fit:cover;">
          <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($snack['name']); ?></h5>
            <p class="text-muted">₱<?php echo number_format($snack['price'], 2); ?></p>
            
            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
              <!-- LOGGED IN USER: Show order form -->
              <form method="POST" class="order-form">
                <input type="hidden" name="place_order" value="1">
                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($snack['name']); ?>">
                <input type="hidden" name="price" value="<?php echo $snack['price']; ?>">
                <input type="hidden" name="category" value="<?php echo $snack['category']; ?>">
                
                <div class="mb-3">
                  <label class="form-label">Quantity:</label>
                  <input type="number" name="quantity" class="form-control text-center" value="1" min="1" required>
                </div>

                <div class="order-summary bg-light p-3 rounded mb-3">
                  <p class="fw-bold mb-2">Order Summary:</p>
                  <p class="m-0">Item: <?php echo htmlspecialchars($snack['name']); ?></p>
                  <p class="m-0">Price: ₱<span class="summary-price"><?php echo number_format($snack['price'], 2); ?></span></p>
                  <p class="m-0">Quantity: <span class="summary-qty">1</span></p>
                  <p class="m-0 fw-bold">Total: ₱<span class="summary-total"><?php echo number_format($snack['price'], 2); ?></span></p>
                </div>

                <button type="submit" class="btn btn-warning w-100">
                  <i class="bi bi-send-check"></i> Submit Order for Approval
                </button>
                <small class="text-muted d-block mt-2">Admin must approve your order before you can proceed to payment</small>
              </form>
            <?php else: ?>
              <!-- NOT LOGGED IN: Show login prompt -->
              <button type="button" class="btn btn-login w-100 open-login-modal"
                      data-product-name="<?php echo htmlspecialchars($snack['name']); ?>"
                      data-product-price="<?php echo $snack['price']; ?>"
                      data-product-category="<?php echo $snack['category']; ?>">
                <i class="bi bi-lock"></i> Login to Order
              </button>
              <div class="login-prompt">
                <small class="text-muted">Login required to place orders</small>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- LOGIN MODAL -->
<div class="modal fade" id="loginModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">☕ Login to Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-4">
          <p class="text-muted">Login or create an account to proceed with your order</p>
        </div>

        <ul class="nav nav-tabs nav-justified mb-4" id="authTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="modal-login-tab" data-bs-toggle="tab" data-bs-target="#modal-login" type="button" role="tab">Login</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="modal-signup-tab" data-bs-toggle="tab" data-bs-target="#modal-signup" type="button" role="tab">Sign Up</button>
          </li>
        </ul>

        <div class="tab-content" id="authTabsContent">
          <!-- Login Tab -->
          <div class="tab-pane fade show active" id="modal-login" role="tabpanel">
            <form id="modalLoginForm" method="POST" action="login_handler.php">
              <input type="hidden" name="login" value="1">
              <input type="hidden" name="payment_flow" value="true">
              <input type="hidden" name="product_name" id="login_product_name">
              <input type="hidden" name="product_price" id="login_product_price">
              <input type="hidden" name="product_category" id="login_product_category">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-coffee w-100">Login & Continue to Menu</button>
            </form>
          </div>

          <!-- Sign Up Tab -->
          <div class="tab-pane fade" id="modal-signup" role="tabpanel">
            <form id="modalSignupForm" method="POST" action="login_handler.php">
              <input type="hidden" name="signup" value="1">
              <input type="hidden" name="payment_flow" value="true">
              <input type="hidden" name="product_name" id="signup_product_name">
              <input type="hidden" name="product_price" id="signup_product_price">
              <input type="hidden" name="product_category" id="signup_product_category">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-coffee w-100">Sign Up & Continue to Menu</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<footer>
  <div class="container">
    <p class="mb-2">© 2025 The other Side Cafe | Online Ordering System</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));

  // Login Modal Handling
  document.querySelectorAll('.open-login-modal').forEach(button => {
    button.addEventListener('click', function() {
      const productName = this.getAttribute('data-product-name');
      const productPrice = this.getAttribute('data-product-price');
      const productCategory = this.getAttribute('data-product-category');
      
      document.getElementById('login_product_name').value = productName;
      document.getElementById('login_product_price').value = productPrice;
      document.getElementById('login_product_category').value = productCategory;
      
      document.getElementById('signup_product_name').value = productName;
      document.getElementById('signup_product_price').value = productPrice;
      document.getElementById('signup_product_category').value = productCategory;
      
      loginModal.show();
    });
  });

  // Update order summary when quantity changes
  document.querySelectorAll('input[name="quantity"]').forEach(input => {
    input.addEventListener('input', function() {
      const form = this.closest('form');
      const price = parseFloat(form.querySelector('input[name="price"]').value);
      const quantity = parseInt(this.value) || 1;
      const total = price * quantity;
      
      form.querySelector('.summary-qty').textContent = quantity;
      form.querySelector('.summary-total').textContent = total.toFixed(2);
    });
  });
});
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>