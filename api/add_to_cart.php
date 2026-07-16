<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$productId = intval($input['product_id'] ?? 0);
$quantity = intval($input['quantity'] ?? 1);

if (!$productId || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product selection']);
    exit;
}

if (addToCart($productId, $quantity)) {
    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart!',
        'cartCount' => getCartCount()
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unable to add product to cart']);
