<?php
/**
 * Login Page
 * NexMart E-Commerce
 */
$pageTitle = 'Login';
$showBreadcrumb = true;
$breadcrumbItems = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'Login', 'url' => '', 'active' => true]
];

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $defaultRedirect = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin' ? 'admin/index.php' : 'index.php';
    header('Location: ' . $defaultRedirect);
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        try {
            // Check user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            // WARNING: Plain text password comparison - NOT SECURE!
            if ($user && $password === $user['password']) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Handle remember me
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                    $stmt->execute([$token, $expires, $user['id']]);
                    
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
                }
                
                // Redirect
                $redirect = $_SESSION['redirect_after_login'] ?? (isset($user['role']) && $user['role'] === 'admin' ? 'admin/index.php' : 'index.php');
                unset($_SESSION['redirect_after_login']);
                
                setFlashMessage('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again.';
        }
    }
}

// Check for remember me cookie
if (isset($_COOKIE['remember_token']) && !isLoggedIn()) {
    try {
        $token = $_COOKIE['remember_token'];
        $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW() AND status = 'active' LIMIT 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            setFlashMessage('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');
            header('Location: profile.php');
            exit;
        }
    } catch (PDOException $e) {
        // Ignore errors
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Login Section -->
<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="auth-card" data-aos="fade-up">
                    <div class="auth-header text-center mb-4">
                        <h2>Welcome Back</h2>
                        <p class="text-muted">Sign in to your account</p>
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
                        
                        <div class="form-group mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter your password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <a href="forgot-password.php" class="text-primary">Forgot Password?</a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>
                    </form>
                    
                    <div class="auth-divider my-4">
                        <span>or continue with</span>
                    </div>
                    
                    <div class="social-login">
                        <button class="btn btn-outline-primary w-100 mb-2">
                            <i class="fab fa-google me-2"></i>Continue with Google
                        </button>
                        <button class="btn btn-outline-primary w-100">
                            <i class="fab fa-facebook-f me-2"></i>Continue with Facebook
                        </button>
                    </div>
                    
                    <div class="auth-footer text-center mt-4">
                        <p class="mb-0">Don't have an account? <a href="register.php" class="text-primary">Sign Up</a></p>
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

.auth-divider {
    text-align: center;
    position: relative;
}

.auth-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: var(--gray);
}

.auth-divider span {
    background: var(--white);
    padding: 0 1rem;
    color: var(--gray);
    position: relative;
}

.social-login button {
    border-radius: var(--radius);
    padding: 0.75rem;
    font-weight: 500;
}
</style>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
