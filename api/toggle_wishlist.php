<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to manage your wishlist', 'loginRequired' => true]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$productId = intval($input['product_id'] ?? 0);

if (!$productId) {
    echo json_encode(['success' => false, 'message' => 'Invalid product selection']);
    exit;
}

$wishlisted = isInWishlist($productId);

if ($wishlisted) {
    if (removeFromWishlist($productId)) {
        echo json_encode([
            'success' => true,
            'message' => 'Removed from wishlist',
            'wishlistCount' => getWishlistCount(),
            'wishlisted' => false
        ]);
        exit;
    }
} else {
    if (addToWishlist($productId)) {
        echo json_encode([
            'success' => true,
            'message' => 'Added to wishlist!',
            'wishlistCount' => getWishlistCount(),
            'wishlisted' => true
        ]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Unable to update wishlist']);
