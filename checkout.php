<?php
/**
 * Checkout Page
 * NexMart E-Commerce
 */
$pageTitle = 'Checkout';
$showBreadcrumb = true;
$breadcrumbItems = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Cart', 'url' => 'cart.php', 'active' => false],
    ['title' => 'Checkout', 'url' => '', 'active' => true]
];

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Require login
requireLogin();

// Get cart items
$cartItems = getCartItems();

if (empty($cartItems)) {
    setFlashMessage('error', 'Your cart is empty');
    header('Location: cart.php');
    exit;
}

// Get user data
$user = getCurrentUser();

// Calculate totals
$subtotal = getCartTotal();
$shippingCost = $subtotal > 50 ? 0 : 9.99;
$taxRate = 0.08;
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
}

$total = $subtotal + $shippingCost + $tax - $discount;

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Validate CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request');
        header('Location: checkout.php');
        exit;
    }
    
    // Get form data
    $shippingName = sanitize($_POST['shipping_name'] ?? $user['name']);
    $shippingEmail = sanitize($_POST['shipping_email'] ?? $user['email']);
    $shippingPhone = sanitize($_POST['shipping_phone'] ?? $user['phone']);
    $shippingAddress = sanitize($_POST['shipping_address'] ?? $user['address']);
    $shippingCity = sanitize($_POST['shipping_city'] ?? $user['city']);
    $shippingState = sanitize($_POST['shipping_state'] ?? $user['state']);
    $shippingZip = sanitize($_POST['shipping_zip'] ?? $user['zip']);
    $shippingCountry = sanitize($_POST['shipping_country'] ?? $user['country'] ?? 'USA');
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'cod');
    $notes = sanitize($_POST['notes'] ?? '');
    
    // Validate
    if (empty($shippingName) || empty($shippingEmail) || empty($shippingPhone) || empty($shippingAddress)) {
        setFlashMessage('error', 'Please fill in all required fields');
    } else {
        try {
            $pdo->beginTransaction();
            
            // Generate order number
            $orderNumber = generateOrderNumber();
            
            // Insert order
            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    user_id, order_number, subtotal, shipping_cost, tax, discount, total,
                    shipping_name, shipping_email, shipping_phone, shipping_address,
                    shipping_city, shipping_state, shipping_zip, shipping_country,
                    payment_method, payment_status, status, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
            ");
            $stmt->execute([
                getCurrentUserId(),
                $orderNumber,
                $subtotal,
                $shippingCost,
                $tax,
                $discount,
                $total,
                $shippingName,
                $shippingEmail,
                $shippingPhone,
                $shippingAddress,
                $shippingCity,
                $shippingState,
                $shippingZip,
                $shippingCountry,
                $paymentMethod,
                $paymentMethod === 'cod' ? 'pending' : 'pending',
                $notes
            ]);
            
            $orderId = $pdo->lastInsertId();
            
            // Insert order items
            foreach ($cartItems as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, price, subtotal)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $orderId,
                    $item['id'],
                    $item['name'],
                    $item['image'],
                    $item['cart_quantity'],
                    $item['price'],
                    $item['price'] * $item['cart_quantity']
                ]);
                
                // Update product quantity (decrement stock)
                $stmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                $stmt->execute([$item['cart_quantity'], $item['id']]);
            }
            
            // Insert payment record
            $stmt = $pdo->prepare("
                INSERT INTO payments (order_id, payment_method, amount, status, payment_date)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$orderId, $paymentMethod, $total, $paymentMethod === 'cod' ? 'pending' : 'pending']);
            
            $pdo->commit();
            
            // Clear cart
            clearCart();
            unset($_SESSION['coupon']);
            
            // Redirect to success page
            header('Location: order-success.php?id=' . $orderId);
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            setFlashMessage('error', 'Error placing order. Please try again.');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Checkout Section -->
<section class="checkout-section py-5">
    <div class="container">
        <div class="row">
            <!-- Checkout Form -->
            <div class="col-lg-8 mb-4">
                <div class="checkout-form-card" data-aos="fade-right">
                    <div class="card-header">
                        <h2><i class="fas fa-credit-card me-2"></i>Checkout</h2>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <!-- Shipping Information -->
                        <div class="form-section">
                            <h3><i class="fas fa-shipping-fast me-2"></i>Shipping Information</h3>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="shipping_name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="shipping_name" name="shipping_name" 
                                               value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="shipping_email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="shipping_email" name="shipping_email" 
                                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="shipping_phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="shipping_phone" name="shipping_phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="shipping_address" class="form-label">Address *</label>
                                <input type="text" class="form-control" id="shipping_address" name="shipping_address" 
                                       value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="shipping_city" class="form-label">City *</label>
                                        <input type="text" class="form-control" id="shipping_city" name="shipping_city" 
                                               value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="shipping_state" class="form-label">State *</label>
                                        <input type="text" class="form-control" id="shipping_state" name="shipping_state" 
                                               value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="shipping_zip" class="form-label">ZIP Code *</label>
                                        <input type="text" class="form-control" id="shipping_zip" name="shipping_zip" 
                                               value="<?php echo htmlspecialchars($user['zip'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="shipping_country" class="form-label">Country</label>
                                <select class="form-select" id="shipping_country" name="shipping_country">
                                    <option value="USA" <?php echo ($user['country'] ?? 'USA') === 'USA' ? 'selected' : ''; ?>>United States</option>
                                    <option value="Canada" <?php echo ($user['country'] ?? '') === 'Canada' ? 'selected' : ''; ?>>Canada</option>
                                    <option value="UK" <?php echo ($user['country'] ?? '') === 'UK' ? 'selected' : ''; ?>>United Kingdom</option>
                                    <option value="Australia" <?php echo ($user['country'] ?? '') === 'Australia' ? 'selected' : ''; ?>>Australia</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Payment Method -->
                        <div class="form-section">
                            <h3><i class="fas fa-credit-card me-2"></i>Payment Method</h3>
                            
                            <div class="payment-methods">
                                <div class="payment-option">
                                    <input type="radio" name="payment_method" id="payment_cod" value="cod" checked>
                                    <label for="payment_cod" class="payment-label">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <div>
                                            <strong>Cash on Delivery</strong>
                                            <small>Pay when you receive your order</small>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="payment-option">
                                    <input type="radio" name="payment_method" id="payment_bank" value="bank_transfer">
                                    <label for="payment_bank" class="payment-label">
                                        <i class="fas fa-university"></i>
                                        <div>
                                            <strong>Bank Transfer</strong>
                                            <small>Transfer directly to our bank account</small>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="payment-option">
                                    <input type="radio" name="payment_method" id="payment_card" value="visa">
                                    <label for="payment_card" class="payment-label">
                                        <i class="fas fa-credit-card"></i>
                                        <div>
                                            <strong>Credit/Debit Card</strong>
                                            <small>Visa, MasterCard, American Express</small>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="payment-option">
                                    <input type="radio" name="payment_method" id="payment_paypal" value="paypal">
                                    <label for="payment_paypal" class="payment-label">
                                        <i class="fab fa-paypal"></i>
                                        <div>
                                            <strong>PayPal</strong>
                                            <small>Pay securely with PayPal</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Notes -->
                        <div class="form-section">
                            <h3><i class="fas fa-sticky-note me-2"></i>Order Notes (Optional)</h3>
                            <div class="form-group">
                                <textarea class="form-control" name="notes" rows="4" 
                                          placeholder="Any special instructions for your order..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Terms -->
                        <div class="form-group mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-primary">Terms & Conditions</a> and <a href="#" class="text-primary">Privacy Policy</a>
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" name="place_order" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-lock me-2"></i>Place Order - <?php echo formatPrice($total); ?>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="order-summary-card" data-aos="fade-left">
                    <div class="card-header">
                        <h3>Order Summary</h3>
                    </div>
                    
                    <div class="order-summary-content">
                        <div class="order-items-preview">
                            <?php foreach (array_slice($cartItems, 0, 3) as $item): ?>
                            <div class="order-item-preview">
                                <img src="assets/images/products/<?php echo htmlspecialchars($item['image'] ?? 'placeholder.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div class="item-info">
                                    <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="item-qty">x<?php echo $item['cart_quantity']; ?></span>
                                </div>
                                <span class="item-price"><?php echo formatPrice($item['price'] * $item['cart_quantity']); ?></span>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if (count($cartItems) > 3): ?>
                            <div class="more-items">
                                +<?php echo count($cartItems) - 3; ?> more items
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="summary-divider"></div>
                        
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
                        
                        <div class="secure-checkout mt-4">
                            <i class="fas fa-shield-alt text-success"></i>
                            <span>Your payment information is secure and encrypted</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.checkout-section {
    background: var(--lighter);
}

