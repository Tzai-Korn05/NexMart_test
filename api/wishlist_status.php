<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$productId = intval($_GET['product_id'] ?? 0);

if (!$productId || !isLoggedIn()) {
    echo json_encode(['success' => false, 'wishlisted' => false]);
    exit;
}

echo json_encode([
    'success' => true,
    'wishlisted' => isInWishlist($productId)
]);
