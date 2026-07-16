<?php
/**
 * Product Details Page
 * NexMart E-Commerce
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Get product ID
$productId = intval($_GET['id'] ?? 0);

if (!$productId) {
    header('Location: products.php');
    exit;
}

// Get product details
$product = getProductById($productId);

if (!$product) {
    setFlashMessage('error', 'Product not found');
    header('Location: products.php');
    exit;
}

// Update page title and breadcrumb
$pageTitle = $product['name'];
$showBreadcrumb = true;
$breadcrumbItems = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Products', 'url' => 'products.php', 'active' => false],
    ['title' => $product['name'], 'url' => '', 'active' => true]
];

// Get product rating
$rating = getProductRating($productId);

// Get related products
$relatedProducts = getRelatedProducts($productId, $product['category_id'], 4);

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = intval($_POST['quantity'] ?? 1);
    if (addToCart($productId, $quantity)) {
        setFlashMessage('success', 'Product added to cart!');
    } else {
        setFlashMessage('error', 'Failed to add product to cart');
    }
    header('Location: product-details.php?id=' . $productId);
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Product Details Section -->
<section class="product-details py-5">
    <div class="container">
        <div class="row">
            <!-- Product Images -->
            <div class="col-lg-6 mb-4" data-aos="fade-right">
                <div class="product-gallery">
                    <div class="main-image">
                        <img src="assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" id="mainProductImage">
                    </div>
                    <div class="thumbnail-images">
                        <?php 
                        $gallery = $product['gallery'] ? explode(',', $product['gallery']) : [];
                        if (empty($gallery)) $gallery[] = $product['image'];
                        foreach ($gallery as $img): 
                        ?>
                        <div class="thumbnail" onclick="changeMainImage('<?php echo htmlspecialchars($img); ?>')">
                            <img src="assets/images/products/<?php echo htmlspecialchars($img); ?>" alt="Thumbnail">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="product-info">
                    <span class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Electronics'); ?></span>
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="product-rating mb-3">
                        <?php echo generateStars($rating['rating']); ?>
                        <span class="ms-2"><?php echo $rating['rating']; ?> out of 5 (<?php echo $rating['count']; ?> reviews)</span>
                    </div>
                    
                    <!-- Product Variants -->
                    <div class="product-variants mb-4">
                        <!-- Capacity/Storage Selector -->
                        <div class="variant-section mb-3">
                            <label class="variant-label">Storage Capacity</label>
                            <div class="variant-options">
                                <input type="radio" name="capacity" id="capacity-64" value="64GB" checked>
                                <label for="capacity-64" class="variant-option">64GB</label>
                                
                                <input type="radio" name="capacity" id="capacity-128" value="128GB">
                                <label for="capacity-128" class="variant-option">128GB</label>
                                
                                <input type="radio" name="capacity" id="capacity-256" value="256GB">
                                <label for="capacity-256" class="variant-option">256GB</label>
                                
                                <input type="radio" name="capacity" id="capacity-512" value="512GB">
                                <label for="capacity-512" class="variant-option">512GB</label>
                            </div>
                        </div>
                        
                        <!-- Color Selector -->
                        <div class="variant-section mb-3">
                            <label class="variant-label">Color</label>
                            <div class="color-options">
                                <input type="radio" name="color" id="color-black" value="Black" checked>
                                <label for="color-black" class="color-option" style="background-color: #1d1d1f;" title="Midnight Black"></label>
                                
                                <input type="radio" name="color" id="color-white" value="White">
                                <label for="color-white" class="color-option" style="background-color: #f5f5f7;" title="Starlight"></label>
                                
                                <input type="radio" name="color" id="color-blue" value="Blue">
                                <label for="color-blue" class="color-option" style="background-color: #276fbf;" title="Pacific Blue"></label>
                                
                                <input type="radio" name="color" id="color-gold" value="Gold">
                                <label for="color-gold" class="color-option" style="background-color: #fad7bd;" title="Gold"></label>
                                
                                <input type="radio" name="color" id="color-purple" value="Purple">
                                <label for="color-purple" class="color-option" style="background-color: #9f8fef;" title="Deep Purple"></label>
                            </div>
                            <div class="selected-color-name mt-2">
                                <small class="text-muted">Selected: <span id="color-name">Midnight Black</span></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="product-price-section mb-4">
                        <div class="current-price"><?php echo formatPrice($product['price']); ?></div>
                        <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                        <div class="original-price"><?php echo formatPrice($product['compare_price']); ?></div>
                        <div class="discount-badge">Save <?php echo calculateDiscount($product['price'], $product['compare_price']); ?>%</div>
                        <?php endif; ?>
                    </div>
                    
                    <p class="product-description mb-4">
                        <?php echo htmlspecialchars($product['short_description'] ?? $product['description']); ?>
                    </p>
                    
                    <?php if ($product['quantity'] > 0): ?>
                    <div class="stock-status mb-3">
                        <i class="fas fa-check-circle text-success"></i>
                        <span class="text-success">In Stock (<?php echo $product['quantity']; ?> available)</span>
                    </div>
                    <?php else: ?>
                    <div class="stock-status mb-3">
                        <i class="fas fa-times-circle text-danger"></i>
                        <span class="text-danger">Out of Stock</span>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="quantity-selector mb-4">
                            <label class="form-label">Quantity:</label>
                            <div class="d-flex align-items-center">
                                <button type="button" class="btn btn-outline-secondary minus" onclick="decrementQuantity()">-</button>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>" class="form-control mx-2 text-center" style="width: 80px;">
                                <button type="button" class="btn btn-outline-secondary plus" onclick="incrementQuantity()">+</button>
                            </div>
                        </div>
                        
                        <div class="product-actions mb-4">
                            <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg me-2" 
                                    <?php echo $product['quantity'] <= 0 ? 'disabled' : ''; ?>>
                                <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                            </button>
                            <button type="button" class="btn btn-outline btn-lg me-2" onclick="toggleWishlist(<?php echo $productId; ?>)">
                                <i class="<?php echo isInWishlist($productId) ? 'fas' : 'far'; ?> fa-heart"></i>
                            </button>
                            <button type="button" class="btn btn-outline btn-lg" onclick="addToCompare(<?php echo $productId; ?>)">
                                <i class="fas fa-exchange-alt"></i>
                            </button>
                        </div>
                    </form>
                    
                    <?php 
                    $features = $product['features'] ? explode("\n", $product['features']) : [];
                    $features = array_filter($features, 'trim'); // Remove empty lines
                    if (!empty($features)): 
                    ?>
                    <div class="product-features mb-4">
                        <h5>Key Features:</h5>
                        <ul>
                            <?php foreach ($features as $feature): ?>
                            <li><i class="fas fa-check text-success me-2"></i><?php echo htmlspecialchars($feature); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($product['brand_name']): ?>
                    <div class="product-brand mb-4">
                        <span class="text-muted">Brand:</span>
                        <a href="products.php?brand=<?php echo htmlspecialchars($product['brand_slug']); ?>" class="ms-2">
                            <?php echo htmlspecialchars($product['brand_name']); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($product['warranty']): ?>
                    <div class="product-warranty mb-4">
                        <i class="fas fa-shield-alt text-primary me-2"></i>
                        <span><?php echo htmlspecialchars($product['warranty']); ?> Warranty</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Product Tabs -->
        <div class="product-tabs mt-5" data-aos="fade-up">
            <ul class="nav nav-tabs" id="productTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button">
                        Description
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button">
                        Specifications
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button">
                        Reviews (<?php echo $rating['count']; ?>)
                    </button>
                </li>
            </ul>
            <div class="tab-content mt-3" id="productTabContent">
                <div class="tab-pane fade show active" id="description">
                    <div class="tab-content-inner">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="specifications">
                    <div class="tab-content-inner">
                        <?php 
                        $specs = $product['specifications'] ? explode("\n", $product['specifications']) : [];
                        if (!empty($specs)): 
                        ?>
                        <table class="table table-striped">
                            <?php foreach ($specs as $spec): 
                            $parts = explode(':', $spec);
                            if (count($parts) >= 2):
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars(trim($parts[0])); ?></strong></td>
                                <td><?php echo htmlspecialchars(trim(implode(':', array_slice($parts, 1)))); ?></td>
                            </tr>
                            <?php endif; 
                            endforeach; 
                            ?>
                        </table>
                        <?php else: ?>
                        <p class="text-muted">No specifications available</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="reviews">
                    <div class="tab-content-inner">
                        <?php if (isLoggedIn()): ?>
                        <div class="review-form mb-4">
                            <h5>Write a Review</h5>
                            <form action="api/add_review.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <div class="form-group mb-3">
                                    <label>Rating</label>
                                    <div class="star-rating">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                                        <label for="star<?php echo $i; ?>"><i class="far fa-star"></i></label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Review Title</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Your Review</label>
                                    <textarea class="form-control" name="comment" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Review</button>
                            </form>
                        </div>
                        <?php else: ?>
                        <p class="text-muted">Please <a href="login.php">login</a> to write a review.</p>
                        <?php endif; ?>
                        
                        <div class="reviews-list">
                            <?php
                            try {
                                $stmt = $pdo->prepare("
                                    SELECT r.*, u.name 
                                    FROM reviews r 
                                    JOIN users u ON r.user_id = u.id 
                                    WHERE r.product_id = ? AND r.status = 'approved' 
                                    ORDER BY r.created_at DESC
                                ");
                                $stmt->execute([$productId]);
                                $reviews = $stmt->fetchAll();
                                
                                foreach ($reviews as $review):
                            ?>
                            <div class="review-item mb-4 pb-4 border-bottom">
                                <div class="review-header d-flex justify-content-between mb-2">
                                    <div>
                                        <strong><?php echo htmlspecialchars($review['name']); ?></strong>
                                        <div class="review-rating">
                                            <?php echo generateStars($review['rating']); ?>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?php echo formatDate($review['created_at']); ?></small>
                                </div>
                                <?php if ($review['title']): ?>
                                <h6 class="review-title"><?php echo htmlspecialchars($review['title']); ?></h6>
                                <?php endif; ?>
                                <p class="review-text"><?php echo htmlspecialchars($review['comment']); ?></p>
                            </div>
                            <?php 
                                endforeach;
                            } catch (PDOException $e) {
                                echo '<p class="text-muted">No reviews yet.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <section class="related-products mt-5" data-aos="fade-up">
            <div class="section-header">
                <h2>Related Products</h2>
            </div>
            <div class="product-grid">
                <?php foreach ($relatedProducts as $related): ?>
                <?php 
                $relatedDiscount = calculateDiscount($related['price'], $related['compare_price']);
                $relatedRating = getProductRating($related['id']);
                ?>
                <div class="product-card">
                    <?php if ($related['is_new']): ?>
                    <span class="product-badge new">New</span>
                    <?php endif; ?>
                    <?php if ($relatedDiscount > 0): ?>
                    <span class="product-badge sale">-<?php echo $relatedDiscount; ?>%</span>
                    <?php endif; ?>
                    
                    <div class="product-image">
                        <a href="product-details.php?id=<?php echo $related['id']; ?>">
                            <img src="assets/images/products/<?php echo htmlspecialchars($related['image'] ?? 'placeholder.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>">
                        </a>
                    </div>
                    
                    <div class="product-details">
                        <span class="product-category"><?php echo htmlspecialchars($related['category_name'] ?? 'Electronics'); ?></span>
                        <h3 class="product-title">
                            <a href="product-details.php?id=<?php echo $related['id']; ?>">
                                <?php echo htmlspecialchars($related['name']); ?>
                            </a>
                        </h3>
                        <div class="product-rating">
                            <?php echo generateStars($relatedRating['rating']); ?>
                        </div>
                        <div class="product-price">
                            <span class="current"><?php echo formatPrice($related['price']); ?></span>
                        </div>
                        <button class="add-to-cart-btn" data-product-id="<?php echo $related['id']; ?>">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</section>

<style>
.product-details {
    background: var(--lighter);
}

.product-gallery {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    box-shadow: var(--shadow);
}

.main-image {
    margin-bottom: 1rem;
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.main-image img {
    width: 100%;
    height: auto;
    object-fit: cover;
    transition: transform 0.3s ease;
    cursor: zoom-in;
}

.thumbnail-images {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
}

.thumbnail {
    width: 80px;
    height: 80px;
    border-radius: var(--radius);
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: var(--transition);
    flex-shrink: 0;
}

.thumbnail:hover,
.thumbnail.active {
    border-color: var(--primary);
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-info {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: 2rem;
    box-shadow: var(--shadow);
}

.product-category {
    color: var(--gray);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.product-title {
    font-size: 2rem;
    font-weight: 700;
    margin: 0.5rem 0 1rem;
    color: var(--dark);
}

.product-rating {
    color: var(--secondary);
}

.product-price-section {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.current-price {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
}

.original-price {
    font-size: 1.25rem;
    color: var(--gray);
    text-decoration: line-through;
}

.discount-badge {
    background: var(--success);
    color: var(--white);
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius);
    font-weight: 600;
}

.stock-status {
    font-size: 1rem;
}

.product-features ul {
    list-style: none;
    padding: 0;
}

.product-features li {
    padding: 0.5rem 0;
}

.quantity-selector .btn {
    width: 40px;
    height: 40px;
}

.product-tabs {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    box-shadow: var(--shadow);
}

.tab-content-inner {
    padding: 1.5rem 0;
}

.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.star-rating input {
    display: none;
}

.star-rating label {
    font-size: 1.5rem;
    color: var(--gray);
    cursor: pointer;
    transition: var(--transition);
}

.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
    color: var(--secondary);
}

.review-item {
    padding: 1rem 0;
}

.review-rating {
    color: var(--secondary);
    font-size: 0.875rem;
}

/* Product Variants Styling */
.product-variants {
    border-top: 1px solid #e5e5e5;
    border-bottom: 1px solid #e5e5e5;
    padding: 1.5rem 0;
}

