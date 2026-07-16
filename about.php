<?php
/**
 * About Us Page
 * NexMart E-Commerce
 */
$pageTitle = 'About Us';
$showBreadcrumb = true;
$breadcrumbItems = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'About Us', 'url' => '', 'active' => true]
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- About Section -->
<section class="about-hero py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h1 class="display-4 fw-bold mb-4">Welcome to NexMart Apple Store</h1>
                <p class="lead mb-4">Your Premium Destination for Authentic Apple Products in Myanmar</p>
                <p class="text-muted">
                    We are Myanmar's leading authorized Apple reseller, dedicated to bringing you the latest and 
                    greatest Apple products with exceptional service and competitive prices in MMK.
                </p>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <img src="/assets/images/about-hero.jpg" alt="NexMart Store" class="img-fluid rounded-3 shadow-lg" 
                     onerror="this.src='https://via.placeholder.com/600x400/007AFF/FFFFFF?text=NexMart+Apple+Store'">
            </div>
        </div>
    </div>
</section>

<!-- Our Story -->
<section class="our-story py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">Our Story</h2>
            <p class="text-muted">How We Became Myanmar's Trusted Apple Partner</p>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto" data-aos="fade-up">
                <p class="fs-5 text-muted mb-4">
                    Founded in 2020, NexMart started with a simple mission: to make premium Apple products 
                    accessible to everyone in Myanmar. What began as a small shop in Yangon has grown into 
                    the country's most trusted Apple reseller.
                </p>
                <p class="fs-5 text-muted mb-4">
                    We understand the Myanmar market and our customers' needs. That's why we offer competitive 
                    pricing in MMK, flexible payment options, and exceptional after-sales support. Our team of 
                    Apple-certified technicians ensures that every product meets the highest quality standards.
                </p>
                <p class="fs-5 text-muted">
                    Today, we serve thousands of satisfied customers across Myanmar, from individual users to 
                    businesses, educational institutions, and creative professionals. We're proud to be part of 
                    Apple's global ecosystem and Myanmar's digital transformation.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="mission-vision py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="feature-card h-100 p-5 bg-primary text-white rounded-3">
                    <div class="icon-box mb-4">
                        <i class="fas fa-bullseye fa-3x"></i>
                    </div>
                    <h3 class="mb-3">Our Mission</h3>
                    <p class="fs-5">
                        To provide Myanmar customers with genuine Apple products, expert advice, and outstanding 
                        service that enhances their digital lifestyle. We strive to make technology accessible, 
                        affordable, and enjoyable for everyone.
                    </p>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="feature-card h-100 p-5 bg-dark text-white rounded-3">
                    <div class="icon-box mb-4">
                        <i class="fas fa-eye fa-3x"></i>
                    </div>
                    <h3 class="mb-3">Our Vision</h3>
                    <p class="fs-5">
                        To be Myanmar's #1 Apple partner and the go-to destination for all Apple products and 
                        services. We envision a future where every Myanmar citizen has access to world-class 
                        technology that empowers their creativity and productivity.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="why-choose py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">Why Choose NexMart?</h2>
            <p class="text-muted">What Makes Us Myanmar's Favorite Apple Store</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-box text-center p-4">
                    <div class="icon-circle mx-auto mb-3">
                        <i class="fas fa-certificate fa-2x text-primary"></i>
                    </div>
                    <h4 class="mb-3">100% Authentic</h4>
                    <p class="text-muted">
                        All our products are genuine Apple products with official warranty. 
                        No imitations, no compromises.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-box text-center p-4">
                    <div class="icon-circle mx-auto mb-3">
                        <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                    </div>
                    <h4 class="mb-3">Competitive Pricing</h4>
                    <p class="text-muted">
                        Best prices in Myanmar with transparent MMK pricing. 
                        No hidden fees, no surprises.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-box text-center p-4">
                    <div class="icon-circle mx-auto mb-3">
                        <i class="fas fa-headset fa-2x text-info"></i>
                    </div>
                    <h4 class="mb-3">Expert Support</h4>
                    <p class="text-muted">
                        Apple-certified technicians and customer service team ready to help 
                        you anytime.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-box text-center p-4">
                    <div class="icon-circle mx-auto mb-3">
                        <i class="fas fa-shipping-fast fa-2x text-warning"></i>
                    </div>
                    <h4 class="mb-3">Fast Delivery</h4>
                    <p class="text-muted">
                        Quick and secure delivery across Myanmar. Track your order every step 
                        of the way.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
                <div class="feature-box text-center p-4">
                    <div class="icon-circle mx-auto mb-3">
                        <i class="fas fa-shield-alt fa-2x text-danger"></i>
                    </div>
                    <h4 class="mb-3">Official Warranty</h4>
                    <p class="text-muted">
                        Full manufacturer warranty on all products with hassle-free 
                        after-sales service.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="600">
                <div class="feature-box text-center p-4">
                    <div class="icon-circle mx-auto mb-3">
                        <i class="fas fa-credit-card fa-2x text-purple"></i>
                    </div>
                    <h4 class="mb-3">Flexible Payment</h4>
                    <p class="text-muted">
                        Multiple payment options including COD, bank transfer, and 
                        credit cards.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Values -->
