<?php
/**
 * Admin Product Edit/Add
 * NexMart E-Commerce
 */
$pageTitle = 'Edit Product';
$isAdmin = true;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$productId = $_GET['id'] ?? null;
$isEdit = $productId !== null;
$error = '';
$success = '';

// Get categories and brands
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();
$brands = $pdo->query("SELECT * FROM brands WHERE status = 'active' ORDER BY name")->fetchAll();

// If editing, get product data
$product = null;
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        setFlashMessage('error', 'Product not found');
        header('Location: products.php');
        exit;
    }
    $pageTitle = 'Edit Product: ' . $product['name'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $slug = sanitize($_POST['slug'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $shortDescription = sanitize($_POST['short_description'] ?? '');
    $categoryId = $_POST['category_id'] ?? '';
    $brandId = $_POST['brand_id'] ?? '';
    $sku = sanitize($_POST['sku'] ?? '');
    $price = $_POST['price'] ?? 0;
    $comparePrice = $_POST['compare_price'] ?? null;
    $quantity = $_POST['quantity'] ?? 0;
    $image = sanitize($_POST['image'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isNew = isset($_POST['is_new']) ? 1 : 0;
    $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    $status = $_POST['status'] ?? 'active';
    
    if (empty($name) || empty($price) || empty($categoryId)) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            if ($isEdit) {
                // Update product
                $stmt = $pdo->prepare("
                    UPDATE products SET 
                    name = ?, slug = ?, description = ?, short_description = ?,
                    category_id = ?, brand_id = ?, sku = ?, price = ?, compare_price = ?,
                    quantity = ?, image = ?, is_featured = ?, is_new = ?, is_bestseller = ?,
                    status = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $name, $slug, $description, $shortDescription,
                    $categoryId, $brandId, $sku, $price, $comparePrice,
                    $quantity, $image, $isFeatured, $isNew, $isBestseller,
                    $status, $productId
                ]);
                setFlashMessage('success', 'Product updated successfully!');
            } else {
                // Insert new product
                $stmt = $pdo->prepare("
                    INSERT INTO products (
                        name, slug, description, short_description,
                        category_id, brand_id, sku, price, compare_price,
                        quantity, image, is_featured, is_new, is_bestseller, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $name, $slug, $description, $shortDescription,
                    $categoryId, $brandId, $sku, $price, $comparePrice,
                    $quantity, $image, $isFeatured, $isNew, $isBestseller, $status
                ]);
                setFlashMessage('success', 'Product added successfully!');
            }
            
            header('Location: products.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Error saving product: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';

$adminActivePage = 'products.php';
?>

<div class="admin-layout">
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1><?php echo $isEdit ? 'Edit Product' : 'Add New Product'; ?></h1>
            <a href="products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Products</a>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Product Name *</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" class="form-control" 
                                       value="<?php echo htmlspecialchars($product['slug'] ?? ''); ?>">
                                <small class="text-muted">Leave empty to auto-generate</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Short Description</label>
                                <input type="text" name="short_description" class="form-control" 
                                       value="<?php echo htmlspecialchars($product['short_description'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Category *</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Brand</label>
                                <select name="brand_id" class="form-select">
                                    <option value="">Select Brand</option>
                                    <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo $brand['id']; ?>"
                                            <?php echo ($product['brand_id'] ?? '') == $brand['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($brand['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">SKU</label>
                                <input type="text" name="sku" class="form-control" 
                                       value="<?php echo htmlspecialchars($product['sku'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Price (MMK) *</label>
                                <input type="number" name="price" class="form-control" step="0.01"
                                       value="<?php echo $product['price'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Compare Price (MMK)</label>
                                <input type="number" name="compare_price" class="form-control" step="0.01"
                                       value="<?php echo $product['compare_price'] ?? ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" class="form-control"
                                       value="<?php echo $product['quantity'] ?? 0; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Image Filename</label>
                                <input type="text" name="image" class="form-control"
                                       value="<?php echo htmlspecialchars($product['image'] ?? ''); ?>">
                                <small class="text-muted">e.g., iphone15.jpg</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?php echo ($product['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($product['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="out_of_stock" <?php echo ($product['status'] ?? '') === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_featured" class="form-check-input" id="featured"
                                           <?php echo ($product['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="featured">Featured Product</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_new" class="form-check-input" id="new"
                                           <?php echo ($product['is_new'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="new">New Arrival</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_bestseller" class="form-check-input" id="bestseller"
                                           <?php echo ($product['is_bestseller'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="bestseller">Bestseller</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $isEdit ? 'Update Product' : 'Add Product'; ?>
                        </button>
                        <a href="products.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

</body>
</html>
