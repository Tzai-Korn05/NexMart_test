<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$cartItems = getCartItems();
$output = [];

foreach ($cartItems as $item) {
    $output[] = [
        'id' => $item['id'],
        'name' => $item['name'],
        'image' => $item['image'] ?? 'placeholder.jpg',
        'price' => $item['price'],
        'quantity' => $item['quantity'],
        'subtotal' => $item['price'] * $item['quantity']
    ];
}

echo json_encode($output);
