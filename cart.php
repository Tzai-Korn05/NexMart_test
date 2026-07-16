<?php
/**
 * Shopping Cart Page
 * NexMart E-Commerce
 */
$pageTitle = 'Shopping Cart';
$showBreadcrumb = true;
$breadcrumbItems = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Cart', 'url' => '', 'active' => true]
];

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Require login to view cart
requireLogin();

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $productId => $quantity) {
            updateCartQuantity($productId, intval($quantity));
        }
        setFlashMessage('success', 'Cart updated successfully');
        header('Location: cart.php');
        exit;
    }
    
    if (isset($_POST['remove_item'])) {
        removeFromCart(intval($_POST['product_id']));
        setFlashMessage('success', 'Item removed from cart');
        header('Location: cart.php');
        exit;
    }
    
    if (isset($_POST['apply_coupon'])) {
        $couponCode = sanitize($_POST['coupon_code']);
        // Validate coupon (simplified)
        try {
            $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' AND valid_from <= CURDATE() AND valid_until >= CURDATE() LIMIT 1");
            $stmt->execute([$couponCode]);
            $coupon = $stmt->fetch();
            
            if ($coupon) {
                $_SESSION['coupon'] = $coupon;
                setFlashMessage('success', 'Coupon applied successfully!');
            } else {
                setFlashMessage('error', 'Invalid or expired coupon code');
            }
        } catch (PDOException $e) {
            setFlashMessage('error', 'Error applying coupon');
        }
        header('Location: cart.php');
        exit;
    }
}

// Get cart items
$cartItems = getCartItems();
$subtotal = getCartTotal();

// Calculate shipping, tax, and discount
$shippingCost = $subtotal > 50 ? 0 : 9.99;
$taxRate = 0.08; // 8% tax
$tax = $subtotal * $taxRate;
$discount = 0;

if (isset($_SESSION['coupon'])) {
    $coupon = $_SESSION['coupon'];
    if ($coupon['discount_type'] === 'percentage') {
        $discount = $subtotal * ($coupon['discount_value'] / 100);
        if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
            $discount = $coupon['max_discount'];
        }
    } else {
        $discount = $coupon['discount_value'];
    }
    
    if ($subtotal < $coupon['min_purchase']) {
        unset($_SESSION['coupon']);
        $discount = 0;
    }
}

$total = $subtotal + $shippingCost + $tax - $discount;

require_once __DIR__ . '/includes/header.php';
?>

