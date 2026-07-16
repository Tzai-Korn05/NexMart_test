<?php
/**
 * Admin Sales Reports
 * NexMart E-Commerce
 */

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$pageTitle = 'Sales Reports';
$isAdmin = true;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

// Get date filter
$period = $_GET['period'] ?? 'month';
$dateFilter = match($period) {
    'today' => 'DATE(o.created_at) = CURDATE()',
    'week' => 'YEARWEEK(o.created_at) = YEARWEEK(NOW())',
    'month' => 'YEAR(o.created_at) = YEAR(NOW()) AND MONTH(o.created_at) = MONTH(NOW())',
    'year' => 'YEAR(o.created_at) = YEAR(NOW())',
    default => '1=1'
};

try {
    // Sales statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_orders,
            SUM(total) as total_revenue,
            AVG(total) as avg_order_value,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as completed_orders
        FROM orders o
        WHERE $dateFilter AND status != 'cancelled'
    ");
    $stats = $stmt->fetch();
    
    // Daily sales - respects date filter
    $stmt = $pdo->query("
        SELECT DATE(o.created_at) as date, SUM(o.total) as sales, COUNT(*) as orders
        FROM orders o
        WHERE $dateFilter AND o.status != 'cancelled'
        GROUP BY DATE(o.created_at)
        ORDER BY date DESC
        LIMIT 30
    ");
    $dailySales = $stmt->fetchAll();
    
    // Top selling products - sum actual quantities sold
    $stmt = $pdo->query("
        SELECT p.name, p.price, 
               SUM(oi.quantity) as sold,
               SUM(oi.quantity * oi.price) as revenue
        FROM order_items oi
        INNER JOIN products p ON oi.product_id = p.id  
        INNER JOIN orders o ON oi.order_id = o.id
        WHERE $dateFilter AND o.status != 'cancelled'
        GROUP BY p.id, p.name, p.price
        ORDER BY sold DESC
        LIMIT 10
    ");
    $topProducts = $stmt->fetchAll();
    
    // Top customers
    $stmt = $pdo->query("
        SELECT u.name, u.email, COUNT(o.id) as orders, SUM(o.total) as total_spent
        FROM users u
        JOIN orders o ON u.id = o.user_id
        WHERE $dateFilter AND o.status != 'cancelled'
        GROUP BY u.id, u.name, u.email
        ORDER BY total_spent DESC
        LIMIT 10
    ");
    $topCustomers = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Error loading reports';
    $stats = [];
    $dailySales = [];
    $topProducts = [];
    $topCustomers = [];
}

require_once __DIR__ . '/../includes/header.php';

$adminActivePage = 'reports.php';
?>

<div class="admin-layout">
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1>Sales Reports</h1>
            <div class="period-filter">
                <a href="?period=today" class="btn btn-sm <?php echo $period === 'today' ? 'btn-primary' : 'btn-outline-secondary'; ?>">Today</a>
                <a href="?period=week" class="btn btn-sm <?php echo $period === 'week' ? 'btn-primary' : 'btn-outline-secondary'; ?>">This Week</a>
                <a href="?period=month" class="btn btn-sm <?php echo $period === 'month' ? 'btn-primary' : 'btn-outline-secondary'; ?>">This Month</a>
                <a href="?period=year" class="btn btn-sm <?php echo $period === 'year' ? 'btn-primary' : 'btn-outline-secondary'; ?>">This Year</a>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-primary"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo formatPrice($stats['total_revenue'] ?? 0); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-success"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $stats['total_orders'] ?? 0; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-info"><i class="fas fa-chart-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo formatPrice($stats['avg_order_value'] ?? 0); ?></div>
                    <div class="stat-label">Avg Order Value</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-warning"><i class="fas fa-check-circle"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $stats['completed_orders'] ?? 0; ?></div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
        </div>
        
        <!-- Top Products -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h3>Top Selling Products</h3></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead><tr><th>Product</th><th>Sold</th><th>Revenue</th></tr></thead>
                            <tbody>
                                <?php foreach ($topProducts as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo $product['sold']; ?></td>
                                    <td><strong><?php echo formatPrice($product['revenue']); ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h3>Top Customers</h3></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead><tr><th>Customer</th><th>Orders</th><th>Total Spent</th></tr></thead>
                            <tbody>
                                <?php foreach ($topCustomers as $customer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                    <td><?php echo $customer['orders']; ?></td>
                                    <td><strong><?php echo formatPrice($customer['total_spent']); ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Daily Sales -->
        <div class="card">
            <div class="card-header"><h3>Daily Sales (Last 30 Days)</h3></div>
            <div class="card-body">
                <canvas id="salesChart" height="80"></canvas>
            </div>
        </div>
    </main>
</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_reverse(array_column($dailySales, 'date'))); ?>,
        datasets: [{
            label: 'Sales',
            data: <?php echo json_encode(array_reverse(array_column($dailySales, 'sales'))); ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {legend: {display: false}},
        scales: {y: {beginAtZero: true}}
    }
});
</script>

<script>
// Force one-time hard reload to clear cache
(function() {
    const cacheKey = 'reports_cache_cleared_v2';
    const cleared = sessionStorage.getItem(cacheKey);
    
    if (!cleared) {
        sessionStorage.setItem(cacheKey, 'true');
        location.reload(true); // Hard reload
    }
})();
</script>

</body>
</html>