<section class="our-values py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">Our Core Values</h2>
            <p class="text-muted">The Principles That Guide Everything We Do</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                <div class="value-card text-center p-4 h-100">
                    <div class="value-icon mb-3">
                        <i class="fas fa-heart fa-3x text-danger"></i>
                    </div>
                    <h5 class="mb-2">Customer First</h5>
                    <p class="text-muted small">Your satisfaction is our top priority</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                <div class="value-card text-center p-4 h-100">
                    <div class="value-icon mb-3">
                        <i class="fas fa-check-circle fa-3x text-success"></i>
                    </div>
                    <h5 class="mb-2">Quality Assured</h5>
                    <p class="text-muted small">Only the best products make it to you</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="300">
                <div class="value-card text-center p-4 h-100">
                    <div class="value-icon mb-3">
                        <i class="fas fa-handshake fa-3x text-primary"></i>
                    </div>
                    <h5 class="mb-2">Integrity</h5>
                    <p class="text-muted small">Honest and transparent in everything</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="400">
                <div class="value-card text-center p-4 h-100">
                    <div class="value-icon mb-3">
                        <i class="fas fa-lightbulb fa-3x text-warning"></i>
                    </div>
                    <h5 class="mb-2">Innovation</h5>
                    <p class="text-muted small">Always improving our service for you</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics -->
<section class="statistics py-5 bg-primary text-white">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-box">
                    <h2 class="display-4 fw-bold mb-2">10,000+</h2>
                    <p class="fs-5 mb-0">Happy Customers</p>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-box">
                    <h2 class="display-4 fw-bold mb-2">25+</h2>
                    <p class="fs-5 mb-0">Apple Products</p>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-box">
                    <h2 class="display-4 fw-bold mb-2">3 Years</h2>
                    <p class="fs-5 mb-0">In Business</p>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-box">
                    <h2 class="display-4 fw-bold mb-2">24/7</h2>
                    <p class="fs-5 mb-0">Support Available</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">Meet Our Team</h2>
            <p class="text-muted">The Passionate People Behind NexMart</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="team-card text-center">
                    <div class="team-image mb-3">
                        <img src="../assets/images/skt.png" 
                             alt="Sai Kyaw Thiha" class="rounded-circle img-fluid">
                    </div>
                    <h5 class="mb-1">Sai Kyaw Thiha</h5>
                    <p class="text-primary mb-2">Founder & CEO</p>
                    <p class="text-muted small">Visionary leader with passion for technology</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="team-card text-center">
                    <div class="team-image mb-3">
                        <img src="../assets/images/akk.png" 
                             alt="Aung Ko Ko" class="rounded-circle img-fluid">
                    </div>
                    <h5 class="mb-1">Aung Ko Ko</h5>
                    <p class="text-success mb-2">Operations Manager</p>
                    <p class="text-muted small">Ensuring smooth operations daily</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="team-card text-center">
                    <div class="team-image mb-3">
                        <img src="../assets/images/mta.png" 
                             alt="May Thu Aung" class="rounded-circle img-fluid">
                    </div>
                    <h5 class="mb-1">May Thu Aung</h5>
                    <p class="text-warning mb-2">Customer Service Lead</p>
                    <p class="text-muted small">Always here to help our customers</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="team-card text-center">
                    <div class="team-image mb-3">
                        <img src="../assets/images/zwh.png" 
                             alt="Zaw Win Htut" class="rounded-circle img-fluid">
                    </div>
                    <h5 class="mb-1">Zaw Win Htut</h5>
                    <p class="text-danger mb-2">Technical Support</p>
                    <p class="text-muted small">Apple-certified technician expert</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h2 class="display-6 fw-bold mb-3">Ready to Experience the Apple Difference?</h2>
                <p class="lead text-muted mb-0">
                    Visit our store or shop online to discover the latest Apple products at the best prices in Myanmar.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                <a href="/products.php" class="btn btn-primary btn-lg px-5 py-3">
                    <i class="fas fa-shopping-cart me-2"></i>Shop Now
                </a>
            </div>
        </div>
    </div>
</section>

<style>
.about-hero {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.feature-card {
    transition: transform 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(0, 122, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}

.feature-box {
    background: white;
    border-radius: 15px;
    transition: all 0.3s ease;
    height: 100%;
}

.feature-box:hover {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transform: translateY(-5px);
}

.value-card {
    background: white;
    border-radius: 15px;
    border: 2px solid #f0f0f0;
    transition: all 0.3s ease;
}

.value-card:hover {
    border-color: #007AFF;
    box-shadow: 0 5px 20px rgba(0, 122, 255, 0.1);
}

.stat-box {
    padding: 2rem;
}

.team-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    transition: all 0.3s ease;
}

.team-card:hover {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transform: translateY(-5px);
}

.team-image img {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border: 4px solid #007AFF;
}

.cta-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.cta-section h2,
.cta-section .lead {
    color: white;
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2rem;
    }
    
    .display-5 {
        font-size: 1.75rem;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