<!-- Cart Section -->
<section class="cart-section py-5">
    <div class="container">
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8 mb-4">
                <div class="cart-items-card" data-aos="fade-right">
                    <div class="card-header">
                        <h2><i class="fas fa-shopping-cart me-2"></i>Shopping Cart</h2>
                        <span class="badge bg-primary"><?php echo count($cartItems); ?> items</span>
                    </div>
                    
                    <?php if (empty($cartItems)): ?>
                    <div class="empty-cart text-center py-5">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                        <h3>Your cart is empty</h3>
                        <p class="text-muted mb-4">Add some products to get started</p>
                        <a href="products.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                        </a>
                    </div>
                    <?php else: ?>
                    <form method="POST" action="">
                        <div class="cart-items-list">
                            <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item">
                                <div class="cart-item-image">
                                    <a href="product-details.php?id=<?php echo $item['id']; ?>">
                                        <img src="assets/images/products/<?php echo htmlspecialchars($item['image'] ?? 'placeholder.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </a>
                                </div>
                                <div class="cart-item-details">
                                    <div class="cart-item-info">
                                        <h4 class="cart-item-title">
                                            <a href="product-details.php?id=<?php echo $item['id']; ?>">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </a>
                                        </h4>
                                        <p class="cart-item-category"><?php echo htmlspecialchars($item['category_name'] ?? 'Electronics'); ?></p>
                                        <?php if ($item['stock_quantity'] > $item['low_stock_threshold']): ?>
                                        <span class="text-success small"><i class="fas fa-check-circle"></i> In Stock</span>
                                        <?php else: ?>
                                        <span class="text-warning small"><i class="fas fa-exclamation-triangle"></i> Low Stock</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="cart-item-price">
                                        <span class="current-price"><?php echo formatPrice($item['price']); ?></span>
                                        <?php if ($item['compare_price'] && $item['compare_price'] > $item['price']): ?>
                                        <span class="original-price"><?php echo formatPrice($item['compare_price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="cart-item-quantity">
                                        <div class="quantity-selector">
                                            <button type="button" class="btn btn-outline-secondary minus" onclick="updateCartQuantity(<?php echo $item['id']; ?>, -1)">-</button>
                                            <input type="number" name="quantities[<?php echo $item['id']; ?>]" 
                                                   value="<?php echo $item['cart_quantity']; ?>" 
                                                   min="1" max="<?php echo $item['stock_quantity']; ?>" 
                                                   class="form-control text-center" style="width: 70px;">
                                            <button type="button" class="btn btn-outline-secondary plus" onclick="updateCartQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                                        </div>
                                    </div>
                                    <div class="cart-item-subtotal">
                                        <strong><?php echo formatPrice($item['price'] * $item['cart_quantity']); ?></strong>
                                    </div>
                                    <div class="cart-item-actions">
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" name="remove_item" class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('Remove this item from cart?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="cart-actions mt-4">
                            <button type="submit" name="update_cart" class="btn btn-outline-primary">
                                <i class="fas fa-sync-alt me-2"></i>Update Cart
                            </button>
                            <a href="products.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Order Summary -->
            <?php if (!empty($cartItems)): ?>
            <div class="col-lg-4">
                <div class="order-summary-card" data-aos="fade-left">
                    <div class="card-header">
                        <h3>Order Summary</h3>
                    </div>
                    
                    <div class="order-summary-content">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span><?php echo $shippingCost === 0 ? '<span class="text-success">FREE</span>' : formatPrice($shippingCost); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Tax (8%)</span>
                            <span><?php echo formatPrice($tax); ?></span>
                        </div>
                        
                        <?php if ($discount > 0): ?>
                        <div class="summary-row discount">
                            <span>Discount</span>
                            <span class="text-success">-<?php echo formatPrice($discount); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-row total">
                            <span>Total</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                        
                        <!-- Coupon Code -->
                        <div class="coupon-section mt-4">
                            <form method="POST" action="">
                                <div class="input-group">
                                    <input type="text" name="coupon_code" class="form-control" 
                                           placeholder="Enter coupon code" 
                                           value="<?php echo isset($_SESSION['coupon']) ? htmlspecialchars($_SESSION['coupon']['code']) : ''; ?>">
                                    <button type="submit" name="apply_coupon" class="btn btn-outline-secondary">
                                        Apply
                                    </button>
                                </div>
                            </form>
                            <?php if (isset($_SESSION['coupon'])): ?>
                            <div class="coupon-applied mt-2">
                                <span class="badge bg-success">Coupon Applied: <?php echo htmlspecialchars($_SESSION['coupon']['code']); ?></span>
                                <a href="cart.php?remove_coupon=1" class="small text-danger ms-2">Remove</a>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-primary btn-lg w-100 mt-4">
                            <i class="fas fa-lock me-2"></i>Proceed to Checkout
                        </a>
                        
                        <div class="trust-badges mt-4">
                            <div class="trust-item">
                                <i class="fas fa-shield-alt text-success"></i>
                                <span>Secure Checkout</span>
                            </div>
                            <div class="trust-item">
                                <i class="fas fa-undo text-primary"></i>
                                <span>Easy Returns</span>
                            </div>
                            <div class="trust-item">
                                <i class="fas fa-truck text-info"></i>
                                <span>Fast Shipping</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
// Remove coupon
if (isset($_GET['remove_coupon'])) {
    unset($_SESSION['coupon']);
    setFlashMessage('success', 'Coupon removed');
    header('Location: cart.php');
    exit;
}
?>

<style>
.cart-section {
    background: var(--lighter);
}

.cart-items-card,
.order-summary-card {
    background: var(--white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--light);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2,
.card-header h3 {
    margin: 0;
    color: var(--dark);
}

.cart-items-list {
    padding: 1.5rem;
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr;
    gap: 1.5rem;
    padding: 1.5rem 0;
    border-bottom: 1px solid var(--light);
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 100px;
    height: 100px;
    border-radius: var(--radius);
    overflow: hidden;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-item-details {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
    gap: 1rem;
    align-items: center;
}

.cart-item-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.cart-item-title a {
    color: var(--dark);
    transition: var(--transition);
}

.cart-item-title a:hover {
    color: var(--primary);
}

.cart-item-category {
    color: var(--gray);
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.current-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary);
}

.original-price {
    font-size: 0.875rem;
    color: var(--gray);
    text-decoration: line-through;
    margin-left: 0.5rem;
}

.quantity-selector {
    display: flex;
    align-items: center;
}

.quantity-selector .btn {
    width: 35px;
    height: 35px;
    padding: 0;
}

.cart-item-subtotal {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--dark);
}

.cart-actions {
    padding: 1.5rem;
    border-top: 1px solid var(--light);
    display: flex;
    gap: 1rem;
}

.order-summary-content {
    padding: 1.5rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    color: var(--gray);
}

.summary-row.total {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--dark);
    margin-top: 1rem;
}

.summary-row.discount {
    color: var(--success);
}

.summary-divider {
    height: 1px;
    background: var(--light);
    margin: 1rem 0;
}

.coupon-section .input-group {
    border-radius: var(--radius);
    overflow: hidden;
}

.trust-badges {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.trust-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.875rem;
    color: var(--gray);
}

.empty-cart {
    padding: 3rem 1.5rem;
}

@media (max-width: 992px) {
    .cart-item {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .cart-item-image {
        width: 80px;
        height: 80px;
    }
    
    .cart-item-details {
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }
    
    .cart-item-details > *:nth-child(1),
    .cart-item-details > *:nth-child(2) {
        grid-column: 1 / -1;
    }
}

@media (max-width: 576px) {
    .cart-item-details {
        grid-template-columns: 1fr;
    }
    
    .cart-actions {
        flex-direction: column;
    }
    
    .cart-actions .btn {
        width: 100%;
    }
}
</style>

<script>
// Quantity update handled by main.js updateCartQuantity function
// which makes API calls to update cart on server
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
