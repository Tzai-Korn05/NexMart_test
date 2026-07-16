<?php
$pageTitle = 'Payments Dashboard';
$isAdmin = true;
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Handle Transaction ID submission and mark payment as completed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_transaction'])) {
    $paymentId = filter_input(INPUT_POST, 'payment_id', FILTER_VALIDATE_INT);
    $transactionId = trim($_POST['transaction_id']);
    
    if ($paymentId && !empty($transactionId)) {
        try {
            $pdo->beginTransaction();
            
            // Update payment with transaction ID and mark as completed
            $stmt = $pdo->prepare("UPDATE payments SET transaction_id = ?, status = 'completed' WHERE id = ?");
            $stmt->execute([$transactionId, $paymentId]);
            
            // Get order_id to update order status
            $stmt = $pdo->prepare("SELECT order_id FROM payments WHERE id = ?");
            $stmt->execute([$paymentId]);
            $orderId = $stmt->fetchColumn();
            
            // Update order payment_status to 'paid'
            if ($orderId) {
                $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
                $stmt->execute([$orderId]);
            }
            
            $pdo->commit();
            $success = 'Payment completed successfully!';
            
            // Redirect to refresh page and show updated data
            header('Location: payments.php?success=1');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error updating payment: ' . $e->getMessage();
        }
    } else {
        $error = 'Please enter a valid Transaction ID';
    }
}

// Handle payment cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_payment'])) {
    $paymentId = filter_input(INPUT_POST, 'payment_id', FILTER_VALIDATE_INT);
    
    if ($paymentId) {
        try {
            $pdo->beginTransaction();
            
            // Update payment status to cancelled
            $stmt = $pdo->prepare("UPDATE payments SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$paymentId]);
            
            // Get order_id to update order status
            $stmt = $pdo->prepare("SELECT order_id FROM payments WHERE id = ?");
            $stmt->execute([$paymentId]);
            $orderId = $stmt->fetchColumn();
            
            // Update order status to cancelled and payment_status to failed
            if ($orderId) {
                $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', payment_status = 'failed' WHERE id = ?");
                $stmt->execute([$orderId]);
            }
            
            $pdo->commit();
            
            // Redirect to refresh page and show updated data
            header('Location: payments.php?cancelled=1');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error cancelling payment: ' . $e->getMessage();
        }
    } else {
        $error = 'Invalid payment ID';
    }
}

