/**
 * NexMart E-Commerce JavaScript
 * Main functionality for the website
 */

// ===== Global Variables =====
let cart = [];
let wishlist = [];

// ===== DOM Content Loaded =====
document.addEventListener('DOMContentLoaded', function() {
    // Initialize loading screen
    hideLoadingScreen();
    
    // Initialize components
    initNavbar();
    initHeroSlider();
    initFlashSaleCountdown();
    initTestimonialSlider();
    initDarkMode();
    initBackToTop();
    initProductActions();
    initCartSidebar();
    initSearch();
    initNewsletter();
    initAOS();
    
    // Do not override server-rendered badge values on load.
});

// ===== Loading Screen =====
function hideLoadingScreen() {
    setTimeout(() => {
        const loadingScreen = document.querySelector('.loading-screen');
        if (loadingScreen) {
            loadingScreen.classList.add('hidden');
            setTimeout(() => {
                loadingScreen.style.display = 'none';
            }, 500);
        }
    }, 1000);
}

// ===== Navbar =====
function initNavbar() {
    const navbar = document.querySelector('.navbar');
    const navbarToggle = document.querySelector('.navbar-toggle');
    const navbarNav = document.querySelector('.navbar-nav');
    
    // Scroll effect
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Mobile menu toggle
    if (navbarToggle) {
        navbarToggle.addEventListener('click', () => {
            navbarNav.classList.toggle('active');
        });
    }
    
    // Close mobile menu on link click
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navbarNav.classList.remove('active');
        });
    });
}

// ===== Hero Slider =====
function initHeroSlider() {
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    let currentSlide = 0;
    let slideInterval;
    
    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.remove('active');
            dots[i].classList.remove('active');
        });
        
        slides[index].classList.add('active');
        dots[index].classList.add('active');
        currentSlide = index;
    }
    
    function nextSlide() {
        const next = (currentSlide + 1) % slides.length;
        showSlide(next);
    }
    
    function prevSlide() {
        const prev = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(prev);
    }
    
    // Auto slide
    if (slides.length > 0) {
        slideInterval = setInterval(nextSlide, 5000);
        
        // Dot navigation
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                clearInterval(slideInterval);
                showSlide(index);
                slideInterval = setInterval(nextSlide, 5000);
            });
        });
    }
}

// ===== Flash Sale Countdown =====
function initFlashSaleCountdown() {
    const countdownElements = document.querySelectorAll('.countdown-timer');
    
    countdownElements.forEach(timer => {
        // Set end date to 24 hours from now
        const endDate = new Date();
        endDate.setHours(endDate.getHours() + 24);
        
        function updateCountdown() {
            const now = new Date();
            const diff = endDate - now;
            
            if (diff <= 0) {
                timer.innerHTML = '<div class="text-center">Sale Ended!</div>';
                return;
            }
            
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            timer.innerHTML = `
                <div class="countdown-item">
                    <span class="number">${String(hours).padStart(2, '0')}</span>
                    <span class="label">Hours</span>
                </div>
                <div class="countdown-item">
                    <span class="number">${String(minutes).padStart(2, '0')}</span>
                    <span class="label">Mins</span>
                </div>
                <div class="countdown-item">
                    <span class="number">${String(seconds).padStart(2, '0')}</span>
                    <span class="label">Secs</span>
                </div>
            `;
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
    });
}

// ===== Testimonial Slider =====
function initTestimonialSlider() {
    const track = document.querySelector('.testimonial-track');
    const cards = document.querySelectorAll('.testimonial-card');
    let currentTestimonial = 0;
    
    if (!track || cards.length === 0) return;
    
    function showTestimonial(index) {
        const translateX = -index * 100;
        track.style.transform = `translateX(${translateX}%)`;
        currentTestimonial = index;
    }
    
    function nextTestimonial() {
        const next = (currentTestimonial + 1) % cards.length;
        showTestimonial(next);
    }
    
    // Auto slide testimonials
    setInterval(nextTestimonial, 5000);
}

// ===== Dark Mode =====
function initDarkMode() {
    const toggle = document.querySelector('.dark-mode-toggle');
    const savedTheme = localStorage.getItem('theme') || 'light';
    
    // Apply saved theme
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    if (toggle) {
        toggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update icon
            toggle.innerHTML = newTheme === 'light' ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
        });
        
        // Set initial icon
        toggle.innerHTML = savedTheme === 'light' ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
    }
}