.checkout-form-card,
.order-summary-card {
    background: var(--white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--light);
}

.card-header h2,
.card-header h3 {
    margin: 0;
    color: var(--dark);
}

.form-section {
    padding: 1.5rem;
    border-bottom: 1px solid var(--light);
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: var(--primary);
}

.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.payment-option {
    position: relative;
}

.payment-option input {
    position: absolute;
    opacity: 0;
}

.payment-label {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 2px solid var(--light);
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
}

.payment-label i {
    font-size: 1.5rem;
    color: var(--gray);
    width: 40px;
    text-align: center;
}

.payment-label strong {
    display: block;
    color: var(--dark);
}

.payment-label small {
    color: var(--gray);
}

.payment-option input:checked + .payment-label {
    border-color: var(--primary);
    background: rgba(37, 99, 235, 0.05);
}

.payment-option input:checked + .payment-label i {
    color: var(--primary);
}

.order-summary-content {
    padding: 1.5rem;
}

.order-items-preview {
    margin-bottom: 1.5rem;
}

.order-item-preview {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--light);
}

.order-item-preview:last-child {
    border-bottom: none;
}

.order-item-preview img {
    width: 50px;
    height: 50px;
    border-radius: var(--radius);
    object-fit: cover;
}

.item-info {
    flex: 1;
}

.item-name {
    display: block;
    font-weight: 500;
    color: var(--dark);
}

.item-qty {
    font-size: 0.875rem;
    color: var(--gray);
}

.item-price {
    font-weight: 600;
    color: var(--primary);
}

.more-items {
    text-align: center;
    color: var(--gray);
    font-size: 0.875rem;
    padding: 0.5rem 0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
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

.secure-checkout {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: rgba(16, 185, 129, 0.1);
    border-radius: var(--radius);
    font-size: 0.875rem;
    color: var(--success);
}

@media (max-width: 768px) {
    .form-section {
        padding: 1rem;
    }
    
    .payment-label {
        padding: 0.75rem;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
