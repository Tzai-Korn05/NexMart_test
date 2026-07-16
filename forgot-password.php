<?php
/**
 * Forgot Password Page
 * NexMart E-Commerce
 */
$pageTitle = 'Forgot Password';
$showBreadcrumb = true;
$breadcrumbItems = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Login', 'url' => 'login.php', 'active' => false],
    ['title' => 'Forgot Password', 'url' => '', 'active' => true]
];

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/email-config.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $resetToken = bin2hex(random_bytes(32));
                $resetExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                $stmt->execute([$resetToken, $resetExpires, $user['id']]);
                
                // Send password reset email
                $emailSent = sendPasswordResetEmail($email, $user['name'], $resetToken);
                
                if ($emailSent) {
                    $success = 'Password reset link has been sent to your email address. Please check your inbox.';
                } else {
                    $success = 'Password reset link has been sent to your email address. If you don\'t receive it, please contact support.';
                }
            } else {
                // Don't reveal if email exists or not for security
                $success = 'If an account exists with this email, a password reset link has been sent.';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Forgot Password Section -->
<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="auth-card" data-aos="fade-up">
                    <div class="auth-header text-center mb-4">
                        <h2>Reset Password</h2>
                        <p class="text-muted">Enter your email to receive reset link</p>
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
                        
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                       placeholder="Enter your email" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                        </button>
                    </form>
                    
                    <div class="auth-footer text-center mt-4">
                        <p class="mb-0">
                            <a href="login.php" class="text-primary"><i class="fas fa-arrow-left me-2"></i>Back to Login</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.auth-section {
    background: linear-gradient(135deg, var(--lighter) 0%, var(--light) 100%);
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
}

.auth-card {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: 2rem;
    box-shadow: var(--shadow-xl);
}

.auth-header h2 {
    font-size: var(--text-3xl);
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.5rem;
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