// ===== Back to Top =====
function initBackToTop() {
    const backToTop = document.querySelector('.back-to-top');
    
    if (!backToTop) return;
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    });
    
    backToTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// ===== Product Actions =====
function initProductActions() {
    // Add to cart buttons
    const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            addToCart(productId);
        });
    });
    
    // Wishlist buttons
    const wishlistBtns = document.querySelectorAll('.wishlist-btn');
    wishlistBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            toggleWishlist(productId);
        });
    });
    
    // Quick view buttons
    const quickViewBtns = document.querySelectorAll('.quick-view-btn');
    quickViewBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            showQuickView(productId);
        });
    });
    
    // Compare buttons
    const compareBtns = document.querySelectorAll('.compare-btn');
    compareBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            addToCompare(productId);
        });
    });
}

// ===== Cart Functions =====
function loadCart() {
    // No client-side cart persistence is required when using server cart APIs.
    cart = [];
}

function saveCart() {
    // Placeholder for backwards compatibility.
}

function getApiUrl(path) {
    const baseUrl = window.NEXMART_BASE_URL || '';
    return `${baseUrl.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
}

async function addToCart(productId) {
    try {
        const response = await fetch(getApiUrl('api/add_to_cart.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ product_id: parseInt(productId, 10), quantity: 1 })
        });
        const result = await response.json();

        if (!result.success) {
            showNotification(result.message || 'Unable to add product to cart', 'error');
            return;
        }

        updateCartCount(result.cartCount);
        showNotification(result.message, 'success');
        fetchCartItems();
        // Cart sidebar removed - items now add instantly without opening sidebar
    } catch (error) {
        console.error('Add to cart error:', error);
        showNotification('An error occurred while adding to cart', 'error');
    }
}

async function removeFromCart(productId) {
    try {
        const response = await fetch(getApiUrl('api/remove_cart_item.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ product_id: parseInt(productId, 10) })
        });
        const result = await response.json();

        if (!result.success) {
            showNotification(result.message || 'Unable to remove item', 'error');
            return;
        }

        updateCartCount(result.cartCount);
        showNotification(result.message, 'success');
        fetchCartItems();
    } catch (error) {
        console.error('Remove from cart error:', error);
        showNotification('An error occurred while removing from cart', 'error');
    }
}

async function updateCartQuantity(productId, change) {
    const itemQuantityEl = document.querySelector(`input[name="quantities[${productId}]"]`);
    const currentQuantity = itemQuantityEl ? parseInt(itemQuantityEl.value, 10) : 1;
    const newQuantity = currentQuantity + change;

    if (newQuantity <= 0) {
        return removeFromCart(productId);
    }

    try {
        const response = await fetch(getApiUrl('api/update_cart_item.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ product_id: parseInt(productId, 10), quantity: newQuantity })
        });
        const result = await response.json();

        if (!result.success) {
            showNotification(result.message || 'Unable to update quantity', 'error');
            return;
        }

        updateCartCount(result.cartCount);
        
        // Check if we're on the cart page
        if (window.location.pathname.includes('cart.php')) {
            // Update the quantity input field
            if (itemQuantityEl) {
                itemQuantityEl.value = newQuantity;
            }
            
            // Fetch and update cart page totals without reload
            await updateCartPageTotals(productId, newQuantity);
            
            showNotification('Cart updated', 'success');
        } else {
            // Update cart sidebar on other pages
            fetchCartItems();
        }
    } catch (error) {
        console.error('Update cart quantity error:', error);
        showNotification('An error occurred while updating cart', 'error');
    }
}

async function updateCartPageTotals(productId, newQuantity) {
    try {
        // Get updated cart data from server
        const response = await fetch(getApiUrl('api/get_cart_items.php'));
        const cartData = await response.json();
        
        if (!cartData || !Array.isArray(cartData)) return;
        
        // Update individual item subtotal
        const cartItem = cartData.find(item => item.id == productId);
        if (cartItem) {
            const itemSubtotal = cartItem.price * newQuantity;
            const subtotalEl = document.querySelector(`.cart-item input[name="quantities[${productId}]"]`)
                ?.closest('.cart-item')
                ?.querySelector('.cart-item-subtotal strong');
            if (subtotalEl) {
                subtotalEl.textContent = formatCurrency(itemSubtotal);
            }
        }
        
        // Calculate totals
        let subtotal = cartData.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        let shippingCost = subtotal > 50 ? 0 : 9.99;
        let taxRate = 0.08;
        let tax = subtotal * taxRate;
        let total = subtotal + shippingCost + tax;
        
        // Update summary elements
        const summaryRows = document.querySelectorAll('.summary-row');
        summaryRows.forEach(row => {
            const label = row.querySelector('span:first-child')?.textContent.toLowerCase();
            const valueEl = row.querySelector('span:last-child');
            
            if (!valueEl) return;
            
            if (label?.includes('subtotal')) {
                valueEl.textContent = formatCurrency(subtotal);
            } else if (label?.includes('shipping')) {
                valueEl.innerHTML = shippingCost === 0 ? '<span class="text-success">FREE</span>' : formatCurrency(shippingCost);
            } else if (label?.includes('tax')) {
                valueEl.textContent = formatCurrency(tax);
            } else if (label?.includes('total')) {
                valueEl.textContent = formatCurrency(total);
            }
        });
        
    } catch (error) {
        console.error('Error updating cart totals:', error);
    }
}

function formatCurrency(amount) {
    return '$' + amount.toFixed(2);
}

function updateCartCount(count = null) {
    if (count === null) {
        count = cart.reduce((sum, item) => sum + item.quantity, 0);
    }

    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(el => {
        el.textContent = count;
    });
}

function updateCartUI() {
    const cartItemsContainer = document.querySelector('.cart-items');
    const cartTotalElement = document.querySelector('.cart-total .total');
    
    if (!cartItemsContainer) return;
    
    fetchCartItems();
}

async function fetchCartItems() {
    const cartItemsContainer = document.querySelector('.cart-items');
    
    try {
        // In a real app, this would be an AJAX call to the server
        // For demo, we'll use a mock response
        const response = await fetch(getApiUrl('api/get_cart_items.php'));
        const items = await response.json();
        
        let html = '';
        let total = 0;
        
        items.forEach(item => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            
            html += `
                <div class="cart-item">
                    <div class="cart-item-image">
                        <img src="assets/images/products/${item.image}" alt="${item.name}">
                    </div>
                    <div class="cart-item-details">
                        <h4 class="cart-item-title">${item.name}</h4>
                        <p class="cart-item-price">$${item.price.toFixed(2)}</p>
                        <div class="cart-item-quantity">
                            <button class="quantity-btn" onclick="updateCartQuantity(${item.id}, -1)">-</button>
                            <span>${item.quantity}</span>
                            <button class="quantity-btn" onclick="updateCartQuantity(${item.id}, 1)">+</button>
                            <button class="cart-item-remove" onclick="removeFromCart(${item.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        cartItemsContainer.innerHTML = html;
        
        const cartTotalElement = document.querySelector('.cart-total .total');
        if (cartTotalElement) {
            cartTotalElement.textContent = `$${total.toFixed(2)}`;
        }
    } catch (error) {
        console.error('Error fetching cart items:', error);
    }
}

