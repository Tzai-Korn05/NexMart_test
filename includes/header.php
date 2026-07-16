<?php
/**
 * Header File
 * NexMart E-Commerce
 * 
 * Includes navigation, search, cart, and user menu
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Get cart and wishlist counts
$cartCount = getCartCount();
$wishlistCount = getWishlistCount();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NexMart - Your One-Stop Electronics Store. Shop the latest smartphones, laptops, tablets, and accessories at unbeatable prices.">
    <meta name="keywords" content="electronics, smartphones, laptops, tablets, accessories, online store, NexMart">
    <meta name="author" content="NexMart">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>NexMart - Premium Electronics Store</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo baseUrl('assets/images/favicon.ico'); ?>">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Swiper.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo baseUrl('assets/css/style.css'); ?>">
    
    <!-- Admin Panel CSS -->
    <?php if (isset($isAdmin) && $isAdmin): ?>
    <link rel="stylesheet" href="<?php echo baseUrl('admin/assets/css/admin-styles.css?v=' . time()); ?>">
    <?php endif; ?>
    
    <!-- Chart.js (for admin dashboard) -->
    <?php if (isset($isAdmin) && $isAdmin): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
</head>
<body class="nexmart-body">
    <?php if (!isset($isAdmin) || !$isAdmin): ?>
    <div class="loading-screen">
        <div class="loader"></div>
    </div>

    <!-- Back to Top Button -->
    <button class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Dark Mode Toggle -->
    <button class="dark-mode-toggle">
        <i class="fas fa-moon"></i>
    </button>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <!-- Brand -->
            <a href="<?php echo baseUrl('index.php'); ?>" class="navbar-brand">
                <i class="fas fa-bolt"></i>
                <span>NexMart</span>
            </a>

            <!-- Navigation Links -->
            <ul class="navbar-nav">
                <li><a href="<?php echo baseUrl('index.php'); ?>" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="<?php echo baseUrl('products.php'); ?>" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">Shop</a></li>
                <li><a href="<?php echo baseUrl('categories.php'); ?>" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">Categories</a></li>
                <li><a href="<?php echo baseUrl('index.php#deals'); ?>" class="nav-link">Deals</a></li>
                <li><a href="<?php echo baseUrl('about.php'); ?>" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">About</a></li>
                <li><a href="<?php echo baseUrl('contact.php'); ?>" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
                <?php if (isAdmin()): ?>
                <li><a href="<?php echo baseUrl('admin/dashboard.php'); ?>" class="nav-link">Admin</a></li>
                <?php endif; ?>
            </ul>

            <!-- Navbar Actions -->
            <div class="navbar-actions">
                <!-- Search -->
                <div class="navbar-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search products..." id="searchInput">
                </div>

                <!-- Wishlist -->
                <a href="<?php echo baseUrl('wishlist.php'); ?>" class="navbar-icon-btn">
                    <i class="far fa-heart"></i>
                    <span class="badge wishlist-count"><?php echo $wishlistCount; ?></span>
                </a>

                <!-- Cart -->
                <a href="<?php echo baseUrl('cart.php'); ?>" class="navbar-icon-btn cart-btn">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="badge cart-count"><?php echo $cartCount; ?></span>
                </a>

                <!-- User Menu -->
                <?php if (isLoggedIn()): ?>
                    <div class="dropdown">
                        <button class="navbar-icon-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Hello, <?php echo htmlspecialchars($currentUser['name']); ?></h6></li>
                            <li><a class="dropdown-item" href="<?php echo baseUrl('profile.php'); ?>"><i class="fas fa-user me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo baseUrl('orders.php'); ?>"><i class="fas fa-box me-2"></i>My Orders</a></li>
                            <li><a class="dropdown-item" href="<?php echo baseUrl('wishlist.php'); ?>"><i class="fas fa-heart me-2"></i>Wishlist</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo baseUrl('logout.php'); ?>"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo baseUrl('login.php'); ?>" class="navbar-icon-btn">
                        <i class="fas fa-user"></i>
                    </a>
                <?php endif; ?>

                <!-- Mobile Menu Toggle -->
                <button class="navbar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Cart Sidebar -->
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h3>Your Cart</h3>
            <button class="cart-close" onclick="closeCartSidebar()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="cart-items" id="cartItems">
            <?php
            $cartItems = getCartItems();
            if (empty($cartItems)):
            ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Your cart is empty</p>
                    <a href="<?php echo baseUrl('products.php'); ?>" class="btn btn-primary mt-3">Start Shopping</a>
                </div>
            <?php else: ?>
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <img src="<?php echo baseUrl('assets/images/products/' . htmlspecialchars($item['image'] ?? 'placeholder.jpg')); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="cart-item-details">
                            <h4 class="cart-item-title"><?php echo htmlspecialchars($item['name']); ?></h4>
                            <p class="cart-item-price"><?php echo formatPrice($item['price']); ?></p>
                            <div class="cart-item-quantity">
                                <button class="quantity-btn" onclick="updateCartQuantity(<?php echo $item['id']; ?>, -1)">-</button>
                                <span><?php echo $item['quantity']; ?></span>
                                <button class="quantity-btn" onclick="updateCartQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                                <button class="cart-item-remove" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php if (!empty($cartItems)): ?>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Total:</span>
                <span class="total"><?php echo formatPrice(getCartTotal()); ?></span>
            </div>
            <a href="<?php echo baseUrl('cart.php'); ?>" class="cart-checkout-btn">View Cart</a>
            <a href="<?php echo baseUrl('checkout.php'); ?>" class="cart-checkout-btn mt-2" style="background: var(--secondary);">Checkout</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <!-- Flash Messages -->
    <div class="container mt-3">
        <?php displayFlashMessage(); ?>
    </div>

    <!-- Breadcrumb (if enabled) -->
    <?php if (isset($showBreadcrumb) && $showBreadcrumb): ?>
    <nav class="breadcrumb">
        <div class="container">
            <ol class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="<?php echo baseUrl('index.php'); ?>">Home</a></li>
                <?php if (isset($breadcrumbItems)): ?>
                    <?php foreach ($breadcrumbItems as $item): ?>
                        <li class="breadcrumb-item <?php echo $item['active'] ? 'active' : ''; ?>">
                            <?php if ($item['active']): ?>
                                <?php echo htmlspecialchars($item['title']); ?>
                            <?php else: ?>
                                <a href="<?php echo htmlspecialchars($item['url']); ?>"><?php echo htmlspecialchars($item['title']); ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ol>
        </div>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
