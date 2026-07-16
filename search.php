<?php
/**
 * Search Results Page
 * NexMart E-Commerce
 */
$pageTitle = 'Search Results';
$showBreadcrumb = true;
$breadcrumbItems = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Search', 'url' => '', 'active' => true]
];

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Get search query
$search = sanitize($_GET['q'] ?? '');

if (empty($search)) {
    header('Location: products.php');
    exit;
}

// Get filter parameters
$categorySlug = sanitize($_GET['category'] ?? '');
$brandSlug = sanitize($_GET['brand'] ?? '');
$minPrice = floatval($_GET['min_price'] ?? 0);
$maxPrice = floatval($_GET['max_price'] ?? 0);
$sortBy = sanitize($_GET['sort'] ?? 'created_at');
$sortOrder = sanitize($_GET['order'] ?? 'DESC');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;

// Get category ID from slug
$categoryId = null;
if ($categorySlug) {
    try {
        $stmt = $pdo->prepare("SELECT id, name FROM categories WHERE slug = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$categorySlug]);
        $category = $stmt->fetch();
        if ($category) {
            $categoryId = $category['id'];
        }
    } catch (PDOException $e) {
        // Ignore error
    }
}

// Get brand ID from slug
$brandId = null;
if ($brandSlug) {
    try {
        $stmt = $pdo->prepare("SELECT id, name FROM brands WHERE slug = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$brandSlug]);
        $brand = $stmt->fetch();
        if ($brand) {
            $brandId = $brand['id'];
        }
    } catch (PDOException $e) {
        // Ignore error
    }
}

// Check for Apple filter (if not already filtered by brand)
if (!$brandId && isset($_GET['apple']) && $_GET['apple'] == '1') {
    try {
        $stmt = $pdo->prepare("SELECT id FROM brands WHERE name = 'Apple' AND status = 'active' LIMIT 1");
        $stmt->execute();
        $appleBrand = $stmt->fetch();
        if ($appleBrand) {
            $brandId = $appleBrand['id'];
        }
    } catch (PDOException $e) {
        // Ignore error
    }
}

// Get total count
$totalProducts = getSearchCount($search, $categoryId, $brandId, $minPrice > 0 ? $minPrice : null, $maxPrice > 0 ? $maxPrice : null);
$totalPages = ceil($totalProducts / $perPage);
$offset = ($page - 1) * $perPage;

// Get products
$products = searchProducts($search, $categoryId, $brandId, $minPrice > 0 ? $minPrice : null, $maxPrice > 0 ? $maxPrice : null, $sortBy, $sortOrder, $perPage, $offset);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Search Results Section -->
<section class="search-section py-5">
    <div class="container">
        <div class="search-header text-center mb-4" data-aos="fade-up">
            <h1>Search Results</h1>
            <p class="text-muted">
                Showing results for: <strong>"<?php echo htmlspecialchars($search); ?>"</strong>
                <span class="badge bg-primary"><?php echo $totalProducts; ?> products found</span>
            </p>
        </div>
        
        <?php if (empty($products)): ?>
        <div class="no-results text-center py-5" data-aos="fade-up">
            <i class="fas fa-search fa-4x text-muted mb-3"></i>
            <h3>No products found</h3>
            <p class="text-muted mb-4">We couldn't find any products matching your search.</p>
            <div class="suggestions">
                <p class="mb-2">Try:</p>
                <ul class="list-unstyled">
                    <li>Checking your spelling</li>
                    <li>Using more general terms</li>
                    <li>Trying different keywords</li>
                    <li>Browsing our categories</li>
                </ul>
            </div>
            <div class="search-actions mt-4">
                <a href="products.php" class="btn btn-primary btn-lg me-2">
                    <i class="fas fa-th-large me-2"></i>Browse All Products
                </a>
                <a href="categories.php" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-list me-2"></i>Browse Categories
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="product-grid" data-aos="fade-up">
            <?php foreach ($products as $product): ?>
            <?php 
            $discount = calculateDiscount($product['price'], $product['compare_price']);
            $rating = getProductRating($product['id']);
            ?>
            <div class="product-card">
                <?php if ($product['is_new']): ?>
                <span class="product-badge new">New</span>
                <?php endif; ?>
                <?php if ($discount > 0): ?>
                <span class="product-badge sale">-<?php echo $discount; ?>%</span>
                <?php endif; ?>
                <?php if ($product['quantity'] <= 0): ?>
                <span class="product-badge" style="background: var(--gray);">Out of Stock</span>
                <?php endif; ?>
                
                <div class="product-image">
                    <a href="product-details.php?id=<?php echo $product['id']; ?>">
                        <img src="assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </a>
                </div>
                
                <div class="product-details">
                    <span class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Electronics'); ?></span>
                    <h3 class="product-title">
                        <a href="product-details.php?id=<?php echo $product['id']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </h3>
                    <div class="product-rating">
                        <?php echo generateStars($rating['rating']); ?>
                        <span>(<?php echo $rating['count']; ?>)</span>
                    </div>
                    <div class="product-price">
                        <span class="current"><?php echo formatPrice($product['price']); ?></span>
                        <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                        <span class="original"><?php echo formatPrice($product['compare_price']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-buttons">
                        <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>" 
                                <?php echo $product['quantity'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i> 
                            <?php echo $product['quantity'] <= 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                        </button>
                        <button class="wishlist-btn <?php echo isInWishlist($product['id']) ? 'wishlisted' : ''; ?>" 
                                data-product-id="<?php echo $product['id']; ?>"
                                title="Add to Wishlist">
                            <i class="<?php echo isInWishlist($product['id']) ? 'fas' : 'far'; ?> fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-wrapper mt-5" data-aos="fade-up">
            <?php echo generatePagination($totalProducts, $page, $perPage, 'search.php?q=' . urlencode($search) . '&' . http_build_query(array_filter([
                'category' => $categorySlug,
                'brand' => $brandSlug,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'sort' => $sortBy,
                'order' => $sortOrder
            ]))); ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<style>
.search-section {
    background: var(--lighter);
}

.search-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.no-results {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: 3rem 1.5rem;
    box-shadow: var(--shadow);
}

.no-results i {
    color: var(--gray);
}

.suggestions {
    max-width: 400px;
    margin: 2rem auto;
    text-align: left;
}

.suggestions p {
    font-weight: 600;
    color: var(--dark);
}

.suggestions ul li {
    padding: 0.25rem 0;
    color: var(--gray);
}

.search-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.pagination-wrapper {
    background: var(--white);
    padding: 1.5rem;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow);
}

@media (max-width: 768px) {
    .search-actions {
        flex-direction: column;
    }
    
    .search-actions .btn {
        width: 100%;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
