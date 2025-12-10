<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $product_id = $_POST['product_id'];

  // Deduct stock by 1
  $sql = "UPDATE products SET stock = stock - 1 WHERE id = $product_id AND stock > 0";
  $conn->query($sql);
  
  echo "<script>alert('Item added to cart successfully!'); window.location='index.php';</script>";
}
?>