.variant-section {
    margin-bottom: 1.5rem;
}

.variant-label {
    display: block;
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.75rem;
    color: var(--dark);
}

/* Capacity/Storage Options */
.variant-options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.variant-options input[type="radio"] {
    display: none;
}

.variant-option {
    padding: 0.75rem 1.5rem;
    border: 2px solid #d1d5db;
    border-radius: 12px;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    font-size: 0.95rem;
    color: #212529;
    display: inline-block;
}

.variant-option:hover {
    border-color: #007bff;
    background: #f8f9fa;
}

.variant-options input[type="radio"]:checked + .variant-option {
    border-color: #007bff;
    background: #007bff;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
}

/* Color Options */
.color-options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.color-options input[type="radio"] {
    display: none;
}

.color-option {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    border: 3px solid transparent;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.color-option:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.color-options input[type="radio"]:checked + .color-option {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2);
    transform: scale(1.05);
}

.color-options input[type="radio"]:checked + .color-option::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: var(--white);
    font-size: 1.2rem;
    font-weight: bold;
    text-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
}

.selected-color-name {
    font-size: 0.9rem;
    color: var(--gray);
}

.selected-color-name span {
    color: var(--dark);
    font-weight: 600;
}

/* Enhanced Button Styling */
.product-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.product-actions .btn-primary {
    flex: 1;
    min-width: 200px;
    padding: 1rem 2rem;
    font-size: 1.05rem;
    font-weight: 600;
    border-radius: var(--radius-lg);
    transition: all 0.3s ease;
    background: var(--primary);
    border: 2px solid var(--primary);
}

