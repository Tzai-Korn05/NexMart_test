<?php
/**
 * Order Success Page
 * NexMart E-Commerce
 */
$pageTitle = 'Order Successful';
$showBreadcrumb = true;
$breadcrumbItems = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Order Success', 'url' => '', 'active' => true]
];

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Require login
requireLogin();

// Get order ID
$orderId = intval($_GET['id'] ?? 0);

if (!$orderId) {
    header('Location: profile.php');
    exit;
}

// Get order details
try {
    $stmt = $pdo->prepare("
        SELECT o.*, u.name as user_name, u.email as user_email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ? AND o.user_id = ? 
        LIMIT 1
    ");
    $stmt->execute([$orderId, getCurrentUserId()]);
    $order = $stmt->fetch();
    
    if (!$order) {
        setFlashMessage('error', 'Order not found');
        header('Location: profile.php');
        exit;
    }
    
    // Get order items
    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll();
    
} catch (PDOException $e) {
    setFlashMessage('error', 'Error loading order details');
    header('Location: profile.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Order Success Section -->
<section class="order-success-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="success-card" data-aos="fade-up">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h1>Order Placed Successfully!</h1>
                    <p class="lead">Thank you for your purchase. Your order has been received and is being processed.</p>
                    
                    <div class="order-number">
                        <span class="label">Order Number:</span>
                        <span class="number"><?php echo htmlspecialchars($order['order_number']); ?></span>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span>Date:</span>
                            <span><?php echo formatDate($order['created_at'], 'F d, Y - g:i A'); ?></span>
                        </div>
                        <div class="detail-row">
                            <span>Total:</span>
                            <span><?php echo formatPrice($order['total']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span>Payment Method:</span>
                            <span class="payment-method">
                                <?php
                                $paymentMethods = [
                                    'cod' => 'Cash on Delivery',
                                    'bank_transfer' => 'Bank Transfer',
                                    'visa' => 'Visa',
                                    'mastercard' => 'MasterCard',
                                    'paypal' => 'PayPal'
                                ];
                                echo htmlspecialchars($paymentMethods[$order['payment_method']] ?? ucfirst($order['payment_method']));
                                ?>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span>Status:</span>
                            <span class="badge bg-warning">Pending</span>
                        </div>
                    </div>
                    
                    <div class="order-items">
                        <h3>Order Items</h3>
                        <div class="items-list">
                            <?php foreach ($orderItems as $item): ?>
                            <div class="order-item">
                                <img src="assets/images/products/<?php echo htmlspecialchars($item['product_image'] ?? 'placeholder.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                <div class="item-info">
                                    <span class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                    <span class="item-qty">Quantity: <?php echo $item['quantity']; ?></span>
                                </div>
                                <span class="item-price"><?php echo formatPrice($item['subtotal']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="shipping-info">
                        <h3>Shipping Address</h3>
                        <div class="address">
                            <p><strong><?php echo htmlspecialchars($order['shipping_name']); ?></strong></p>
                            <p><?php echo htmlspecialchars($order['shipping_address']); ?></p>
                            <p><?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_state']); ?> <?php echo htmlspecialchars($order['shipping_zip']); ?></p>
                            <p><?php echo htmlspecialchars($order['shipping_country']); ?></p>
                            <p><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($order['shipping_phone']); ?></p>
                        </div>
                    </div>
                    
                    <div class="next-steps">
                        <h3>What's Next?</h3>
                        <ul>
                            <li><i class="fas fa-check text-success me-2"></i>You'll receive an order confirmation email shortly</li>
                            <li><i class="fas fa-check text-success me-2"></i>We'll process your order within 24 hours</li>
                            <li><i class="fas fa-check text-success me-2"></i>You'll receive tracking information once shipped</li>
                            <li><i class="fas fa-check text-success me-2"></i>Estimated delivery: 3-5 business days</li>
                        </ul>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="orders.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-box me-2"></i>View My Orders
                        </a>
                        <a href="products.php" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.order-success-section {
    background: var(--lighter);
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    padding: 3rem 0;
}

.success-card {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: 3rem;
    box-shadow: var(--shadow-xl);
    text-align: center;
}

.success-icon {
    font-size: 5rem;
    color: var(--success);
    margin-bottom: 1.5rem;
    animation: scaleIn 0.5s ease;
}

@keyframes scaleIn {
    0% { transform: scale(0); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.success-card h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 1rem;
}

.success-card .lead {
    color: var(--gray);
    font-size: 1.125rem;
    margin-bottom: 2rem;
}

.order-number {
    background: rgba(37, 99, 235, 0.1);
    padding: 1rem 2rem;
    border-radius: var(--radius-lg);
    display: inline-block;
    margin-bottom: 2rem;
}

.order-number .label {
    display: block;
    color: var(--gray);
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.order-number .number {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}

.order-details {
    background: var(--lighter);
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    margin-bottom: 2rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--light);
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row span:first-child {
    color: var(--gray);
}

.detail-row span:last-child {
    font-weight: 600;
    color: var(--dark);
}

.order-items {
    text-align: left;
    margin-bottom: 2rem;
}

.order-items h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--dark);
}

.items-list {
    background: var(--lighter);
    border-radius: var(--radius-lg);
    padding: 1rem;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--light);
}

.order-item:last-child {
    border-bottom: none;
}

.order-item img {
    width: 60px;
    height: 60px;
    border-radius: var(--radius);
    object-fit: cover;
}

.item-info {
    flex: 1;
    text-align: left;
}

.item-name {
    display: block;
    font-weight: 600;
    color: var(--dark);
}

.item-qty {
    font-size: 0.875rem;
    color: var(--gray);
}

.item-price {
    font-weight: 700;
    color: var(--primary);
}

.shipping-info {
    text-align: left;
    margin-bottom: 2rem;
}

.shipping-info h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--dark);
}

.address {
    background: var(--lighter);
    padding: 1.5rem;
    border-radius: var(--radius-lg);
}

.address p {
    margin: 0.25rem 0;
    color: var(--dark);
}

.next-steps {
    text-align: left;
    margin-bottom: 2rem;
}

.next-steps h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--dark);
}

.next-steps ul {
    list-style: none;
    padding: 0;
}

.next-steps li {
    padding: 0.5rem 0;
    color: var(--dark);
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .success-card {
        padding: 2rem 1.5rem;
    }
    
    .success-card h1 {
        font-size: 1.75rem;
    }
    
    .order-number .number {
        font-size: 1.25rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .action-buttons .btn {
        width: 100%;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