// ===== Cart Sidebar =====
function initCartSidebar() {
    const cartBtn = document.querySelector('.cart-btn');
    const cartSidebar = document.querySelector('.cart-sidebar');
    const overlay = document.querySelector('.overlay');
    const closeBtn = document.querySelector('.cart-close');
    
    if (!cartBtn || !cartSidebar) return;
    
    cartBtn.addEventListener('click', openCartSidebar);
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeCartSidebar);
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeCartSidebar);
    }
}

function openCartSidebar() {
    const cartSidebar = document.querySelector('.cart-sidebar');
    const overlay = document.querySelector('.overlay');
    
    if (cartSidebar) cartSidebar.classList.add('active');
    if (overlay) overlay.classList.add('active');
    
    document.body.style.overflow = 'hidden';
}

function closeCartSidebar() {
    const cartSidebar = document.querySelector('.cart-sidebar');
    const overlay = document.querySelector('.overlay');
    
    if (cartSidebar) cartSidebar.classList.remove('active');
    if (overlay) overlay.classList.remove('active');
    
    document.body.style.overflow = '';
}

// ===== Wishlist Functions =====
function loadWishlist() {
    const savedWishlist = localStorage.getItem('nexmart_wishlist');
    if (savedWishlist) {
        wishlist = JSON.parse(savedWishlist);
    }
}

