<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$productId = intval($input['product_id'] ?? 0);

if (!$productId) {
    echo json_encode(['success' => false, 'message' => 'Invalid product selection']);
    exit;
}

if (removeFromCart($productId)) {
    echo json_encode(['success' => true, 'message' => 'Item removed from cart', 'cartCount' => getCartCount()]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unable to remove item']);
