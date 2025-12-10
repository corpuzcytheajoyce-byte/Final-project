<?php
session_start();
include 'db_connect.php';

// Check if user is already logged in and coming from payment flow
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    if (isset($_SESSION['payment_redirect']) && $_SESSION['payment_redirect'] === true) {
        unset($_SESSION['payment_redirect']);
        header("Location: process_payment.php");
        exit();
    } else {
        header("Location: menu.php");
        exit();
    }
}

$error = '';
$payment_flow = false;

// Check if this is a payment flow redirect
if (isset($_GET['payment']) && $_GET['payment'] === 'true') {
    $payment_flow = true;
    $_SESSION['payment_redirect'] = true;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $payment_flow = isset($_POST['payment_flow']) && $_POST['payment_flow'] === 'true';
    
    // DATABASE AUTHENTICATION
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verify password (assuming passwords are hashed)
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            
            if ($payment_flow) {
                unset($_SESSION['payment_redirect']);
                header("Location: process_payment.php");
            } else {
                header("Location: menu.php");
            }
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
    $stmt->close();
}

// Handle sign up form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $payment_flow = isset($_POST['payment_flow']) && $_POST['payment_flow'] === 'true';
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username already exists! Please choose a different username.";
        } else {
            // Create new user in database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $email = NULL; // Email allows NULL in database
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $_SESSION['user_logged_in'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['user_id'] = $stmt->insert_id;
                
                if ($payment_flow) {
                    unset($_SESSION['payment_redirect']);
                    header("Location: process_payment.php");
                } else {
                    header("Location: menu.php");
                }
                exit();
            } else {
                $error = "Registration failed! Please try again.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $payment_flow ? 'Login to Proceed with Payment' : 'Login'; ?> | The Other Side Cafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f6f1;
            font-family: 'Poppins', sans-serif;
        }
        .auth-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
        }
        .card {
            border: none;
            background-color: #fff8f0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .btn-coffee {
            background-color: #6f4e37;
            color: white;
            border: none;
        }
        .btn-coffee:hover {
            background-color: #5a3c29;
        }
        .nav-tabs .nav-link.active {
            background-color: #6f4e37;
            color: white;
            border: none;
        }
        .nav-tabs .nav-link {
            color: #6f4e37;
        }
        .payment-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="card p-4">
                <h2 class="text-center mb-4">☕ The Other Side Cafe</h2>
                
                <?php if ($payment_flow): ?>
                    <div class="payment-notice">
                        <strong>🛒 Proceed to Payment</strong>
                        <p class="mb-0 small">Please login or sign up to complete your purchase</p>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <ul class="nav nav-tabs nav-justified mb-4" id="authTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">Login</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="signup-tab" data-bs-toggle="tab" data-bs-target="#signup" type="button" role="tab">Sign Up</button>
                    </li>
                </ul>

                <div class="tab-content" id="authTabsContent">
                    <!-- Login Tab -->
                    <div class="tab-pane fade show active" id="login" role="tabpanel">
                        <form method="POST">
                            <input type="hidden" name="login" value="1">
                            <input type="hidden" name="payment_flow" value="<?php echo $payment_flow ? 'true' : 'false'; ?>">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-coffee w-100">
                                <?php echo $payment_flow ? 'Login & Proceed to Payment' : 'Login'; ?>
                            </button>
                        </form>
                        <div class="mt-3 text-center">
                            <small class="text-muted">Try: admin / password</small>
                        </div>
                    </div>

                    <!-- Sign Up Tab -->
                    <div class="tab-pane fade" id="signup" role="tabpanel">
                        <form method="POST">
                            <input type="hidden" name="signup" value="1">
                            <input type="hidden" name="payment_flow" value="<?php echo $payment_flow ? 'true' : 'false'; ?>">
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
                            <button type="submit" class="btn btn-coffee w-100">
                                <?php echo $payment_flow ? 'Sign Up & Proceed to Payment' : 'Sign Up'; ?>
                            </button>
                        </form>
                    </div>
                </div>
                
                <?php if (!$payment_flow): ?>
                    <div class="text-center mt-3">
                        <a href="menu.php" class="btn btn-outline-coffee btn-sm">Continue to Menu</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>