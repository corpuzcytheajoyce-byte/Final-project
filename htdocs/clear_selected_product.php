<?php
session_start();

// Clear the selected product from session
if (isset($_SESSION['selected_product'])) {
    unset($_SESSION['selected_product']);
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
?>