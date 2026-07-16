<?php
/**
 * Categories Page
 * NexMart E-Commerce
 */
$pageTitle = 'Categories';
$showBreadcrumb = true;
$breadcrumbItems = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Categories', 'url' => '', 'active' => true]
];

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Get all categories
$categories = getCategories();

// Get subcategories for each category
foreach ($categories as &$category) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id = ? AND status = 'active' ORDER BY sort_order ASC");
        $stmt->execute([$category['id']]);
        $category['subcategories'] = $stmt->fetchAll();
        
        // Get product count for each category
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ? AND status = 'active'");
        $stmt->execute([$category['id']]);
        $category['product_count'] = $stmt->fetch()['count'];
        
        // Get product count for subcategories
        foreach ($category['subcategories'] as &$subcat) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ? AND status = 'active'");
            $stmt->execute([$subcat['id']]);
            $subcat['product_count'] = $stmt->fetch()['count'];
        }
    } catch (PDOException $e) {
        $category['subcategories'] = [];
        $category['product_count'] = 0;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Categories Section -->
<section class="categories-page py-5">
    <div class="container">
        <div class="page-header text-center mb-5" data-aos="fade-up">
            <h1>Browse Categories</h1>
            <p class="text-muted">Explore our wide range of electronic devices</p>
        </div>
        
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
            <div class="category-card-large" data-aos="fade-up" data-aos-delay="<?php echo $category['id'] * 50; ?>">
                <div class="category-image">
                    <img src="assets/images/<?php echo htmlspecialchars($category['image'] ?? 'products/placeholder.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($category['name']); ?>">
                    <div class="category-overlay">
                        <i class="fas <?php echo htmlspecialchars($category['icon'] ?? 'fa-box'); ?>"></i>
                    </div>
                </div>
                <div class="category-content">
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p class="text-muted"><?php echo htmlspecialchars($category['description'] ?? ''); ?></p>
                    <span class="product-count"><?php echo $category['product_count']; ?> Products</span>
                    
                    <?php if (!empty($category['subcategories'])): ?>
                    <div class="subcategories">
                        <h5>Subcategories:</h5>
                        <ul>
                            <?php foreach ($category['subcategories'] as $subcat): ?>
                            <li>
                                <a href="products.php?category=<?php echo htmlspecialchars($subcat['slug']); ?>">
                                    <?php echo htmlspecialchars($subcat['name']); ?>
                                    <span class="count">(<?php echo $subcat['product_count']; ?>)</span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <a href="products.php?category=<?php echo htmlspecialchars($category['slug']); ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-right me-2"></i>View Products
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Featured Categories -->
        <div class="featured-categories mt-5" data-aos="fade-up">
            <h2 class="text-center mb-4">Featured Categories</h2>
            <div class="featured-grid">
                <?php 
                $featuredCats = array_slice($categories, 0, 4);
                foreach ($featuredCats as $category): 
                ?>
                <div class="featured-category">
                    <a href="products.php?category=<?php echo htmlspecialchars($category['slug']); ?>">
                        <img src="assets/images/<?php echo htmlspecialchars($category['image'] ?? 'products/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($category['name']); ?>">
                        <div class="featured-overlay">
                            <h4><?php echo htmlspecialchars($category['name']); ?></h4>
                            <span><?php echo $category['product_count']; ?> Products</span>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<style>
.categories-page {
    background: var(--lighter);
}

.page-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

.category-card-large {
    background: var(--white);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.category-card-large:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-xl);
}

.category-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.category-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.category-card-large:hover .category-image img {
    transform: scale(1.1);
}

.category-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(37, 99, 235, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition);
}

.category-card-large:hover .category-overlay {
    opacity: 1;
}

.category-overlay i {
    font-size: 4rem;
    color: var(--white);
}

.category-content {
    padding: 1.5rem;
}

.category-content h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.category-content p {
    color: var(--gray);
    margin-bottom: 1rem;
}

.product-count {
    display: inline-block;
    background: var(--primary);
    color: var(--white);
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius);
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.subcategories {
    margin: 1rem 0;
    padding: 1rem 0;
    border-top: 1px solid var(--light);
    border-bottom: 1px solid var(--light);
}

.subcategories h5 {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray);
    margin-bottom: 0.75rem;
    text-transform: uppercase;
}

.subcategories ul {
    list-style: none;
    padding: 0;
}

.subcategories li {
    margin-bottom: 0.5rem;
}

.subcategories li a {
    color: var(--dark);
    text-decoration: none;
    transition: var(--transition);
    display: flex;
    justify-content: space-between;
}

.subcategories li a:hover {
    color: var(--primary);
}

.subcategories li a .count {
    color: var(--gray);
    font-size: 0.875rem;
}

.featured-categories {
    margin-top: 4rem;
}

.featured-categories h2 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark);
}

.featured-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.featured-category {
    position: relative;
    border-radius: var(--radius-xl);
    overflow: hidden;
    height: 200px;
}

.featured-category img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.featured-category:hover img {
    transform: scale(1.1);
}

.featured-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 1.5rem;
    color: var(--white);
}

.featured-overlay h4 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}

.featured-overlay span {
    font-size: 0.875rem;
    opacity: 0.9;
}

@media (max-width: 768px) {
    .categories-grid {
        grid-template-columns: 1fr;
    }
    
    .featured-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .featured-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
