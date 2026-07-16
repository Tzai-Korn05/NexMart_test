<?php
/**
 * Admin Dashboard
 * NexMart E-Commerce
 */
$pageTitle = 'Admin Dashboard';
$isAdmin = true;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin access
requireAdmin();

// Get dashboard statistics
try {
    // Monthly revenue (current month)
    $stmt = $pdo->query("SELECT SUM(total) as monthly_revenue FROM orders WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status != 'cancelled'");
    $monthlyRevenue = $stmt->fetch()['monthly_revenue'] ?? 0;
    
    // Last month revenue for comparison
    $stmt = $pdo->query("SELECT SUM(total) as last_month_revenue FROM orders WHERE MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND status != 'cancelled'");
    $lastMonthRevenue = $stmt->fetch()['last_month_revenue'] ?? 0;
    
    // Calculate revenue growth percentage
    $revenueGrowth = 0;
    if ($lastMonthRevenue > 0) {
        $revenueGrowth = (($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
    }
    
    // Total revenue
    $stmt = $pdo->query("SELECT SUM(total) as total_revenue FROM orders WHERE status != 'cancelled'");
    $totalRevenue = $stmt->fetch()['total_revenue'] ?? 0;
    
    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
    $totalOrders = $stmt->fetch()['total_orders'];
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE role = 'customer'");
    $totalUsers = $stmt->fetch()['total_users'];
    
    // Total products
    $stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products");
    $totalProducts = $stmt->fetch()['total_products'];
    
    // Pending orders
    $stmt = $pdo->query("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'");
    $pendingOrders = $stmt->fetch()['pending_orders'];
    
    // Low stock products
    $stmt = $pdo->query("SELECT COUNT(*) as low_stock FROM products WHERE quantity <= low_stock_threshold AND status = 'active'");
    $lowStock = $stmt->fetch()['low_stock'];
    
    // Recent orders
    $stmt = $pdo->query("
        SELECT o.*, u.name as customer_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 10
    ");
    $recentOrders = $stmt->fetchAll();
    
    // Best selling products
    $stmt = $pdo->query("
        SELECT p.id, p.name, p.price, SUM(oi.quantity) as total_sold 
        FROM products p 
        JOIN order_items oi ON p.id = oi.product_id 
        JOIN orders o ON oi.order_id = o.id 
        WHERE o.status != 'cancelled' 
        GROUP BY p.id, p.name, p.price
        ORDER BY total_sold DESC 
        LIMIT 5
    ");
    $bestSellingProducts = $stmt->fetchAll();
    
    // Daily sales data for chart (last 30 days)
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m-%d') as date, SUM(total) as sales 
        FROM orders 
        WHERE status != 'cancelled' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY date 
        ORDER BY date ASC
    ");
    $dailySales = $stmt->fetchAll();
    
    // Order status breakdown for chart
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM orders 
        GROUP BY status
    ");
    $orderStatusData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Daily sales data for the last 30 days (Sales & Revenue chart)
    $stmt = $pdo->query("
        SELECT 
            DATE(created_at) as date,
            DATE_FORMAT(created_at, '%b %d') as date_label,
            COUNT(*) as order_count,
            SUM(total) as revenue
        FROM orders 
        WHERE status != 'cancelled' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY date, date_label
        ORDER BY date ASC
    ");
    $dailySalesData = $stmt->fetchAll();
    
    // Top selling products by quantity (Top Products chart)
    $stmt = $pdo->query("
        SELECT 
            p.name,
            SUM(oi.quantity) as total_quantity,
            SUM(oi.quantity * oi.price) as total_revenue
        FROM products p
        JOIN order_items oi ON p.id = oi.product_id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status != 'cancelled'
        GROUP BY p.id, p.name
        ORDER BY total_quantity DESC
        LIMIT 10
    ");
    $topProductsData = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Error loading dashboard data';
    $todaySales = 0;
    $totalSales = 0;
    $todayRevenue = 0;
    $totalRevenue = 0;
    $monthlyRevenue = 0;
    $lastMonthRevenue = 0;
    $revenueGrowth = 0;
    $totalOrders = 0;
    $totalUsers = 0;
    $totalProducts = 0;
    $pendingOrders = 0;
    $lowStock = 0;
    $recentOrders = [];
    $bestSellingProducts = [];
    $dailySales = [];
    $orderStatusData = [];
    $dailySalesData = [];
    $topProductsData = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Admin Dashboard -->
<div class="admin-layout">
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div class="admin-header">
            <h1>Dashboard</h1>
            <div class="admin-user">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="dashboard-stats-grid">
            <!-- Monthly Revenue Card -->
            <div class="stat-card-modern">
                <div class="stat-icon-modern bg-green">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
                <div class="stat-content-modern">
                    <div class="stat-label-modern">Monthly Revenue</div>
                    <div class="stat-value-modern"><?php echo formatPrice($monthlyRevenue); ?></div>
                    <?php if ($revenueGrowth != 0): ?>
                        <div class="stat-trend <?php echo $revenueGrowth >= 0 ? 'trend-up' : 'trend-down'; ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <?php if ($revenueGrowth >= 0): ?>
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                    <polyline points="17 6 23 6 23 12"></polyline>
                                <?php else: ?>
                                    <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
                                    <polyline points="17 18 23 18 23 12"></polyline>
                                <?php endif; ?>
                            </svg>
                            <span><?php echo number_format(abs($revenueGrowth), 1); ?>% vs last month</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Pending Orders Card -->
            <div class="stat-card-modern">
                <div class="stat-icon-modern bg-warning">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
                <div class="stat-content-modern">
                    <div class="stat-label-modern">Pending Orders</div>
                    <div class="stat-value-modern"><?php echo $pendingOrders; ?></div>
                    <a href="orders.php?status=pending" class="stat-link">View all pending →</a>
                </div>
            </div>
            
            <!-- Total Customers Card -->
            <div class="stat-card-modern">
                <div class="stat-icon-modern bg-blue">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="stat-content-modern">
                    <div class="stat-label-modern">Total Customers</div>
                    <div class="stat-value-modern"><?php echo $totalUsers; ?></div>
                    <a href="users.php" class="stat-link">Manage customers →</a>
                </div>
            </div>
            
            <!-- Low Stock Alerts Card -->
            <div class="stat-card-modern">
                <div class="stat-icon-modern bg-danger">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div class="stat-content-modern">
                    <div class="stat-label-modern">Low Stock Alerts</div>
                    <div class="stat-value-modern"><?php echo $lowStock; ?></div>
                    <a href="inventory.php" class="stat-link">Check inventory →</a>
                </div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div class="dashboard-charts-grid">
            <div class="chart-card-modern">
                <div class="chart-header">
                    <h3>Daily Sales & Revenue (Last 30 Days)</h3>
                    <a href="reports.php" class="show-all-link">Show All</a>
                </div>
                <canvas id="dailySalesChart"></canvas>
            </div>
            
            <div class="chart-card-modern">
                <div class="chart-header">
                    <h3>Top Selling Products</h3>
                    <a href="products.php" class="show-all-link">Show All</a>
                </div>
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="recent-orders mt-4">
            <div class="card">
                <div class="card-header">
                    <h3>Recent Orders</h3>
                    <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><a href="orders.php"><?php echo htmlspecialchars($order['order_number']); ?></a></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo formatPrice($order['total']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $order['status'] === 'delivered' ? 'success' : 
                                            ($order['status'] === 'pending' ? 'warning' : 'info'); 
                                        ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($order['created_at']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Best Selling Products -->
        <div class="best-selling mt-4">
            <div class="card">
                <div class="card-header">
                    <h3>Best Selling Products</h3>
                    <a href="products.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bestSellingProducts as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo formatPrice($product['price']); ?></td>
                                    <td><?php echo $product['total_sold']; ?></td>
                                    <td><?php echo formatPrice($product['price'] * $product['total_sold']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>



<script>
// Daily Sales & Revenue Chart (Area Chart with real data)
const dailySalesCtx = document.getElementById('dailySalesChart').getContext('2d');
const dailyData = <?php echo json_encode($dailySalesData); ?>;

new Chart(dailySalesCtx, {
    type: 'line',
    data: {
        labels: dailyData.map(item => item.date_label),
        datasets: [
            {
                label: 'Orders',
                data: dailyData.map(item => item.order_count),
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                borderColor: '#3b82f6',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: '#3b82f6',
                yAxisID: 'y'
            },
            {
                label: 'Revenue ($)',
                data: dailyData.map(item => parseFloat(item.revenue)),
                backgroundColor: 'rgba(147, 197, 253, 0.2)',
                borderColor: '#93c5fd',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: '#93c5fd',
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false
        },
        plugins: {
            legend: {
                display: true,
                position: 'top',
                align: 'end',
                labels: {
                    usePointStyle: true,
                    padding: 20,
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            if (context.dataset.label === 'Revenue ($)') {
                                label += '$' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            } else {
                                label += context.parsed.y;
                            }
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                beginAtZero: true,
                ticks: {
                    font: {
                        size: 11
                    }
                },
                grid: {
                    color: '#e5e7eb'
                },
                title: {
                    display: true,
                    text: 'Orders'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                beginAtZero: true,
                ticks: {
                    font: {
                        size: 11
                    },
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                },
                grid: {
                    drawOnChartArea: false
                },
                title: {
                    display: true,
                    text: 'Revenue'
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        size: 11
                    }
                }
            }
        }
    }
});

// Top Selling Products Chart (Horizontal Bar Chart with real data)
const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
const topProductsData = <?php echo json_encode($topProductsData); ?>;

new Chart(topProductsCtx, {
    type: 'bar',
    data: {
        labels: topProductsData.map(item => {
            // Truncate long product names
            const name = item.name;
            return name.length > 30 ? name.substring(0, 30) + '...' : name;
        }),
        datasets: [{
            label: 'Units Sold',
            data: topProductsData.map(item => parseInt(item.total_quantity)),
            backgroundColor: '#3b82f6',
            borderRadius: 6,
            barThickness: 25
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Sold: ' + context.parsed.x + ' units';
                    },
                    afterLabel: function(context) {
                        const revenue = topProductsData[context.dataIndex].total_revenue;
                        return 'Revenue: $' + parseFloat(revenue).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    font: {
                        size: 11
                    }
                },
                grid: {
                    color: '#e5e7eb'
                }
            },
            y: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        size: 11
                    }
                }
            }
        }
    }
});
</script>

</body>
</html>
