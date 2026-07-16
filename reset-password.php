<?php
/**
 * Reset Password Page
 * NexMart E-Commerce
 */
$pageTitle = 'Reset Password';
$showBreadcrumb = true;
$breadcrumbItems = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Reset Password', 'url' => '', 'active' => true]
];

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';
$validToken = false;
$user = null;

// Check for token
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Invalid or missing reset token.';
} else {
    // Verify token
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE reset_token = ? 
            AND reset_expires > NOW() 
            AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            $validToken = true;
        } else {
            $error = 'This reset link is invalid or has expired. Please request a new password reset.';
        }
    } catch (PDOException $e) {
        $error = 'An error occurred. Please try again.';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($password)) {
        $error = 'Please enter a new password';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        try {
            // Update password and clear reset token
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                UPDATE users 
                SET password = ?, reset_token = NULL, reset_expires = NULL 
                WHERE id = ?
            ");
            $stmt->execute([$hashedPassword, $user['id']]);
            
            $success = 'Your password has been successfully reset! You can now login with your new password.';
            $validToken = false; // Prevent form from showing again
        } catch (PDOException $e) {
            $error = 'An error occurred while resetting your password. Please try again.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Reset Password Section -->
<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="auth-card" data-aos="fade-up">
                    <div class="auth-header text-center mb-4">
                        <h2>🔐 Reset Password</h2>
                        <p class="text-muted">Enter your new password</p>
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
                    <div class="text-center mt-4">
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Go to Login
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($validToken && !$success): ?>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="form-group mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter new password" required minlength="6">
                            </div>
                            <small class="form-text text-muted">Must be at least 6 characters</small>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Confirm new password" required minlength="6">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-check me-2"></i>Reset Password
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <?php if (!$validToken && !$success): ?>
                    <div class="text-center mt-4">
                        <a href="forgot-password.php" class="btn btn-outline-primary">
                            <i class="fas fa-redo me-2"></i>Request New Reset Link
                        </a>
                    </div>
                    <?php endif; ?>
                    
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
