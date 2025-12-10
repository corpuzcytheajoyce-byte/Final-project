<?php
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';

// Handle admin login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Admin credentials (in production, use database and password hashing)
    $admin_username = 'owner';
    $admin_password = 'owner123';
    
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid admin credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | The Other Side Cafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6f4e37, #8b6b4f);
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 400px;
            margin: 0 auto;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .card-header {
            background-color: #6f4e37;
            color: white;
            border-radius: 15px 15px 0 0 !important;
            text-align: center;
            padding: 30px;
        }
        .btn-admin {
            background-color: #6f4e37;
            color: white;
            border: none;
            padding: 12px;
        }
        .btn-admin:hover {
            background-color: #5a3c29;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">☕ Admin Panel</h2>
                    <p class="mb-0 mt-2">The Other Side Cafe</p>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required placeholder="Enter username">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="Enter password">
                        </div>
                        <button type="submit" name="login" class="btn btn-admin w-100">Admin Login</button>
                    </form>
                    
                    <div class="text-center mt-3">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>