.product-actions .btn-primary:hover:not(:disabled) {
    background: #0056b3;
    border-color: #0056b3;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 123, 255, 0.3);
}

.product-actions .btn-outline {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-lg);
    border: 2px solid #d1d5db;
    background: var(--white);
    color: var(--dark);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    padding: 0;
}

.product-actions .btn-outline:hover {
    border-color: var(--primary);
    background: var(--primary);
    color: var(--white);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
}

.product-actions .btn-outline i {
    font-size: 1.2rem;
}

/* Quantity Selector Enhancement */
.quantity-selector {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: var(--radius-lg);
}

.quantity-selector .btn {
    border-radius: var(--radius);
    font-weight: 600;
    transition: all 0.2s ease;
}

.quantity-selector .btn:hover {
    background: var(--primary);
    border-color: var(--primary);
    color: var(--white);
    transform: scale(1.05);
}

/* Product Features Enhancement */
.product-features {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: var(--radius-lg);
}

.product-features h5 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--dark);
}

.product-features ul li {
    padding: 0.75rem 0;
    border-bottom: 1px solid #e5e5e5;
    transition: all 0.2s ease;
}

.product-features ul li:last-child {
    border-bottom: none;
}

.product-features ul li:hover {
    padding-left: 0.5rem;
    color: var(--primary);
}

