<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $product_name = $input['product_name'];
    $quantity = $input['quantity'];
    $total = $input['total'];
    $customer_name = $input['customer_name'];
    $customer_id = $input['customer_id'];
    
    // Insert notification into database
    $stmt = $conn->prepare("INSERT INTO order_notifications (customer_id, customer_name, product_name, quantity, total_amount, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("issid", $customer_id, $customer_name, $product_name, $quantity, $total);
    
    if ($stmt->execute()) {
        // You can also send email notification here
        sendEmailNotification($customer_name, $product_name, $quantity, $total);
        
        echo json_encode(['success' => true, 'message' => 'Owner notified successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to notify owner']);
    }
    
    $stmt->close();
}

function sendEmailNotification($customer_name, $product_name, $quantity, $total) {
    $to = "owner@theothersidecafe.com"; // Owner's email
    $subject = "New Payment Notification - The Other Side Cafe";
    $message = "
    <h2>New Payment Received!</h2>
    <p><strong>Customer:</strong> $customer_name</p>
    <p><strong>Product:</strong> $product_name</p>
    <p><strong>Quantity:</strong> $quantity</p>
    <p><strong>Total Amount:</strong> ₱$total</p>
    <p><strong>Payment Time:</strong> " . date('Y-m-d H:i:s') . "</p>
    <br>
    <p>Please check the admin panel for more details.</p>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: notifications@theothersidecafe.com" . "\r\n";
    
    mail($to, $subject, $message, $headers);
}
?>