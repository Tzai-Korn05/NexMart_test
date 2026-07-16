<?php
/**
 * Admin Orders Management
 * NexMart E-Commerce
 */
$pageTitle = 'Orders Management';
$isAdmin = true;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/email-config.php';

// Require admin access
requireAdmin();

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
        
        // Auto-complete payment when order is delivered
        if ($newStatus === 'delivered') {
            $stmt = $pdo->prepare("UPDATE payments SET status = 'completed' WHERE order_id = ? AND status = 'pending'");
            $stmt->execute([$orderId]);
            
            // Also update order payment_status
            $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
            $stmt->execute([$orderId]);
        }
        
        // Invalidate payment when order is cancelled
        if ($newStatus === 'cancelled') {
            $stmt = $pdo->prepare("UPDATE payments SET status = 'invalid' WHERE order_id = ? AND status != 'completed'");
            $stmt->execute([$orderId]);
            
            // Also update order payment_status
            $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'cancelled' WHERE id = ?");
            $stmt->execute([$orderId]);
        }
        
        // Get order details for email
        $stmt = $pdo->prepare("
            SELECT o.*, u.email, u.name as customer_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if ($order) {
            // Get order items
            $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $order['items'] = $stmt->fetchAll();
            
            // Send status update email
            sendOrderEmail($order['email'], $order);
        }
        
        setFlashMessage('success', 'Order status updated successfully');
    } catch (PDOException $e) {
        setFlashMessage('error', 'Error updating order status');
    }
    
    header('Location: orders.php');
    exit;
}

// Get filters
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT o.*, u.name as customer_name, u.email as customer_email,
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE 1=1";
$params = [];

if ($status) {
    $sql .= " AND o.status = ?";
    $params[] = $status;
}

if ($search) {
    $sql .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY o.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
    // Get status counts
    $statusCounts = [];
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    while ($row = $stmt->fetch()) {
        $statusCounts[$row['status']] = $row['count'];
    }
} catch (PDOException $e) {
    $error = 'Error loading orders';
    $orders = [];
    $statusCounts = [];
}

require_once __DIR__ . '/../includes/header.php';

$adminActivePage = 'orders.php';
?>

<div class="admin-layout">
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1>Orders Management</h1>
        </div>
        
        <?php displayFlashMessage(); ?>
        
        <!-- Status Filter Tabs -->
        <div class="status-tabs mb-4">
            <a href="orders.php" class="status-tab <?php echo empty($status) ? 'active' : ''; ?>">
                All Orders <span class="badge"><?php echo array_sum($statusCounts); ?></span>
            </a>
            <a href="orders.php?status=pending" class="status-tab <?php echo $status === 'pending' ? 'active' : ''; ?>">
                Pending <span class="badge bg-warning"><?php echo $statusCounts['pending'] ?? 0; ?></span>
            </a>
            <a href="orders.php?status=processing" class="status-tab <?php echo $status === 'processing' ? 'active' : ''; ?>">
                Processing <span class="badge bg-info"><?php echo $statusCounts['processing'] ?? 0; ?></span>
            </a>
            <a href="orders.php?status=shipped" class="status-tab <?php echo $status === 'shipped' ? 'active' : ''; ?>">
                Shipped <span class="badge bg-primary"><?php echo $statusCounts['shipped'] ?? 0; ?></span>
            </a>
            <a href="orders.php?status=delivered" class="status-tab <?php echo $status === 'delivered' ? 'active' : ''; ?>">
                Delivered <span class="badge bg-success"><?php echo $statusCounts['delivered'] ?? 0; ?></span>
            </a>
        </div>
        
        <!-- Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by order number, customer name, or email..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="orders.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Orders Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">No orders found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                    </td>
                                    <td><?php echo $order['item_count']; ?> item(s)</td>
                                    <td><strong><?php echo formatPrice($order['total']); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $order['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span><br>
                                        <small><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="mb-2">
                                            <span class="badge bg-<?php 
                                                echo $order['status'] === 'delivered' ? 'success' : 
                                                ($order['status'] === 'shipped' ? 'primary' : 
                                                ($order['status'] === 'processing' ? 'info' : 
                                                ($order['status'] === 'cancelled' ? 'danger' : 'warning'))); 
                                            ?>" style="font-size: 0.85rem;">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </div>
                                        
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <form method="POST" class="d-inline me-1">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <input type="hidden" name="status" value="processing">
                                                <input type="hidden" name="update_status" value="1">
                                                <button type="submit" class="btn btn-sm btn-info" title="Mark as Processing">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Cancel this order?');">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <input type="hidden" name="status" value="cancelled">
                                                <input type="hidden" name="update_status" value="1">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Cancel Order">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        <?php elseif ($order['status'] === 'processing'): ?>
                                            <form method="POST" class="d-inline me-1">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <input type="hidden" name="status" value="shipped">
                                                <input type="hidden" name="update_status" value="1">
                                                <button type="submit" class="btn btn-sm btn-primary" title="Mark as Shipped">
                                                    <i class="fas fa-shipping-fast"></i>
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Cancel this order?');">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <input type="hidden" name="status" value="cancelled">
                                                <input type="hidden" name="update_status" value="1">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Cancel Order">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        <?php elseif ($order['status'] === 'shipped'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <input type="hidden" name="status" value="delivered">
                                                <input type="hidden" name="update_status" value="1">
                                                <button type="submit" class="btn btn-sm btn-success" title="Mark as Delivered">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            </form>
                                        <?php elseif ($order['status'] === 'delivered'): ?>
                                            <small class="text-success"><i class="fas fa-check"></i> Complete</small>
                                        <?php elseif ($order['status'] === 'cancelled'): ?>
                                            <small class="text-danger"><i class="fas fa-ban"></i> Cancelled</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatDate($order['created_at']); ?></td>
                                    <td>
                                        <a href="<?php echo baseUrl('order-details.php?id=' . $order['id']); ?>" 
                                           class="btn btn-sm btn-primary" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-3 text-muted">
            Total: <?php echo count($orders); ?> order(s)
        </div>
    </main>
</div>

</body>
</html>