/* Tab Enhancement */
.product-tabs .nav-tabs {
    border-bottom: 2px solid #e5e5e5;
}

.product-tabs .nav-link {
    padding: 1rem 1.5rem;
    color: var(--gray);
    font-weight: 500;
    border: none;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.product-tabs .nav-link:hover {
    color: var(--primary);
    border-bottom-color: var(--primary);
    background: transparent;
}

.product-tabs .nav-link.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
    background: transparent;
    font-weight: 600;
}

/* Responsive Improvements */
@media (max-width: 768px) {
    .product-title {
        font-size: 1.5rem;
    }
    
    .current-price {
        font-size: 1.5rem;
    }
    
    .product-info {
        padding: 1.5rem;
    }
    
    .product-gallery {
        padding: 1rem;
    }
    
    .product-actions {
        flex-direction: column;
    }
    
    .product-actions .btn-primary {
        min-width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .product-actions .btn-outline {
        width: 100%;
        height: 45px;
    }
    
    .variant-option {
        padding: 0.65rem 1.25rem;
        font-size: 0.9rem;
    }
    
    .product-variants {
        padding: 1rem 0;
    }
    
    .variant-section {
        margin-bottom: 1rem;
    }
    
    .thumbnail {
        width: 60px;
        height: 60px;
    }
}

@media (max-width: 576px) {
    .product-price-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .current-price {
        font-size: 1.75rem;
    }
    
    .color-option {
        width: 40px;
        height: 40px;
    }
}
</style>

<script>
function changeMainImage(src) {
    document.getElementById('mainProductImage').src = 'assets/images/products/' + src;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    event.target.closest('.thumbnail').classList.add('active');
}

function incrementQuantity() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.max);
    if (parseInt(input.value) < max) {
        input.value = parseInt(input.value) + 1;
    }
}

