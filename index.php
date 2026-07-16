<?php
/**
 * Homepage
 * NexMart E-Commerce
 * 
 * Main landing page with all sections
 */
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';

// Get featured products
$featuredProducts = getFeaturedProducts(8);
$newProducts = getNewProducts(8);
$categories = getCategories();
// Use categories instead of brands for the home section
//$brands = getBrands(12);
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1 data-aos="fade-up">Welcome to NexMart</h1>
            <p data-aos="fade-up" data-aos-delay="100">Discover the Latest Electronics at Unbeatable Prices</p>
            <div class="hero-buttons" data-aos="fade-up" data-aos-delay="200">
                <a href="products.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag"></i> Shop Now
                </a>
                <a href="categories.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-th-large"></i> Browse Categories
                </a>
            </div>
        </div>
        <div class="hero-slider" data-aos="fade-left" data-aos-delay="300">
            <div class="hero-slide active">
                <img src="assets/images/hero1.jpg" alt="Featured Products">
            </div>
            <div class="hero-slide">
                <img src="assets/images/products/mac.jpg"New Arrivals">
            </div>
            <div class="hero-slide">
                <img src="assets/images/products/iphones.jpg" alt="Best Sellers">
            </div>
            <div class="hero-dots">
                <div class="hero-dot active" data-slide="0"></div>
                <div class="hero-dot" data-slide="1"></div>
                <div class="hero-dot" data-slide="2"></div>
            </div>
            <div class="discount-banner">
                <i class="fas fa-tag"></i> Up to 50% OFF
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories" data-aos="fade-up">
    <div class="container">
        <div class="section-header">
            <h2>Shop by Category</h2>
            <p>Explore our wide range of electronic devices</p>
        </div>
        <div class="category-grid">
            <?php foreach ($categories as $category): ?>
            <div class="category-card" data-aos="zoom-in" data-aos-delay="<?php echo $category['id'] * 50; ?>">
                <a href="products.php?category=<?php echo htmlspecialchars($category['slug']); ?>">
                    <i class="fas <?php echo htmlspecialchars($category['icon'] ?? 'fa-box'); ?>"></i>
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p><?php echo htmlspecialchars($category['description'] ?? 'View products'); ?></p>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="products-section" data-aos="fade-up">
    <div class="container">
        <div class="section-header">
            <h2>Featured Products</h2>
            <p>Handpicked products just for you</p>
        </div>
        <div class="product-grid">
            <?php foreach ($featuredProducts as $product): ?>
            <?php 
            $discount = calculateDiscount($product['price'], $product['compare_price']);
            $rating = getProductRating($product['id']);
            ?>
            <div class="product-card" data-aos="fade-up" data-aos-delay="<?php echo $product['id'] * 30; ?>">
                <?php if ($product['is_new']): ?>
                <span class="product-badge new">New</span>
                <?php endif; ?>
                <?php if ($discount > 0): ?>
                <span class="product-badge sale">-<?php echo $discount; ?>%</span>
                <?php endif; ?>
                <?php if ($product['is_featured']): ?>
                <span class="product-badge featured">Featured</span>
                <?php endif; ?>
                
                <div class="product-image">
                    <a href="product-details.php?id=<?php echo $product['id']; ?>">
                        <img src="assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
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
                        <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
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
        <div class="text-center mt-4">
            <a href="products.php" class="btn btn-outline btn-lg">View All Products</a>
        </div>
    </div>
</section>

