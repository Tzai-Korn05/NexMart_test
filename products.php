<?php
/**
 * Products Listing Page
 * NexMart E-Commerce
 */
$pageTitle = 'Products';
$showBreadcrumb = true;
$breadcrumbItems = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Products', 'url' => '', 'active' => true]
];

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Get filter parameters
$search = sanitize($_GET['q'] ?? '');
$categorySlug = sanitize($_GET['category'] ?? '');
$minPrice = floatval($_GET['min_price'] ?? 0);
$maxPrice = floatval($_GET['max_price'] ?? 0);
$sortBy = sanitize($_GET['sort'] ?? 'created_at');
$sortOrder = sanitize($_GET['order'] ?? 'DESC');
$page = intval($_GET['page'] ?? 1);
$perPage = 9; // Show 9 products per page

// Handle AJAX request for loading more products
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json');
    
    $categoryId = null;
    if ($categorySlug) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$categorySlug]);
            $cat = $stmt->fetch();
            if ($cat) {
                $categoryId = $cat['id'];
            }
        } catch (PDOException $e) {
            // Ignore error
        }
    }
    
    $offset = ($page - 1) * $perPage;
    $products = searchProducts($search, $categoryId, null, $minPrice > 0 ? $minPrice : null, $maxPrice > 0 ? $maxPrice : null, $sortBy, $sortOrder, $perPage, $offset);
    
    $html = '';
    foreach ($products as $product) {
        $discount = calculateDiscount($product['price'], $product['compare_price']);
        $rating = getProductRating($product['id']);
        $isWishlisted = isInWishlist($product['id']);
        
        ob_start();
        ?>
        <div class="product-card" data-aos="fade-up">
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
                    <button class="wishlist-btn <?php echo $isWishlisted ? 'wishlisted' : ''; ?>" 
                            data-product-id="<?php echo $product['id']; ?>"
                            title="Add to Wishlist">
                        <i class="<?php echo $isWishlisted ? 'fas' : 'far'; ?> fa-heart"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php
        $html .= ob_get_clean();
    }
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'hasMore' => count($products) == $perPage
    ]);
    exit;
}

// Get category ID from slug
$categoryId = null;
// Ensure $category is defined to avoid undefined variable notices
$category = null;
if ($categorySlug) {
    try {
        $stmt = $pdo->prepare("SELECT id, name FROM categories WHERE slug = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$categorySlug]);
        $category = $stmt->fetch();
        if ($category) {
            $categoryId = $category['id'];
            $breadcrumbItems[] = ['title' => $category['name'], 'url' => '', 'active' => true];
        }
    } catch (PDOException $e) {
        // Ignore error
    }
}

// Get total count
$totalProducts = getSearchCount($search, $categoryId, null, $minPrice > 0 ? $minPrice : null, $maxPrice > 0 ? $maxPrice : null);
$totalPages = ceil($totalProducts / $perPage);
$offset = ($page - 1) * $perPage;

// Get products
$products = searchProducts($search, $categoryId, null, $minPrice > 0 ? $minPrice : null, $maxPrice > 0 ? $maxPrice : null, $sortBy, $sortOrder, $perPage, $offset);

// Get all categories for filters
$categories = getCategories();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Products Page -->
<section class="products-page py-5">
    <div class="container">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="filter-sidebar" data-aos="fade-right">
                    <div class="filter-header">
                        <h4><i class="fas fa-filter me-2"></i>Filters</h4>
                        <a href="products.php" class="btn btn-sm btn-outline-secondary">Clear All</a>
                    </div>
                    
                    <!-- Categories -->
                    <div class="filter-group">
                        <h5>Categories</h5>
                        <div class="category-filters">
                            <div class="form-check">
                                <input type="radio" class="form-check-input" name="category" id="cat-all" value="" 
                                       <?php echo !$categoryId ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="cat-all">All Categories</label>
                            </div>
                            <?php foreach ($categories as $cat): ?>
                            <div class="form-check">
                                <input type="radio" class="form-check-input" name="category" id="cat-<?php echo $cat['id']; ?>" 
                                       value="<?php echo htmlspecialchars($cat['slug']); ?>"
                                       <?php echo $categoryId == $cat['id'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="cat-<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Brands filter removed as requested -->
                    
                    <button class="btn btn-primary w-100 mt-3" id="applyFiltersBtn">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="col-lg-9">
                <!-- Products Header -->
                <div class="products-header mb-4" data-aos="fade-up">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h2 class="mb-1">
                                <?php echo $category ? htmlspecialchars($category['name']) : 'All Products'; ?>
                                <?php if ($search): ?>
                                    <small class="text-muted">- Search: "<?php echo htmlspecialchars($search); ?>"</small>
                                <?php endif; ?>
                            </h2>
                            <p class="text-muted mb-0"><?php echo $totalProducts; ?> products found</p>
                        </div>
                        <div class="sort-dropdown">
                            <select class="form-select" id="sortBy" onchange="applyFilters()">
                                <option value="created_at-DESC" <?php echo $sortBy == 'created_at' ? 'selected' : ''; ?>>Sort by: Newest</option>
                                <option value="price-ASC" <?php echo $sortBy == 'price' && $sortOrder == 'ASC' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price-DESC" <?php echo $sortBy == 'price' && $sortOrder == 'DESC' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="name-ASC" <?php echo $sortBy == 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <?php if (empty($products)): ?>
                <div class="text-center py-5" data-aos="fade-up">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h3>No products found</h3>
                    <p class="text-muted">Try adjusting your filters or search terms</p>
                    <a href="products.php" class="btn btn-primary mt-3">View All Products</a>
                </div>
                <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                    <?php 
                    $discount = calculateDiscount($product['price'], $product['compare_price']);
                    $rating = getProductRating($product['id']);
                    ?>
                    <div class="product-card" data-aos="fade-up">
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
                
                <!-- Load More Button -->
                <?php if ($totalProducts > $perPage): ?>
                <div class="text-center mt-4" id="loadMoreContainer">
                    <button class="btn btn-primary btn-lg" id="loadMoreBtn" data-page="1">
                        <i class="fas fa-spinner me-2"></i>Load More Products
                    </button>
                    <p class="text-muted mt-2 mb-0">
                        <span id="loadedCount"><?php echo min($perPage, $totalProducts); ?></span> of <?php echo $totalProducts; ?> products loaded
                    </p>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
.products-page {
    background: var(--lighter);
}

.filter-sidebar {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    position: sticky;
    top: 100px;
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--light);
}

.filter-header h4 {
    margin: 0;
    color: var(--primary);
}

.filter-group {
    margin-bottom: 1.5rem;
}

.filter-group h5 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: var(--dark);
}

