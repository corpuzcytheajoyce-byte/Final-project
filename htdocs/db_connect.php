<?php
$host = "sql100.infinityfree.com";
$user = "if0_40389391"; // default XAMPP username
$pass = "rekU128iOgfmf";
$db = "if0_40389391_coffee_shop";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>