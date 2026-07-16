<?php
/**
 * Admin Analytics Dashboard
 * NexMart E-Commerce - Most Selling Products & Top Loyal Customers
 */

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$pageTitle = 'Analytics Dashboard';
$isAdmin = true;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin access
requireAdmin();

// Get most selling products
try {
    // Force fresh data - no query cache
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    $stmt = $pdo->query("
        SELECT
            p.id,
            p.name,
            p.image,
            p.price,
            c.name as category,
            SUM(oi.quantity) as total_sold,
            SUM(oi.quantity * oi.price) as total_revenue,
            COUNT(DISTINCT o.id) as total_orders
        FROM products p
        INNER JOIN order_items oi ON p.id = oi.product_id
        INNER JOIN orders o ON oi.order_id = o.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE o.status != 'cancelled'
        GROUP BY p.id, p.name, p.image, p.price, c.id, c.name
        ORDER BY total_sold DESC
        LIMIT 10
    ");
    $topProducts = $stmt->fetchAll();
    
    // Get top 10 loyal/frequent customers
    $stmt = $pdo->query("
        SELECT 
            u.id,
            u.name,
            u.email,
            u.phone,
            COUNT(DISTINCT o.id) as total_orders,
            SUM(o.total) as total_spent,
            MAX(o.created_at) as last_order_date,
            MIN(o.created_at) as first_order_date
        FROM users u
        INNER JOIN orders o ON u.id = o.user_id
        WHERE o.status != 'cancelled' AND u.role = 'customer'
        GROUP BY u.id
        ORDER BY total_spent DESC, total_orders DESC
        LIMIT 10
    ");
    $topCustomers = $stmt->fetchAll();
    
    // Get category-wise sales
    $stmt = $pdo->query("
        SELECT 
            c.name as category,
            COUNT(DISTINCT p.id) as product_count,
            SUM(oi.quantity) as units_sold,
            SUM(oi.quantity * oi.price) as revenue
        FROM products p
        INNER JOIN order_items oi ON p.id = oi.product_id
        INNER JOIN orders o ON oi.order_id = o.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE o.status != 'cancelled'
        GROUP BY c.id, c.name
        ORDER BY revenue DESC
    ");
    $categorySales = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Error loading analytics data: ' . $e->getMessage();
    $topProducts = [];
    $topCustomers = [];
    $categorySales = [];
}

require_once __DIR__ . '/../includes/header.php';

$adminActivePage = 'analytics.php';
?>

<div class="admin-layout">
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    
    <main class="admin-content">
        <!-- Header -->
        <header class="admin-header">
            <div>
                <h1><i class="fas fa-chart-line me-2"></i>Analytics Dashboard</h1>
                <p class="text-muted">Most selling products and top loyal customers</p>
            </div>
            <div class="admin-header-actions">
                <span class="text-muted"><?php echo date('l, F j, Y'); ?></span>
            </div>
        </header>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Top Selling Products -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-trophy me-2 text-warning"></i>Top 10 Most Selling Products
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($topProducts)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No sales data available</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="60">Rank</th>
                                    <th width="80">Image</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th class="text-center">Units Sold</th>
                                    <th class="text-center">Total Orders</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topProducts as $index => $product): ?>
                                    <tr>
                                        <td>
                                            <span class="badge <?php 
                                                echo $index === 0 ? 'bg-warning' : ($index === 1 ? 'bg-secondary' : ($index === 2 ? 'bg-info' : 'bg-light text-dark')); 
                                            ?>">
                                                #<?php echo $index + 1; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <img src="<?php echo baseUrl('assets/images/products/' . htmlspecialchars($product['image'] ?? 'placeholder.jpg')); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                 class="img-thumbnail"
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                                        <td><?php echo formatPrice($product['price']); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-primary"><?php echo number_format($product['total_sold']); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info"><?php echo number_format($product['total_orders']); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success"><?php echo formatPrice($product['total_revenue']); ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Loyal Customers -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-star me-2 text-warning"></i>Top 10 Loyal Customers
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($topCustomers)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No customer data available</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="60">Rank</th>
                                    <th>Customer Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th class="text-center">Total Orders</th>
                                    <th class="text-end">Total Spent</th>
                                    <th>First Order</th>
                                    <th>Last Order</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topCustomers as $index => $customer): ?>
                                    <tr>
                                        <td>
                                            <span class="badge <?php 
                                                echo $index === 0 ? 'bg-warning' : ($index === 1 ? 'bg-secondary' : ($index === 2 ? 'bg-info' : 'bg-light text-dark')); 
                                            ?>">
                                                #<?php echo $index + 1; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($customer['name']); ?></strong>
                                                <br>
                                                <small class="text-muted">ID: <?php echo $customer['id']; ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-primary"><?php echo number_format($customer['total_orders']); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success"><?php echo formatPrice($customer['total_spent']); ?></strong>
                                        </td>
                                        <td>
                                            <small><?php echo date('M d, Y', strtotime($customer['first_order_date'])); ?></small>
                                        </td>
                                        <td>
                                            <small><?php echo date('M d, Y', strtotime($customer['last_order_date'])); ?></small>
                                        </td>
                                        <td>
                                            <a href="<?php echo baseUrl('admin/users.php?view=' . $customer['id']); ?>" 
                                               class="btn btn-sm btn-primary" 
                                               title="View Customer">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Category Performance -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tags me-2"></i>Category Performance
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($categorySales)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No category data available</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th class="text-center">Products</th>
                                    <th class="text-center">Units Sold</th>
                                    <th class="text-end">Revenue</th>
                                    <th width="200">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $maxRevenue = max(array_column($categorySales, 'revenue'));
                                foreach ($categorySales as $category): 
                                    $percentage = ($category['revenue'] / $maxRevenue) * 100;
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($category['category']); ?></strong></td>
                                        <td class="text-center"><?php echo number_format($category['product_count']); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-info"><?php echo number_format($category['units_sold']); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success"><?php echo formatPrice($category['revenue']); ?></strong>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $percentage; ?>%"
                                                     aria-valuenow="<?php echo $percentage; ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <?php echo number_format($percentage, 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>

<script>
// Force one-time hard reload to clear cache
(function() {
    const cacheKey = 'analytics_cache_cleared_v2';
    const cleared = sessionStorage.getItem(cacheKey);
    
    if (!cleared) {
        sessionStorage.setItem(cacheKey, 'true');
        location.reload(true); // Hard reload
    }
})();
</script>
