<?php
/**
 * Admin Inventory Management
 * NexMart E-Commerce
 */
$pageTitle = 'Inventory Management';
$isAdmin = true;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Require admin access
requireAdmin();

// Handle bulk stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_update'])) {
    if (isset($_POST['products']) && is_array($_POST['products'])) {
        try {
            $pdo->beginTransaction();
            
            foreach ($_POST['products'] as $productId => $quantity) {
                $quantity = (int)$quantity;
                if ($quantity >= 0) {
                    $stmt = $pdo->prepare("UPDATE products SET quantity = ? WHERE id = ?");
                    $stmt->execute([$quantity, $productId]);
                }
            }
            
            $pdo->commit();
            setFlashMessage('success', 'Inventory updated successfully');
        } catch (PDOException $e) {
            $pdo->rollBack();
            setFlashMessage('error', 'Error updating inventory');
        }
        
        header('Location: inventory.php');
        exit;
    }
}

// Handle quick stock adjustment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_update'])) {
    $productId = (int)$_POST['product_id'];
    $newQuantity = (int)$_POST['quantity'];
    
    try {
        $stmt = $pdo->prepare("UPDATE products SET quantity = ? WHERE id = ?");
        $stmt->execute([$newQuantity, $productId]);
        setFlashMessage('success', 'Stock updated successfully');
    } catch (PDOException $e) {
        setFlashMessage('error', 'Error updating stock');
    }
    
    header('Location: inventory.php');
    exit;
}

// Get filters
$stockFilter = $_GET['stock'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$brandFilter = $_GET['brand'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT p.*, c.name as category_name, b.name as brand_name,
        CASE 
            WHEN p.quantity = 0 THEN 'out_of_stock'
            WHEN p.quantity <= p.low_stock_threshold THEN 'low_stock'
            ELSE 'in_stock'
        END as stock_status
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN brands b ON p.brand_id = b.id 
        WHERE 1=1";
$params = [];

if ($stockFilter === 'low') {
    $sql .= " AND p.quantity > 0 AND p.quantity <= p.low_stock_threshold";
} elseif ($stockFilter === 'out') {
    $sql .= " AND p.quantity = 0";
} elseif ($stockFilter === 'in') {
    $sql .= " AND p.quantity > p.low_stock_threshold";
}

if ($categoryFilter) {
    $sql .= " AND p.category_id = ?";
    $params[] = $categoryFilter;
}

if ($brandFilter) {
    $sql .= " AND p.brand_id = ?";
    $params[] = $brandFilter;
}

if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY p.quantity ASC, p.name ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Get inventory statistics
    $stats = [
        'total_products' => 0,
        'total_stock_value' => 0,
        'low_stock_count' => 0,
        'out_of_stock_count' => 0,
        'total_items' => 0
    ];
    
    $statsStmt = $pdo->query("
        SELECT 
            COUNT(*) as total_products,
            SUM(quantity * price) as total_stock_value,
            SUM(quantity) as total_items,
            SUM(CASE WHEN quantity > 0 AND quantity <= low_stock_threshold THEN 1 ELSE 0 END) as low_stock_count,
            SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as out_of_stock_count
        FROM products
    ");
    $stats = $statsStmt->fetch();
    
    // Get categories for filter
    $categoriesStmt = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name");
    $categories = $categoriesStmt->fetchAll();
    
    // Get brands for filter
    $brandsStmt = $pdo->query("SELECT id, name FROM brands WHERE status = 'active' ORDER BY name");
    $brands = $brandsStmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Error loading inventory';
    $products = [];
    $stats = ['total_products' => 0, 'total_stock_value' => 0, 'low_stock_count' => 0, 'out_of_stock_count' => 0, 'total_items' => 0];
    $categories = [];
    $brands = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Admin Layout -->
<div class="admin-layout">
    <?php 
    $adminActivePage = 'inventory.php';
    require_once __DIR__ . '/sidebar.php'; 
    ?>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-warehouse"></i> Inventory Management</h1>
        </div>
        
        <?php displayFlashMessage(); ?>
        
        <!-- Inventory Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Products</div>
                        <div class="stat-value"><?php echo number_format($stats['total_products']); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Items</div>
                        <div class="stat-value"><?php echo number_format($stats['total_items']); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Low Stock</div>
                        <div class="stat-value text-warning"><?php echo number_format($stats['low_stock_count']); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Out of Stock</div>
                        <div class="stat-value text-danger"><?php echo number_format($stats['out_of_stock_count']); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="stat-content" style="color: white;">
                        <div class="stat-label" style="color: rgba(255,255,255,0.9);">Total Stock Value</div>
                        <div class="stat-value" style="font-size: 2.5rem;"><?php echo formatPrice($stats['total_stock_value']); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Stock Status</label>
                        <select name="stock" class="form-select">
                            <option value="">All Stock Levels</option>
                            <option value="low" <?php echo $stockFilter === 'low' ? 'selected' : ''; ?>>Low Stock</option>
                            <option value="out" <?php echo $stockFilter === 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                            <option value="in" <?php echo $stockFilter === 'in' ? 'selected' : ''; ?>>In Stock</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $categoryFilter == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Brand</label>
                        <select name="brand" class="form-select">
                            <option value="">All Brands</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>" <?php echo $brandFilter == $brand['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="SKU or Name..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="inventory.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Inventory Table -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Product Inventory</h5>
                </div>
                
                <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Image</th>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Price</th>
                                    <th style="width: 120px;">Current Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">No products found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                    <tr class="<?php echo $product['stock_status'] === 'out_of_stock' ? 'table-danger' : ($product['stock_status'] === 'low_stock' ? 'table-warning' : ''); ?>">
                                        <td>
                                            <?php if ($product['image']): ?>
                                                <img src="../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <div style="width: 50px; height: 50px; background: #e5e7eb; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        </td>
                                        <td><code><?php echo htmlspecialchars($product['sku']); ?></code></td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['brand_name'] ?? 'N/A'); ?></td>
                                        <td><strong><?php echo formatPrice($product['price']); ?></strong></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge <?php 
                                                    echo $product['quantity'] == 0 ? 'bg-danger' : 
                                                        ($product['quantity'] <= $product['low_stock_threshold'] ? 'bg-warning' : 'bg-success'); 
                                                ?>" style="font-size: 1rem; padding: 0.5rem 0.75rem;">
                                                    <?php echo $product['quantity']; ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($product['quantity'] == 0): ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php elseif ($product['quantity'] <= $product['low_stock_threshold']): ?>
                                                <span class="badge bg-warning">Low Stock</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">In Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="product-edit.php?id=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-primary" 
                                               title="Edit Product">
                                                <i class="fas fa-edit"></i>
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
            Total: <?php echo count($products); ?> product(s)
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