function saveWishlist() {
    localStorage.setItem('nexmart_wishlist', JSON.stringify(wishlist));
}

async function toggleWishlist(productId) {
    try {
        const response = await fetch(getApiUrl('api/toggle_wishlist.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ product_id: parseInt(productId, 10) })
        });
        const result = await response.json();

        if (!result.success) {
            if (result.loginRequired) {
                showNotification('Please log in to manage your wishlist', 'warning');
                return;
            }
            showNotification(result.message || 'Unable to update wishlist', 'error');
            return;
        }

        updateWishlistCount(result.wishlistCount);
        updateWishlistButtons(result.wishlisted ? [parseInt(productId, 10)] : []);
        showNotification(result.message, 'success');
    } catch (error) {
        console.error('Wishlist toggle error:', error);
        showNotification('An error occurred while updating wishlist', 'error');
    }
}

function updateWishlistCount(count = null) {
    if (count === null) {
        count = wishlist.length;
    }
    const wishlistCountElements = document.querySelectorAll('.wishlist-count');
    wishlistCountElements.forEach(el => {
        el.textContent = count;
    });
}

function updateWishlistButtons(wishlistedProductIds = []) {
    const wishlistBtns = document.querySelectorAll('.wishlist-btn');
    wishlistBtns.forEach(btn => {
        const productId = parseInt(btn.dataset.productId, 10);
        if (wishlistedProductIds.length > 0) {
            if (wishlistedProductIds.includes(productId)) {
                btn.classList.add('wishlisted');
                btn.innerHTML = '<i class="fas fa-heart"></i>';
            } else {
                btn.classList.remove('wishlisted');
                btn.innerHTML = '<i class="far fa-heart"></i>';
            }
        } else {
            // If no product IDs are passed, preserve existing server-side state as much as possible.
            if (btn.classList.contains('wishlisted')) {
                btn.innerHTML = '<i class="fas fa-heart"></i>';
            } else {
                btn.innerHTML = '<i class="far fa-heart"></i>';
            }
        }
    });
}

// ===== Search =====
function initSearch() {
    const searchInput = document.querySelector('.navbar-search input');
    const searchBtn = document.querySelector('.navbar-search button');
    
    if (!searchInput) return;
    
    // Live search
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                performLiveSearch(query);
            }, 300);
        }
    });
    
    // Search on enter
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (query) {
                window.location.href = `search.php?q=${encodeURIComponent(query)}`;
            }
        }
    });
}

async function performLiveSearch(query) {
    const resultsContainer = document.querySelector('.search-results');
    
    if (!resultsContainer) return;
    
    try {
        const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}`);
        const results = await response.json();
        
        if (results.length > 0) {
            let html = '<div class="search-results-list">';
            results.forEach(product => {
                html += `
                    <a href="product-details.php?id=${product.id}" class="search-result-item">
                        <img src="assets/images/products/${product.image}" alt="${product.name}">
                        <div>
                            <h4>${product.name}</h4>
                            <p class="price">$${product.price.toFixed(2)}</p>
                        </div>
                    </a>
                `;
            });
            html += '</div>';
            resultsContainer.innerHTML = html;
            resultsContainer.style.display = 'block';
        } else {
            resultsContainer.innerHTML = '<p class="text-center text-muted">No products found</p>';
            resultsContainer.style.display = 'block';
        }
    } catch (error) {
        console.error('Search error:', error);
    }
}

// ===== Newsletter =====
function initNewsletter() {
    const newsletterForm = document.querySelector('.newsletter-form');
    
    if (!newsletterForm) return;
    
    newsletterForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const email = this.querySelector('input[type="email"]').value;
        
        try {
            const response = await fetch('api/newsletter.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email: email })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('Thank you for subscribing!', 'success');
                this.reset();
            } else {
                showNotification(result.message || 'Subscription failed', 'error');
            }
        } catch (error) {
            console.error('Newsletter error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        }
    });
}

// ===== AOS Animation =====
function initAOS() {
    // Initialize AOS if library is loaded
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    }
}

// ===== Quick View Modal =====
function showQuickView(productId) {
    // Create modal with product details
    const modal = document.createElement('div');
    modal.className = 'quick-view-modal';
    modal.innerHTML = `
        <div class="modal-overlay" onclick="closeQuickView()"></div>
        <div class="modal-content">
            <button class="modal-close" onclick="closeQuickView()">
                <i class="fas fa-times"></i>
            </button>
            <div class="quick-view-content">
                <!-- Product details will be loaded here -->
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    // Load product details
    loadQuickViewContent(productId);
}