.category-filters,
.brand-filters {
    max-height: 200px;
    overflow-y: auto;
}

.products-header {
    background: var(--white);
    padding: 1.5rem;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow);
}

.sort-dropdown select {
    min-width: 200px;
}

@media (max-width: 992px) {
    .filter-sidebar {
        position: static;
    }
}
</style>

<script>
// Apply filters function
function applyFilters() {
    console.log('=== applyFilters() called ===');
    
    // Get the checked category radio button
    const checkedRadio = document.querySelector('input[name="category"]:checked');
    console.log('Checked radio element:', checkedRadio);
    
    const category = checkedRadio ? checkedRadio.value : '';
    console.log('Category value:', category);
    
    // Get sort value
    const sortSelect = document.getElementById('sortBy');
    const sortValue = sortSelect ? sortSelect.value : '';
    console.log('Sort value:', sortValue);

    // Get current URL parameters to preserve search query
    const currentParams = new URLSearchParams(window.location.search);
    const searchQuery = currentParams.get('q');
    
    const params = new URLSearchParams();
    
    // Preserve search query if it exists
    if (searchQuery) params.append('q', searchQuery);
    
    // Add category if not empty
    if (category) {
        console.log('Adding category to params:', category);
        params.append('category', category);
    } else {
        console.log('Category is empty, not adding to params');
    }
    
    // Parse sort value (format: "field-order")
    if (sortValue) {
        const [sortField, sortOrder] = sortValue.split('-');
        params.append('sort', sortField);
        params.append('order', sortOrder);
    }
    
    const finalURL = 'products.php?' + params.toString();
    console.log('Final URL:', finalURL);
    console.log('Params string:', params.toString());
    
    window.location.href = finalURL;
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing filters...');
    
    // Attach event listeners to category radio buttons
    const categoryRadios = document.querySelectorAll('input[name="category"]');
    console.log('Found category radios:', categoryRadios.length);
    
    categoryRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            console.log('Radio changed, calling applyFilters()');
            applyFilters();
        });
    });
    
    // Attach event listener to apply filters button
    const applyBtn = document.getElementById('applyFiltersBtn');
    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            console.log('Apply button clicked');
            applyFilters();
        });
    }
    
    // Attach event listener to sort dropdown
    const sortSelect = document.getElementById('sortBy');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            console.log('Sort changed');
            applyFilters();
        });
    }
    
    // Load More Products functionality
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const totalProducts = <?php echo $totalProducts; ?>;
    const perPage = <?php echo $perPage; ?>;
    
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const currentPage = parseInt(this.getAttribute('data-page'));
            const nextPage = currentPage + 1;
            
            // Disable button and show loading state
            loadMoreBtn.disabled = true;
            const originalHTML = loadMoreBtn.innerHTML;
            loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
            
            // Build URL with current filters
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('page', nextPage);
            urlParams.set('ajax', '1');
            
            // Make AJAX request
            fetch('products.php?' + urlParams.toString())
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.html) {
                        // Append new products to grid
                        const productGrid = document.querySelector('.product-grid');
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data.html;
                        
                        // Append each product card
                        const newCards = tempDiv.querySelectorAll('.product-card');
                        newCards.forEach(card => {
                            productGrid.appendChild(card);
                        });
                        
                        // Update page counter
                        loadMoreBtn.setAttribute('data-page', nextPage);
                        
                        // Update loaded count
                        const loadedCount = document.getElementById('loadedCount');
                        const currentCount = parseInt(loadedCount.textContent);
                        const newCount = Math.min(currentCount + newCards.length, totalProducts);
                        loadedCount.textContent = newCount;
                        
                        // Re-initialize AOS for new elements
                        if (typeof AOS !== 'undefined') {
                            AOS.refresh();
                        }
                        
                        // Re-attach event listeners for new products
                        initializeProductButtons();
                        
                        // Check if there are more products to load
                        if (!data.hasMore || newCount >= totalProducts) {
                            // Hide the load more button
                            document.getElementById('loadMoreContainer').style.display = 'none';
                        } else {
                            // Re-enable button
                            loadMoreBtn.disabled = false;
                            loadMoreBtn.innerHTML = originalHTML;
                        }
                    } else {
                        // Error or no more products
                        loadMoreBtn.disabled = false;
                        loadMoreBtn.innerHTML = originalHTML;
                        showToast('No more products to load', 'info');
                    }
                })
                .catch(error => {
                    console.error('Error loading more products:', error);
                    loadMoreBtn.disabled = false;
                    loadMoreBtn.innerHTML = originalHTML;
                    showToast('Failed to load more products', 'error');
                });
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
