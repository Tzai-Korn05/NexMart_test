<?php
/**
 * Footer File
 * NexMart E-Commerce
 * 
 * Includes footer links, social media, and copyright
 */
?>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- About Section -->
            <div class="footer-section">
                <h3>NexMart</h3>
                <p>Your trusted electronics store since 2020. We offer the latest smartphones, laptops, tablets, and accessories at unbeatable prices.</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>



            <!-- Categories -->
            <div class="footer-section">
                <h3>Categories</h3>
                <ul>
                    <li><a href="<?php echo baseUrl('products.php?category=smartphones'); ?>">Smartphones</a></li>
                    <li><a href="<?php echo baseUrl('products.php?category=laptops'); ?>">Laptops</a></li>
                    <li><a href="<?php echo baseUrl('products.php?category=tablets'); ?>">Tablets</a></li>
                    <li><a href="<?php echo baseUrl('products.php?category=accessories'); ?>">Accessories</a></li>
                    <li><a href="<?php echo baseUrl('products.php?category=gaming'); ?>">Gaming</a></li>
                    <li><a href="<?php echo baseUrl('products.php?category=smart-watches'); ?>">Smart Watches</a></li>
                </ul>
            </div>
            
            <!-- Customer Service -->
            <div class="footer-section">
                <h3>Customer Service</h3>
                <ul>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Shipping Info</a></li>
                    <li><a href="#">Returns & Exchanges</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms & Conditions</a></li>
                </ul>
            </div>

            <!-- Contact Info (moved) -->
            <div class="footer-section">
                <h3>Contact Us</h3>
                <ul>
                    <li><i class="fas fa-map-marker-alt"></i> 123, Pyay Road, Kamayut, Yangon</li>
                    <li><i class="fas fa-phone"></i> (+95) 9 771 662558</li>
                    <li><i class="fas fa-envelope"></i> saikyawthihacs@gmail.com</li>
                    <li><i class="fas fa-clock"></i> Mon - Fri: 9:00 AM - 6:00 PM</li>
                </ul>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="payment-methods-section text-center mb-4">
            <h4 class="mb-3">Payment Methods</h4>
            <div class="payment-methods justify-content-center">
                <i class="fa-brands fa-cc-visa payment-icon" title="Visa" aria-hidden="true"></i>
                <i class="fa-brands fa-cc-mastercard payment-icon" title="MasterCard" aria-hidden="true"></i>
                <i class="fa-brands fa-cc-paypal payment-icon" title="PayPal" aria-hidden="true"></i>
                <i class="fa-brands fa-cc-amex payment-icon" title="American Express" aria-hidden="true"></i>
                <i class="fa-brands fa-cc-discover payment-icon" title="Discover" aria-hidden="true"></i>
            </div>
        </div>

        <!-- Copyright -->
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> NexMart. All rights reserved.</p>
        </div>
    </div>
</footer>

<style>
/* Footer payment icons sizing */
.payment-methods .payment-icon {
    font-size: 28px; /* larger icon */
    margin: 0 12px;
    vertical-align: middle;
}

@media (max-width: 576px) {
    .payment-methods .payment-icon { font-size: 22px; margin: 0 8px; }
}
</style>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- AOS Animation Library -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Swiper.js -->
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    window.NEXMART_BASE_URL = '<?php echo rtrim(baseUrl(''), '/'); ?>';
</script>

<!-- Custom JavaScript -->
<script src="<?php echo baseUrl('assets/js/main.js'); ?>"></script>

<!-- Chatbot Styles -->
<link rel="stylesheet" href="<?php echo baseUrl('assets/css/chatbot.css'); ?>">

<!-- Chatbot Script -->
<script src="<?php echo baseUrl('assets/js/chatbot.js'); ?>"></script>

</body>
</html>