<!-- Flash Sale Section -->
<section class="flash-sale" id="deals" data-aos="fade-up">
    <div class="container">
        <div class="flash-sale-header">
            <div>
                <h2><i class="fas fa-bolt"></i> Flash Sale</h2>
                <p>Limited time offers - Don't miss out!</p>
            </div>
            <div class="countdown-timer">
                <div class="countdown-item">
                    <span class="number" id="hours">00</span>
                    <span class="label">Hours</span>
                </div>
                <div class="countdown-item">
                    <span class="number" id="minutes">00</span>
                    <span class="label">Mins</span>
                </div>
                <div class="countdown-item">
                    <span class="number" id="seconds">00</span>
                    <span class="label">Secs</span>
                </div>
            </div>
        </div>
        
        <div class="product-grid">
            <?php 
            $flashSaleProducts = array_slice($featuredProducts, 0, 4);
            foreach ($flashSaleProducts as $product): 
            $discount = calculateDiscount($product['price'], $product['compare_price']);
            ?>
            <div class="product-card" data-aos="zoom-in" data-aos-delay="<?php echo $product['id'] * 50; ?>">
                <span class="product-badge sale">-<?php echo $discount > 0 ? $discount : 20; ?>%</span>
                <div class="product-image">
                    <a href="product-details.php?id=<?php echo $product['id']; ?>">
                        <img src="assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </a>
                </div>
                <div class="product-details">
                    <span class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Electronics'); ?></span>
                    <h3 class="product-title">
                        <a href="product-details.php?id=<?php echo $product['id']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </h3>
                    <div class="product-price">
                        <span class="current"><?php echo formatPrice($product['price']); ?></span>
                        <?php if ($product['compare_price']): ?>
                        <span class="original"><?php echo formatPrice($product['compare_price']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-buttons">
                        <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
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
    </div>
</section>

<!-- New Arrivals Section -->
<section class="products-section" data-aos="fade-up">
    <div class="container">
        <div class="section-header">
            <h2>New Arrivals</h2>
            <p>Fresh from the factory - Latest products</p>
        </div>
        <div class="product-grid">
            <?php foreach ($newProducts as $product): ?>
            <?php 
            $discount = calculateDiscount($product['price'], $product['compare_price']);
            $rating = getProductRating($product['id']);
            ?>
            <div class="product-card" data-aos="fade-up" data-aos-delay="<?php echo $product['id'] * 30; ?>">
                <span class="product-badge new">New</span>
                <?php if ($discount > 0): ?>
                <span class="product-badge sale">-<?php echo $discount; ?>%</span>
                <?php endif; ?>
                
                <div class="product-image">
                    <a href="product-details.php?id=<?php echo $product['id']; ?>">
                        <img src="assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
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
                        <?php if ($product['compare_price']): ?>
                        <span class="original"><?php echo formatPrice($product['compare_price']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-buttons">
                        <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
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
    </div>
</section>

<!-- Category Showcase Section -->
<section class="brands" data-aos="fade-up">
    <div class="container">
        <div class="section-header">
            <h2>Shop by Category</h2>
            <p>Products from top categories you trust</p>
        </div>
        <div class="brand-grid">
            <?php foreach ($categories as $category): ?>
            <div class="brand-card" data-aos="zoom-in" data-aos-delay="<?php echo $category['id'] * 30; ?>">
                <a href="products.php?category=<?php echo htmlspecialchars($category['slug']); ?>" title="<?php echo htmlspecialchars($category['name']); ?>">
                    <img src="assets/images/<?php echo htmlspecialchars($category['image'] ?? 'products/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features/Benefits Section -->
<section class="about" data-aos="fade-up">
    <div class="container">
        <div class="section-header">
            <h2>Why Choose NexMart?</h2>
            <p>We provide the best shopping experience</p>
        </div>
        <div class="features-grid">
            <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                <i class="fas fa-shipping-fast"></i>
                <h3>Fast Shipping</h3>
                <p>Free delivery on orders over $50. Get your products in 2-3 business days.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                <i class="fas fa-shield-alt"></i>
                <h3>Secure Payment</h3>
                <p>100% secure payment methods. Your data is always protected with SSL encryption.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                <i class="fas fa-undo"></i>
                <h3>Easy Returns</h3>
                <p>30-day return policy. Not satisfied? Return it for a full refund.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                <i class="fas fa-headset"></i>
                <h3>24/7 Support</h3>
                <p>Round-the-clock customer service. We're here to help you anytime.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                <i class="fas fa-certificate"></i>
                <h3>Quality Guarantee</h3>
                <p>All products are 100% authentic and come with manufacturer warranty.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="600">
                <i class="fas fa-tags"></i>
                <h3>Best Prices</h3>
                <p>Competitive pricing with regular discounts and special offers.</p>
            </div>
        </div>
    </div>
</section>

<!-- Customer Reviews/Testimonials Section -->
<section class="testimonials" data-aos="fade-up">
    <div class="container">
        <div class="section-header">
            <h2>What Our Customers Say</h2>
            <p>Real reviews from real customers</p>
        </div>
        <div class="testimonial-slider">
            <div class="testimonial-track">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="testimonial-avatar">
                            <img src="assets/images/user1.png" alt="Customer">
                        </div>
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"Amazing shopping experience! The products are genuine and the delivery was super fast. Will definitely shop again!"</p>
                        <h4 class="testimonial-author">John Doe</h4>
                        <p class="testimonial-role">Verified Buyer</p>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="testimonial-avatar">
                            <img src="assets/images/user2.png" alt="Customer">
                        </div>
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <p class="testimonial-text">"Best prices I found anywhere. The customer service team was very helpful when I had questions about my order."</p>
                        <h4 class="testimonial-author">Jane Smith</h4>
                        <p class="testimonial-role">Verified Buyer</p>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="testimonial-avatar">
                            <img src="assets/images/user3.png" alt="Customer">
                        </div>
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"I've been shopping at NexMart for over a year now. Always satisfied with the quality and service. Highly recommended!"</p>
                        <h4 class="testimonial-author">Mike Johnson</h4>
                        <p class="testimonial-role">Verified Buyer</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter" data-aos="fade-up">
    <div class="container">
        <h2><i class="fas fa-envelope"></i> Subscribe to Our Newsletter</h2>
        <p>Get the latest deals, new arrivals, and exclusive offers delivered to your inbox</p>
        <form class="newsletter-form" action="api/newsletter.php" method="POST">
            <input type="email" name="email" placeholder="Enter your email address" required>
            <button type="submit">
                <i class="fas fa-paper-plane"></i> Subscribe
            </button>
        </form>
        <p class="text-muted mt-3 small">We respect your privacy. Unsubscribe anytime.</p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
