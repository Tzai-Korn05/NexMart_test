<?php
/**
 * Helper Functions File
 * NexMart E-Commerce
 * 
 * Contains reusable helper functions for the application
 */

// Sanitize input data
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit;
    }
}

// Redirect if not admin
function requireAdmin() {
    if (!isLoggedIn()) {
        header('Location: ' . baseUrl('login.php'));
        exit;
    }
    if (!isAdmin()) {
        header('Location: ' . baseUrl('index.php'));
        exit;
    }
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user data
function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([getCurrentUserId()]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

// Format price (Myanmar Kyat)
function formatPrice($price) {
    return number_format($price, 0) . ' Ks';
}

// Calculate discount percentage
function calculateDiscount($price, $comparePrice) {
    if ($comparePrice && $comparePrice > $price) {
        return round((($comparePrice - $price) / $comparePrice) * 100);
    }
    return 0;
}

// Generate slug from string
function generateSlug($string) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    return $slug;
}

// Get the base URL for the current application root
function getBaseUrl() {
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
    $rootDir = realpath(__DIR__ . '/..');
    if (!$docRoot || !$rootDir) {
        return '';
    }

    $basePath = str_replace('\\', '/', str_replace($docRoot, '', $rootDir));
    return rtrim($basePath, '/');
}

function baseUrl($path = '') {
    $base = getBaseUrl();
    $path = ltrim($path, '/');
    if ($base === '') {
        return '/' . $path;
    }
    return $base . '/' . $path;
}

// Upload image
function uploadImage($file, $directory = 'uploads/') {
    $targetDir = __DIR__ . '/../assets/' . $directory;
    
    // Create directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    // Get file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($extension, $allowedExtensions)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    // Check file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File too large'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = $targetDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to upload file'];
}

// Delete image
function deleteImage($filename, $directory = 'uploads/') {
    $filePath = __DIR__ . '/../assets/' . $directory . $filename;
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// Get cart count
function getCartCount() {
    global $pdo;
    if (!isLoggedIn()) {
        return isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM carts WHERE user_id = ?");
        $stmt->execute([getCurrentUserId()]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (PDOException $e) {
        return 0;
    }
}

// Get wishlist count
function getWishlistCount() {
    global $pdo;
    if (!isLoggedIn()) return 0;
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
        $stmt->execute([getCurrentUserId()]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (PDOException $e) {
        return 0;
    }
}

// Add to cart
function addToCart($productId, $quantity = 1) {
    global $pdo;
    
    if (!isLoggedIn()) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
        return true;
    }
    
    try {
        // Check if product exists
        $stmt = $pdo->prepare("SELECT id, quantity FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) return false;
        
        // Check if already in cart
        $stmt = $pdo->prepare("SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ?");
        $stmt->execute([getCurrentUserId(), $productId]);
        $cartItem = $stmt->fetch();
        
        if ($cartItem) {
            $newQuantity = $cartItem['quantity'] + $quantity;
            $stmt = $pdo->prepare("UPDATE carts SET quantity = ? WHERE id = ?");
            $stmt->execute([$newQuantity, $cartItem['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([getCurrentUserId(), $productId, $quantity]);
        }
        
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Remove from cart
function removeFromCart($productId) {
    global $pdo;
    
    if (!isLoggedIn()) {
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }
        return true;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM carts WHERE user_id = ? AND product_id = ?");
        $stmt->execute([getCurrentUserId(), $productId]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Update cart quantity
function updateCartQuantity($productId, $quantity) {
    global $pdo;
    
    if ($quantity <= 0) {
        return removeFromCart($productId);
    }
    
    if (!isLoggedIn()) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = $quantity;
        }
        return true;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE carts SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$quantity, getCurrentUserId(), $productId]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Get cart items
function getCartItems() {
    global $pdo;
    
    if (!isLoggedIn()) {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            return [];
        }
        
        $items = [];
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
                if ($product) {
                    // Normalize field names for consistency
                    $product['cart_quantity'] = $quantity;
                    $product['stock_quantity'] = $product['quantity'];
                    $items[] = $product;
                }
            } catch (PDOException $e) {
                continue;
            }
        }
        return $items;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.quantity as cart_quantity, p.quantity as stock_quantity, c.id as cart_id 
            FROM carts c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ? AND p.status = 'active'
        ");
        $stmt->execute([getCurrentUserId()]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Get cart total
function getCartTotal() {
    $items = getCartItems();
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['cart_quantity'];
    }
    return $total;
}

// Clear cart
function clearCart() {
    global $pdo;
    
    if (!isLoggedIn()) {
        $_SESSION['cart'] = [];
        return true;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM carts WHERE user_id = ?");
        $stmt->execute([getCurrentUserId()]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Add to wishlist
function addToWishlist($productId) {
    global $pdo;
    
    if (!isLoggedIn()) return false;
    
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([getCurrentUserId(), $productId]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Remove from wishlist
function removeFromWishlist($productId) {
    global $pdo;
    
    if (!isLoggedIn()) return false;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([getCurrentUserId(), $productId]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Check if product is in wishlist
function isInWishlist($productId) {
    global $pdo;
    
    if (!isLoggedIn()) return false;
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([getCurrentUserId(), $productId]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        return false;
    }
}

// Get wishlist items
function getWishlistItems() {
    global $pdo;
    
    if (!isLoggedIn()) return [];
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.* 
            FROM wishlist w 
            JOIN products p ON w.product_id = p.id 
            WHERE w.user_id = ? AND p.status = 'active'
        ");
        $stmt->execute([getCurrentUserId()]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Get product rating
function getProductRating($productId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE product_id = ? AND status = 'approved'");
        $stmt->execute([$productId]);
        $result = $stmt->fetch();
        return [
            'rating' => round($result['avg_rating'] ?? 0, 1),
            'count' => $result['count'] ?? 0
        ];
    } catch (PDOException $e) {
        return ['rating' => 0, 'count' => 0];
    }
}

// Generate stars HTML
function generateStars($rating) {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
    
    $html = '';
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<i class="fas fa-star"></i>';
    }
    if ($halfStar) {
        $html .= '<i class="fas fa-star-half-alt"></i>';
    }
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<i class="far fa-star"></i>';
    }
    
    return $html;
}

// Truncate text
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Get featured products
function getFeaturedProducts($limit = 8) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, b.name as brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.is_featured = 1 AND p.status = 'active' 
            ORDER BY p.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Get new products
function getNewProducts($limit = 8) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, b.name as brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.is_new = 1 AND p.status = 'active' 
            ORDER BY p.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Get best selling products
function getBestSellingProducts($limit = 8) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, b.name as brand_name,
                   COALESCE(SUM(CASE WHEN o.status != 'cancelled' THEN oi.quantity ELSE 0 END), 0) as total_sold
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN brands b ON p.brand_id = b.id 
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id
            WHERE p.is_bestseller = 1 AND p.status = 'active'
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Get categories
function getCategories($parentId = null) {
    global $pdo;
    
    try {
        if ($parentId === null) {
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id IS NULL AND status = 'active' ORDER BY sort_order ASC");
            $stmt->execute();
        } else {
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id = ? AND status = 'active' ORDER BY sort_order ASC");
            $stmt->execute([$parentId]);
        }
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Get brands
function getBrands($limit = null) {
    global $pdo;
    
    try {
        if ($limit) {
            $stmt = $pdo->prepare("SELECT * FROM brands WHERE status = 'active' ORDER BY sort_order ASC LIMIT ?");
            $stmt->execute([$limit]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM brands WHERE status = 'active' ORDER BY sort_order ASC");
            $stmt->execute();
        }
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Get product by ID
function getProductById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug, 
                   b.name as brand_name, b.slug as brand_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.id = ? AND p.status = 'active'
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

// Get product by slug
function getProductBySlug($slug) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug, 
                   b.name as brand_name, b.slug as brand_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.slug = ? AND p.status = 'active'
        ");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

// Search products
function searchProducts($query, $categoryId = null, $brandId = null, $minPrice = null, $maxPrice = null, $sortBy = 'created_at', $sortOrder = 'DESC', $limit = 20, $offset = 0) {
    global $pdo;
    
    try {
        $sql = "
            SELECT p.*, c.name as category_name, b.name as brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.status = 'active'
        ";
        $params = [];
        
        if ($query) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
            $searchTerm = "%$query%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($brandId) {
            $sql .= " AND p.brand_id = ?";
            $params[] = $brandId;
        }
        
        if ($minPrice) {
            $sql .= " AND p.price >= ?";
            $params[] = $minPrice;
        }
        
        if ($maxPrice) {
            $sql .= " AND p.price <= ?";
            $params[] = $maxPrice;
        }
        
        // Validate sort column
        $allowedSortColumns = ['created_at', 'price', 'name', 'views'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }
        
        $sql .= " ORDER BY p.$sortBy $sortOrder LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Get total search results count
function getSearchCount($query, $categoryId = null, $brandId = null, $minPrice = null, $maxPrice = null) {
    global $pdo;
    
    try {
        $sql = "SELECT COUNT(*) as count FROM products p WHERE p.status = 'active'";
        $params = [];
        
        if ($query) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
            $searchTerm = "%$query%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($brandId) {
            $sql .= " AND p.brand_id = ?";
            $params[] = $brandId;
        }
        
        if ($minPrice) {
            $sql .= " AND p.price >= ?";
            $params[] = $minPrice;
        }
        
        if ($maxPrice) {
            $sql .= " AND p.price <= ?";
            $params[] = $maxPrice;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (PDOException $e) {
        return 0;
    }
}

// Get related products
function getRelatedProducts($productId, $categoryId, $limit = 4) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, b.name as brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.id != ? AND p.category_id = ? AND p.status = 'active' 
            ORDER BY RAND() 
            LIMIT ?
        ");
        $stmt->execute([$productId, $categoryId, $limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Set flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Get flash message
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Display flash message
function displayFlashMessage() {
    $message = getFlashMessage();
    if ($message) {
        $alertClass = $message['type'] === 'success' ? 'alert-success' : 
                     ($message['type'] === 'error' ? 'alert-danger' : 'alert-info');
        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        echo $message['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

// Generate pagination
function generatePagination($total, $currentPage, $perPage, $url) {
    $totalPages = ceil($total / $perPage);
    
    if ($totalPages <= 1) return '';
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($currentPage - 1) . '">Previous</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=1">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . $totalPages . '">' . $totalPages . '</a></li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($currentPage + 1) . '">Next</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Send email (demo function - replace with actual email service)
function sendEmail($to, $subject, $message) {
    // In production, use PHPMailer or similar
    // This is a placeholder for demo purposes
    $headers = "From: noreply@nexmart.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}

// Generate order number
function generateOrderNumber() {
    return 'NM' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

// Get date in readable format
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

// Time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return formatDate($datetime);
    }
}
?>
