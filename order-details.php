<?php
/**
 * Order Details Page
 * NexMart E-Commerce
 */
$pageTitle = 'Order Details';
$showBreadcrumb = true;
$breadcrumbItems = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'My Orders', 'url' => 'orders.php', 'active' => false],
    ['title' => 'Order Details', 'url' => '', 'active' => true]
];

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$orderId = intval($_GET['id'] ?? 0);
if (!$orderId) {
    header('Location: orders.php');
    exit;
}

try {
    if (isAdmin()) {
        $stmt = $pdo->prepare(
            "SELECT o.*, u.name as user_name, u.email as user_email
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE o.id = ?
             LIMIT 1"
        );
        $stmt->execute([$orderId]);
    } else {
        $stmt = $pdo->prepare(
            "SELECT o.*, u.name as user_name, u.email as user_email
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE o.id = ? AND o.user_id = ?
             LIMIT 1"
        );
        $stmt->execute([$orderId, getCurrentUserId()]);
    }

    $order = $stmt->fetch();
    if (!$order) {
        setFlashMessage('error', 'Order not found');
        header('Location: orders.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll();
} catch (PDOException $e) {
    setFlashMessage('error', 'Error loading order details');
    header('Location: orders.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="order-details-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h4 mb-0">Order Details</h1>
                            <small class="text-muted">Order #<?php echo htmlspecialchars($order['order_number']); ?></small>
                        </div>
                        <a href="<?php echo baseUrl('orders.php'); ?>" class="btn btn-secondary">Back to Orders</a>
                    </div>
                    <div class="card-body">
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <h5>Order Summary</h5>
                                <ul class="list-unstyled">
                                    <li><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($order['status'])); ?></li>
                                    <li><strong>Payment Status:</strong> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['payment_status']))); ?></li>
                                    <li><strong>Payment Method:</strong> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['payment_method']))); ?></li>
                                    <li><strong>Placed On:</strong> <?php echo formatDate($order['created_at'], 'F d, Y - g:i A'); ?></li>
                                </ul>
                            </div>

                            <div class="col-md-6">
                                <h5>Customer</h5>
                                <ul class="list-unstyled">
                                    <li><strong>Name:</strong> <?php echo htmlspecialchars($order['user_name']); ?></li>
                                    <li><strong>Email:</strong> <?php echo htmlspecialchars($order['user_email']); ?></li>
                                    <?php if (!empty($order['shipping_phone'])): ?>
                                    <li><strong>Phone:</strong> <?php echo htmlspecialchars($order['shipping_phone']); ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Shipping Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['shipping_name'] ?? $order['user_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['shipping_email'] ?? $order['user_email']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Address:</strong><br>
                                    <?php echo htmlspecialchars($order['shipping_address'] ?? ''); ?><br>
                                    <?php echo htmlspecialchars($order['shipping_city'] ?? ''); ?>,
                                    <?php echo htmlspecialchars($order['shipping_state'] ?? ''); ?>
                                    <?php echo htmlspecialchars($order['shipping_zip'] ?? ''); ?><br>
                                    <?php echo htmlspecialchars($order['shipping_country'] ?? ''); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Items in Order</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($orderItems)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4">No items found for this order.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($orderItems as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                <td class="text-center"><?php echo (int)$item['quantity']; ?></td>
                                                <td class="text-end"><?php echo formatPrice($item['price']); ?></td>
                                                <td class="text-end"><?php echo formatPrice($item['subtotal']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Order Notes</h5>
                            </div>
                            <div class="card-body">
                                <p><?php echo nl2br(htmlspecialchars($order['notes'] ?? 'No special instructions.')); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Order Total</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex justify-content-between py-1"><span>Subtotal</span><span><?php echo formatPrice($order['subtotal']); ?></span></li>
                                    <li class="d-flex justify-content-between py-1"><span>Shipping</span><span><?php echo formatPrice($order['shipping_cost']); ?></span></li>
                                    <li class="d-flex justify-content-between py-1"><span>Tax</span><span><?php echo formatPrice($order['tax']); ?></span></li>
                                    <li class="d-flex justify-content-between py-1"><span>Discount</span><span>-<?php echo formatPrice($order['discount']); ?></span></li>
                                    <li class="border-top mt-2 pt-2 d-flex justify-content-between fw-bold"><span>Total</span><span><?php echo formatPrice($order['total']); ?></span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php';
