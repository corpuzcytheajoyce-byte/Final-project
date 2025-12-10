<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: auth.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $user_id = $_SESSION['user_id'];
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $payment_method = $_POST['payment_method'];
    $status = "Preparing";
    $order_time = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO orders (user_id, product_name, category, price, quantity, payment_method, status, order_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdisis", $user_id, $product_name, $category, $price, $quantity, $payment_method, $status, $order_time);
    $stmt->execute();
    $stmt->close();

    header("Location: order_history.php");
    exit();
}
?>
