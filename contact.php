<?php
/**
 * Contact Page
 * NexMart E-Commerce
 */
$pageTitle = 'Contact Us';
$showBreadcrumb = true;
$breadcrumbItems = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Contact', 'url' => '', 'active' => true]
];

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } elseif (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO contacts (name, email, phone, subject, message, status) 
                VALUES (?, ?, ?, ?, ?, 'new')
            ");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            
            $success = 'Thank you for contacting us! We will get back to you soon.';
        } catch (PDOException $e) {
            $error = 'Error submitting your message. Please try again.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Contact Section -->
<section class="contact-section py-5">
    <div class="container">
        <div class="page-header text-center mb-5" data-aos="fade-up">
            <h1>Contact Us</h1>
            <p class="text-muted">We'd love to hear from you. Get in touch with us.</p>
        </div>
        
        <div class="row">
            <!-- Contact Info -->
            <div class="col-lg-4 mb-4">
                <div class="contact-info" data-aos="fade-right">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <h4>Our Address</h4>
                            <p>No. 123, Pyay Road<br>Kamayut Township, Yangon<br>Myanmar</p>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <h4>Phone Number</h4>
                            <p>+95 9 771 662 558<br>+95 9 952 000 438</p>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h4>Email Address</h4>
                            <p>saikyawthihacs@gmail.com<br>support@nexmart.com</p>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <h4>Business Hours</h4>
                            <p>Mon - Fri: 9:00 AM - 6:00 PM<br>Sat - Sun: Closed</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="contact-form-card" data-aos="fade-left">
                    <div class="card-header">
                        <h3>Send us a Message</h3>
                    </div>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">Your Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">Your Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="6" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Map Section -->
        <div class="map-section mt-5" data-aos="fade-up">
            <div class="map-container">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d244934.02268192677!2d96.02690724999999!3d16.8660694!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30c1949e223e196b%3A0x56fbd271f8080bb4!2sYangon%2C%20Myanmar!5e0!3m2!1sen!2smm!4v1620000000000!5m2!1sen!2smm" 
                    width="100%" 
                    height="400" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="faq-section mt-5" data-aos="fade-up">
            <h2 class="text-center mb-4">Frequently Asked Questions</h2>
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            What payment methods do you accept?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            We accept Cash on Delivery, Bank Transfer, Credit/Debit Cards (Visa, MasterCard, American Express), and PayPal.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            How long does shipping take?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Standard shipping takes 3-5 business days. Express shipping (when available) takes 1-2 business days.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            What is your return policy?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            We offer a 30-day return policy for most products. Items must be in original condition with all accessories and packaging.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            Do you offer warranty on products?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, most products come with manufacturer warranty. Warranty periods vary by product and brand.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                            How can I track my order?
                        </button>
                    </h2>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Once your order ships, you will receive an email with tracking information. You can also track your order from your account.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.contact-section {
    background: var(--lighter);
}

.page-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.info-card {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.info-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-lg);
    background: var(--primary);
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.info-content h4 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--dark);
}

.info-content p {
    margin: 0;
    color: var(--gray);
    line-height: 1.6;
}

.contact-form-card {
    background: var(--white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.contact-form-card .card-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--light);
}

.contact-form-card .card-header h3 {
    margin: 0;
    color: var(--dark);
}

.contact-form-card form {
    padding: 1.5rem;
}

.map-section {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    overflow: hidden;
}

.map-container {
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.faq-section h2 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark);
}

.accordion-item {
    border: 1px solid var(--light);
    border-radius: var(--radius-lg);
    margin-bottom: 1rem;
    overflow: hidden;
}

.accordion-button {
    font-weight: 600;
    color: var(--dark);
}

.accordion-button:not(.collapsed) {
    background: var(--primary);
    color: var(--white);
}

.accordion-body {
    color: var(--gray);
    line-height: 1.6;
}

@media (max-width: 768px) {
    .contact-info {
        margin-bottom: 2rem;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