try {
    $stmt = $pdo->query("SELECT p.*, o.order_number, u.name as customer_name 
        FROM payments p 
        JOIN orders o ON p.order_id = o.id 
        JOIN users u ON o.user_id = u.id 
        ORDER BY p.created_at DESC LIMIT 100");
    $payments = $stmt->fetchAll();
    
    // Get payment statistics
    $stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
    $totalRevenue = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'pending'");
    $pendingAmount = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM payments WHERE status = 'completed'");
    $completedCount = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM payments WHERE status = 'pending'");
    $pendingCount = $stmt->fetch()['total'] ?? 0;
    
    $averageTransaction = $completedCount > 0 ? $totalRevenue / $completedCount : 0;
    
    // Daily payment data for chart (last 30 days)
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m-%d') as date, 
               SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_amount,
               SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount
        FROM payments 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY date 
        ORDER BY date ASC
    ");
    $dailyPayments = $stmt->fetchAll();
    
    // Payment method breakdown
    $stmt = $pdo->query("
        SELECT payment_method, COUNT(*) as count, SUM(amount) as total_amount
        FROM payments 
        WHERE status = 'completed'
        GROUP BY payment_method
    ");
    $paymentMethods = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Error loading payments';
    $payments = [];
    $dailyPayments = [];
    $paymentMethods = [];
    $totalRevenue = 0;
    $pendingAmount = 0;
    $completedCount = 0;
    $pendingCount = 0;
    $averageTransaction = 0;
}

require_once __DIR__ . '/../includes/header.php';

$adminActivePage = 'payments.php';
?>

<div class="admin-layout">
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header"><h1>Payments Dashboard</h1></div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> Payment completed successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['cancelled'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-ban"></i> Payment cancelled successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Payment Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card" style="background:#fff;padding:1.5rem;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.08);display:flex;align-items:center;gap:1rem;">
                    <div style="background:linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);width:64px;height:64px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-dollar-sign" style="color:#fff;font-size:28px;"></i>
                    </div>
                    <div>
                        <h2 style="margin:0;font-size:1.75rem;font-weight:700;color:#1e293b;"><?php echo formatPrice($totalRevenue); ?></h2>
                        <p style="margin:0;color:#64748b;font-size:0.875rem;">Total Revenue</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stat-card" style="background:#fff;padding:1.5rem;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.08);display:flex;align-items:center;gap:1rem;">
                    <div style="background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%);width:64px;height:64px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-clock" style="color:#fff;font-size:28px;"></i>
                    </div>
                    <div>
                        <h2 style="margin:0;font-size:1.75rem;font-weight:700;color:#1e293b;"><?php echo formatPrice($pendingAmount); ?></h2>
                        <p style="margin:0;color:#64748b;font-size:0.875rem;">Pending Payments</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stat-card" style="background:#fff;padding:1.5rem;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.08);display:flex;align-items:center;gap:1rem;">
                    <div style="background:linear-gradient(135deg, #10b981 0%, #059669 100%);width:64px;height:64px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-check-circle" style="color:#fff;font-size:28px;"></i>
                    </div>
                    <div>
                        <h2 style="margin:0;font-size:1.75rem;font-weight:700;color:#1e293b;"><?php echo $completedCount; ?></h2>
                        <p style="margin:0;color:#64748b;font-size:0.875rem;">Completed</p>
                        <small style="color:#94a3b8;">/ <?php echo $pendingCount; ?> pending</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stat-card" style="background:#fff;padding:1.5rem;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.08);display:flex;align-items:center;gap:1rem;">
                    <div style="background:linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);width:64px;height:64px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-chart-line" style="color:#fff;font-size:28px;"></i>
                    </div>
                    <div>
                        <h2 style="margin:0;font-size:1.75rem;font-weight:700;color:#1e293b;"><?php echo formatPrice($averageTransaction); ?></h2>
                        <p style="margin:0;color:#64748b;font-size:0.875rem;">Avg. Transaction</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-lg-8 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Payment Trends (Last 30 Days)</h3>
                        <canvas id="paymentTrendsChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Payment Methods</h3>
                        <canvas id="paymentMethodsChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h3 class="card-title mb-3">Recent Payments</h3>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Transaction ID</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                            <tr><td colspan="8" class="text-center py-4">No payments found</td></tr>
                        <?php else: ?>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($payment['order_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($payment['customer_name']); ?></td>
                                <td><strong><?php echo formatPrice($payment['amount']); ?></strong></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $payment['status'] === 'completed' ? 'success' : 
                                        ($payment['status'] === 'pending' ? 'warning' : 
                                        ($payment['status'] === 'invalid' ? 'secondary' : 
                                        ($payment['status'] === 'cancelled' ? 'secondary' : 'danger'))); 
                                    ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($payment['status'] === 'pending'): ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Mark this payment as completed?');">
                                            <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                            <input type="text" name="transaction_id" class="form-control form-control-sm d-inline-block" 
                                                   style="width: 150px;" placeholder="Enter Transaction ID" required>
                                    <?php else: ?>
                                        <small><?php echo htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatDate($payment['created_at']); ?></td>
                                <td>
                                    <?php if ($payment['status'] === 'pending'): ?>
                                            <button type="submit" name="update_transaction" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Complete
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this payment?');">
                                            <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                            <button type="submit" name="cancel_payment" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="fas fa-check-circle"></i> Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
// Payment Trends Chart (Bar Chart)
const paymentTrendsCtx = document.getElementById('paymentTrendsChart').getContext('2d');
new Chart(paymentTrendsCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($dailyPayments, 'date')); ?>,
        datasets: [
            {
                label: 'Completed Payments',
                data: <?php echo json_encode(array_column($dailyPayments, 'completed_amount')); ?>,
                backgroundColor: '#10b981',
                borderColor: '#059669',
                borderWidth: 1,
                borderRadius: 6
            },
            {
                label: 'Pending Payments',
                data: <?php echo json_encode(array_column($dailyPayments, 'pending_amount')); ?>,
                backgroundColor: '#f59e0b',
                borderColor: '#d97706',
                borderWidth: 1,
                borderRadius: 6
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 15
                }
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += '$' + context.parsed.y.toFixed(2);
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        }
    }
});

// Payment Methods Chart (Doughnut Chart)
const paymentMethodsCtx = document.getElementById('paymentMethodsChart').getContext('2d');
new Chart(paymentMethodsCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_map(function($m) { 
            return ucfirst(str_replace('_', ' ', $m['payment_method'])); 
        }, $paymentMethods)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($paymentMethods, 'total_amount')); ?>,
            backgroundColor: [
                '#3b82f6',
                '#10b981',
                '#f59e0b',
                '#8b5cf6',
                '#ef4444',
                '#06b6d4'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    usePointStyle: true,
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return label + ': $' + value.toFixed(2) + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});
</script>

</body>
</html>