function closeQuickView() {
    const modal = document.querySelector('.quick-view-modal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
}

async function loadQuickViewContent(productId) {
    const content = document.querySelector('.quick-view-content');
    
    try {
        const response = await fetch(`api/product.php?id=${productId}`);
        const product = await response.json();
        
        content.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <img src="assets/images/products/${product.image}" alt="${product.name}" class="img-fluid">
                </div>
                <div class="col-md-6">
                    <h2>${product.name}</h2>
                    <p class="price">$${product.price.toFixed(2)}</p>
                    <p class="description">${product.short_description}</p>
                    <div class="rating">
                        ${generateStars(product.rating)}
                    </div>
                    <div class="actions">
                        <button class="btn btn-primary" onclick="addToCart(${product.id})">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="btn btn-outline" onclick="toggleWishlist(${product.id})">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <a href="product-details.php?id=${product.id}" class="btn btn-secondary mt-3">
                        View Full Details
                    </a>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error loading product:', error);
        content.innerHTML = '<p class="text-center text-muted">Error loading product details</p>';
    }
}

// ===== Compare Function =====
function addToCompare(productId) {
    let compareList = JSON.parse(localStorage.getItem('nexmart_compare')) || [];
    
    if (compareList.includes(productId)) {
        showNotification('Product already in compare list', 'info');
        return;
    }
    
    if (compareList.length >= 4) {
        showNotification('You can compare up to 4 products', 'warning');
        return;
    }
    
    compareList.push(productId);
    localStorage.setItem('nexmart_compare', JSON.stringify(compareList));
    showNotification('Added to compare list', 'success');
}

// ===== Notification System =====
function showNotification(message, type = 'info') {
    // Remove existing notification
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// ===== Star Rating Generator =====
function generateStars(rating) {
    let stars = '';
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    
    for (let i = 0; i < fullStars; i++) {
        stars += '<i class="fas fa-star"></i>';
    }
    
    if (hasHalfStar) {
        stars += '<i class="fas fa-star-half-alt"></i>';
    }
    
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
    for (let i = 0; i < emptyStars; i++) {
        stars += '<i class="far fa-star"></i>';
    }
    
    return stars;
}

// ===== Price Range Slider =====
function initPriceRange() {
    const minInput = document.querySelector('.price-range-min');
    const maxInput = document.querySelector('.price-range-max');
    const minDisplay = document.querySelector('.price-min-display');
    const maxDisplay = document.querySelector('.price-max-display');
    
    if (!minInput || !maxInput) return;
    
    function updateDisplay() {
        if (minDisplay) minDisplay.textContent = `$${minInput.value}`;
        if (maxDisplay) maxDisplay.textContent = `$${maxInput.value}`;
    }
    
    minInput.addEventListener('input', updateDisplay);
    maxInput.addEventListener('input', updateDisplay);
}

// ===== Filter Functions =====
function applyFilters() {
    console.log('=== main.js applyFilters() called ===');
    
    // Get category from radio buttons (name="category") or select dropdown (.filter-category)
    const categoryRadio = document.querySelector('input[name="category"]:checked');
    const categorySelect = document.querySelector('.filter-category');
    const category = categoryRadio ? categoryRadio.value : (categorySelect ? categorySelect.value : '');
    console.log('Category:', category);
    
    const brand = document.querySelector('.filter-brand')?.value;
    const minPrice = document.querySelector('.price-range-min')?.value;
    const maxPrice = document.querySelector('.price-range-max')?.value;
    
    // Get sort value from #sortBy or .sort-by
    const sortById = document.getElementById('sortBy');
    const sortByClass = document.querySelector('.sort-by');
    const sortValue = sortById ? sortById.value : (sortByClass ? sortByClass.value : '');
    console.log('Sort value:', sortValue);
    
    // Get current URL parameters to preserve search query
    const currentParams = new URLSearchParams(window.location.search);
    const searchQuery = currentParams.get('q');
    
    const params = new URLSearchParams();
    
    // Preserve search query if it exists
    if (searchQuery) params.append('q', searchQuery);
    
    if (category) params.append('category', category);
    if (brand) params.append('brand', brand);
    if (minPrice) params.append('min_price', minPrice);
    if (maxPrice) params.append('max_price', maxPrice);
    
    // Handle sort value (can be "field-order" format or just field)
    if (sortValue) {
        if (sortValue.includes('-')) {
            const [sortField, sortOrder] = sortValue.split('-');
            params.append('sort', sortField);
            params.append('order', sortOrder);
        } else {
            params.append('sort', sortValue);
        }
    }
    
    console.log('Final URL params:', params.toString());
    window.location.href = `products.php?${params.toString()}`;
}

// ===== Image Zoom =====
function initImageZoom() {
    const zoomContainers = document.querySelectorAll('.zoom-container');
    
    zoomContainers.forEach(container => {
        const img = container.querySelector('img');
        const lens = container.querySelector('.zoom-lens');
        
        if (!img || !lens) return;
        
        container.addEventListener('mousemove', function(e) {
            const rect = container.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const xPercent = (x / rect.width) * 100;
            const yPercent = (y / rect.height) * 100;
            
            lens.style.backgroundImage = `url(${img.src})`;
            lens.style.backgroundSize = '200%';
            lens.style.backgroundPosition = `${xPercent}% ${yPercent}%`;
            
            lens.style.left = `${x - lens.offsetWidth / 2}px`;
            lens.style.top = `${y - lens.offsetHeight / 2}px`;
        });
        
        container.addEventListener('mouseleave', function() {
            lens.style.opacity = '0';
        });
        
        container.addEventListener('mouseenter', function() {
            lens.style.opacity = '1';
        });
    });
}

// ===== Quantity Selector =====
function initQuantitySelector() {
    const quantitySelectors = document.querySelectorAll('.quantity-selector');
    
    quantitySelectors.forEach(selector => {
        const minusBtn = selector.querySelector('.minus');
        const plusBtn = selector.querySelector('.plus');
        const input = selector.querySelector('input');
        
        minusBtn.addEventListener('click', () => {
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
            }
        });
        
        plusBtn.addEventListener('click', () => {
            const currentValue = parseInt(input.value);
            const maxValue = parseInt(input.dataset.max) || 99;
            if (currentValue < maxValue) {
                input.value = currentValue + 1;
            }
        });
    });
}

// ===== Form Validation =====
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
        
        // Email validation
        if (input.type === 'email' && input.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                input.classList.add('is-invalid');
                isValid = false;
            }
        }
    });
    
    return isValid;
}

// ===== Lazy Loading Images =====
function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
    }
}

// ===== AJAX Helper =====
async function ajaxRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        return await response.json();
    } catch (error) {
        console.error('AJAX error:', error);
        return { success: false, message: 'An error occurred' };
    }
}

// ===== Debounce Function =====
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ===== Throttle Function =====
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ===== Export functions for global use =====
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.updateCartQuantity = updateCartQuantity;
window.toggleWishlist = toggleWishlist;
window.showQuickView = showQuickView;
window.closeQuickView = closeQuickView;
window.addToCompare = addToCompare;
window.showNotification = showNotification;
window.applyFilters = applyFilters;
window.validateForm = validateForm;
