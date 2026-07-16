<?php
/**
 * Wishlist Page
 * NexMart E-Commerce
 */
$pageTitle = 'Wishlist';
$showBreadcrumb = true;
$breadcrumbItems = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Wishlist', 'url' => '', 'active' => true]
];

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Require login
requireLogin();

// Handle remove from wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    removeFromWishlist(intval($_POST['product_id']));
    setFlashMessage('success', 'Item removed from wishlist');
    header('Location: wishlist.php');
    exit;
}

// Handle add all to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_all_to_cart'])) {
    $wishlistItems = getWishlistItems();
    foreach ($wishlistItems as $item) {
        addToCart($item['id'], 1);
    }
    setFlashMessage('success', 'All items added to cart');
    header('Location: cart.php');
    exit;
}

// Get wishlist items
$wishlistItems = getWishlistItems();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Wishlist Section -->
<section class="wishlist-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="wishlist-card" data-aos="fade-up">
                    <div class="card-header">
                        <h2><i class="fas fa-heart me-2"></i>My Wishlist</h2>
                        <span class="badge bg-primary"><?php echo count($wishlistItems); ?> items</span>
                    </div>
                    
                    <?php if (empty($wishlistItems)): ?>
                    <div class="empty-wishlist text-center py-5">
                        <i class="fas fa-heart fa-4x text-muted mb-3"></i>
                        <h3>Your wishlist is empty</h3>
                        <p class="text-muted mb-4">Save items you love by clicking the heart icon</p>
                        <a href="products.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag me-2"></i>Browse Products
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="wishlist-items">
                        <?php foreach ($wishlistItems as $item): ?>
                        <?php 
                        $discount = calculateDiscount($item['price'], $item['compare_price']);
                        $rating = getProductRating($item['id']);
                        ?>
                        <div class="wishlist-item">
                            <div class="item-image">
                                <a href="product-details.php?id=<?php echo $item['id']; ?>">
                                    <img src="assets/images/products/<?php echo htmlspecialchars($item['image'] ?? 'placeholder.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </a>
                            </div>
                            <div class="item-details">
                                <div class="item-info">
                                    <span class="item-category"><?php echo htmlspecialchars($item['category_name'] ?? 'Electronics'); ?></span>
                                    <h4 class="item-title">
                                        <a href="product-details.php?id=<?php echo $item['id']; ?>">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                    </h4>
                                    <div class="item-rating">
                                        <?php echo generateStars($rating['rating']); ?>
                                        <span>(<?php echo $rating['count']; ?>)</span>
                                    </div>
                                    <div class="item-price">
                                        <span class="current"><?php echo formatPrice($item['price']); ?></span>
                                        <?php if ($item['compare_price'] && $item['compare_price'] > $item['price']): ?>
                                        <span class="original"><?php echo formatPrice($item['compare_price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <button class="btn btn-primary" onclick="addToCart(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                    </button>
                                    <form method="POST" action="" class="d-inline">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" name="remove_item" class="btn btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="wishlist-actions mt-4">
                        <form method="POST" action="">
                            <button type="submit" name="add_all_to_cart" class="btn btn-primary btn-lg">
                                <i class="fas fa-shopping-cart me-2"></i>Add All to Cart
                            </button>
                        </form>
                        <a href="products.php" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.wishlist-section {
    background: var(--lighter);
}

.wishlist-card {
    background: var(--white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--light);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    margin: 0;
    color: var(--dark);
}

.empty-wishlist {
    padding: 3rem 1.5rem;
}

.wishlist-items {
    padding: 1.5rem;
}

.wishlist-item {
    display: grid;
    grid-template-columns: 120px 1fr;
    gap: 1.5rem;
    padding: 1.5rem 0;
    border-bottom: 1px solid var(--light);
}

.wishlist-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 120px;
    height: 120px;
    border-radius: var(--radius);
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.item-info {
    flex: 1;
}

.item-category {
    color: var(--gray);
    font-size: 0.875rem;
    text-transform: uppercase;
}

.item-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0.5rem 0;
}

.item-title a {
    color: var(--dark);
    transition: var(--transition);
}

.item-title a:hover {
    color: var(--primary);
}

.item-rating {
    color: var(--secondary);
    font-size: 0.875rem;
}

.item-price .current {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary);
}

.item-price .original {
    font-size: 0.875rem;
    color: var(--gray);
    text-decoration: line-through;
    margin-left: 0.5rem;
}

.item-actions {
    display: flex;
    gap: 0.5rem;
}

.wishlist-actions {
    padding: 1.5rem;
    border-top: 1px solid var(--light);
    display: flex;
    gap: 1rem;
}

@media (max-width: 768px) {
    .wishlist-item {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .item-image {
        width: 100px;
        height: 100px;
    }
    
    .item-details {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .item-actions {
        width: 100%;
    }
    
    .item-actions .btn {
        flex: 1;
    }
    
    .wishlist-actions {
        flex-direction: column;
    }
    
    .wishlist-actions .btn {
        width: 100%;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
