<?php
/**
 * User Orders Page
 * NexMart E-Commerce
 */
$pageTitle = 'My Orders';
$showBreadcrumb = true;
$breadcrumbItems = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'My Orders', 'url' => '', 'active' => true]
];

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Require login
requireLogin();

// Load current user
$user = getCurrentUser() ?? [];

// Get user orders
try {
    $stmt = $pdo->prepare("
        SELECT o.*, 
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([getCurrentUserId()]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Orders Section -->
<section class="orders-section py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="profile-sidebar" data-aos="fade-right">
                    <div class="user-info">
                        <div class="user-avatar">
                            <img src="uploads/users/<?php echo htmlspecialchars($user['image'] ?? 'default.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($user['name'] ?? 'User'); ?>">
                        </div>
                        <h4><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                    </div>
                    
                    <nav class="profile-nav">
                        <a href="profile.php" class="nav-link">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <a href="orders.php" class="nav-link active">
                            <i class="fas fa-box me-2"></i>My Orders
                        </a>
                        <a href="wishlist.php" class="nav-link">
                            <i class="fas fa-heart me-2"></i>Wishlist
                        </a>
                        <a href="#" class="nav-link">
                            <i class="fas fa-map-marker-alt me-2"></i>Addresses
                        </a>
                        <a href="#" class="nav-link">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                        <a href="logout.php" class="nav-link text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="orders-content" data-aos="fade-left">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-box me-2"></i>My Orders</h3>
                            <span class="badge bg-primary"><?php echo count($orders); ?> orders</span>
                        </div>
                        <div class="card-body">
                            <?php if (empty($orders)): ?>
                            <div class="empty-orders text-center py-5">
                                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                                <h3>No orders yet</h3>
                                <p class="text-muted mb-4">Start shopping to see your orders here</p>
                                <a href="products.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                                </a>
                            </div>
                            <?php else: ?>
                            <div class="orders-list">
                                <?php foreach ($orders as $order): ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <div class="order-number">
                                            <span class="label">Order #:</span>
                                            <span class="number"><?php echo htmlspecialchars($order['order_number']); ?></span>
                                        </div>
                                        <div class="order-date">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo formatDate($order['created_at']); ?>
                                        </div>
                                        <div class="order-status">
                                            <span class="badge bg-<?php 
                                                echo $order['status'] === 'delivered' ? 'success' : 
                                                ($order['status'] === 'cancelled' ? 'danger' : 
                                                ($order['status'] === 'shipped' ? 'info' : 'warning')); 
                                            ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="order-body">
                                        <div class="order-info">
                                            <div class="info-item">
                                                <span class="label">Items:</span>
                                                <span><?php echo $order['item_count']; ?> items</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="label">Total:</span>
                                                <span class="total"><?php echo formatPrice($order['total']); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span class="label">Payment:</span>
                                                <span><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="order-actions">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </button>
                                            <?php if ($order['status'] === 'delivered'): ?>
                                            <button class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-redo me-1"></i>Reorder
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="order-footer">
                                        <div class="shipping-info">
                                            <i class="fas fa-truck me-2"></i>
                                            <span>Shipping to: <?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_state']); ?></span>
                                        </div>
                                        <?php if ($order['tracking_number']): ?>
                                        <div class="tracking-info">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            <span>Tracking: <?php echo htmlspecialchars($order['tracking_number']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.orders-section {
    background: var(--lighter);
}

.profile-sidebar {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    position: sticky;
    top: 100px;
}

.user-info {
    text-align: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--light);
}

.user-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    margin: 0 auto 1rem;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-info h4 {
    margin: 0.5rem 0 0.25rem;
    color: var(--dark);
}

.user-info p {
    margin: 0;
    color: var(--gray);
    font-size: 0.875rem;
}

.profile-nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.profile-nav .nav-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border-radius: var(--radius);
    color: var(--dark);
    text-decoration: none;
    transition: var(--transition);
}

.profile-nav .nav-link:hover,
.profile-nav .nav-link.active {
    background: var(--primary);
    color: var(--white);
}

.orders-content .card {
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

.card-header h3 {
    margin: 0;
    color: var(--dark);
}

.card-body {
    padding: 1.5rem;
}

.empty-orders {
    padding: 3rem 1.5rem;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.order-card {
    border: 1px solid var(--light);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    transition: var(--transition);
}

.order-card:hover {
    box-shadow: var(--shadow);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--light);
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.order-number {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.order-number .label {
    color: var(--gray);
    font-size: 0.875rem;
}

.order-number .number {
    font-weight: 600;
    color: var(--dark);
}

.order-date {
    color: var(--gray);
    font-size: 0.875rem;
}

.order-body {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.order-info {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-item .label {
    color: var(--gray);
    font-size: 0.75rem;
    text-transform: uppercase;
}

.info-item span:last-child {
    font-weight: 600;
    color: var(--dark);
}

.info-item .total {
    font-size: 1.125rem;
    color: var(--primary);
}

.order-actions {
    display: flex;
    gap: 0.5rem;
}

.order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid var(--light);
    font-size: 0.875rem;
    color: var(--gray);
    flex-wrap: wrap;
    gap: 1rem;
}

.shipping-info,
.tracking-info {
    display: flex;
    align-items: center;
}

@media (max-width: 992px) {
    .profile-sidebar {
        position: static;
    }
}

@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .order-body {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .order-info {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .order-actions {
        width: 100%;
    }
    
    .order-actions .btn {
        flex: 1;
    }
    
    .order-footer {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
function viewOrderDetails(orderId) {
    // In a real application, this would open a modal or navigate to order details page
    window.location.href = 'order-details.php?id=' + orderId;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