function decrementQuantity() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

// Color variant selection handler
document.addEventListener('DOMContentLoaded', function() {
    // Color selection
    const colorOptions = document.querySelectorAll('input[name="color"]');
    const colorNameDisplay = document.getElementById('color-name');
    
    const colorNames = {
        'Black': 'Midnight Black',
        'White': 'Starlight',
        'Blue': 'Pacific Blue',
        'Gold': 'Gold',
        'Purple': 'Deep Purple'
    };
    
    colorOptions.forEach(option => {
        option.addEventListener('change', function() {
            const selectedColor = this.value;
            colorNameDisplay.textContent = colorNames[selectedColor] || selectedColor;
        });
    });
    
    // Image zoom functionality
    const mainImage = document.getElementById('mainProductImage');
    const mainImageContainer = document.querySelector('.main-image');
    
    mainImageContainer.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        
        mainImage.style.transformOrigin = `${x}% ${y}%`;
        mainImage.style.transform = 'scale(1.5)';
    });
    
    mainImageContainer.addEventListener('mouseleave', function() {
        mainImage.style.transform = 'scale(1)';
    });
    
    // Set first thumbnail as active
    const firstThumbnail = document.querySelector('.thumbnail');
    if (firstThumbnail) {
        firstThumbnail.classList.add('active');
    }
    
    // Smooth scroll for product tabs
    const tabButtons = document.querySelectorAll('#productTab button');
    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function() {
            this.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
