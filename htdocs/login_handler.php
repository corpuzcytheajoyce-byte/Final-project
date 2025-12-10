<?php
session_start();
include 'db_connect.php';

// Handle login form submission from modal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['login'])) {
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
            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_logged_in'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['id'];
                
                // Store product info in session to pre-fill the form after login
                if ($payment_flow && isset($_POST['product_name'])) {
                    $_SESSION['selected_product'] = [
                        'product_name' => $_POST['product_name'],
                        'price' => $_POST['product_price'],
                        'category' => $_POST['product_category']
                    ];
                }
                
                // Always redirect back to menu after login
                header("Location: menu.php");
                exit();
            } else {
                header("Location: menu.php?error=Invalid password");
                exit();
            }
        } else {
            header("Location: menu.php?error=User not found");
            exit();
        }
        $stmt->close();
    }
    
    // Handle sign up
    if (isset($_POST['signup'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $payment_flow = isset($_POST['payment_flow']) && $_POST['payment_flow'] === 'true';
        
        if ($password !== $confirm_password) {
            header("Location: menu.php?error=Passwords do not match");
            exit();
        } else {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                header("Location: menu.php?error=Username already exists");
                exit();
            } else {
                // Create new user in database
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $email = NULL;
                
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $email, $hashed_password);
                
                if ($stmt->execute()) {
                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['username'] = $username;
                    $_SESSION['user_id'] = $stmt->insert_id;
                    
                    // Store product info in session to pre-fill the form after login
                    if ($payment_flow && isset($_POST['product_name'])) {
                        $_SESSION['selected_product'] = [
                            'product_name' => $_POST['product_name'],
                            'price' => $_POST['product_price'],
                            'category' => $_POST['product_category']
                        ];
                    }
                    
                    header("Location: menu.php");
                    exit();
                } else {
                    header("Location: menu.php?error=Registration failed");
                    exit();
                }
            }
            $stmt->close();
        }
    }
}

header("Location: menu.php");
exit();
?>