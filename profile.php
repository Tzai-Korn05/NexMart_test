<?php
/**
 * User Profile Page
 * NexMart E-Commerce
 */
$pageTitle = 'My Profile';
$showBreadcrumb = true;
$breadcrumbItems = [
    ['title' => 'Home', 'url' => 'index.php', 'active' => false],
    ['title' => 'My Profile', 'url' => '', 'active' => true]
];

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Require login
requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $zip = sanitize($_POST['zip'] ?? '');
    $country = sanitize($_POST['country'] ?? 'USA');
    
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } elseif (empty($name)) {
        $error = 'Name is required';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE users SET name = ?, phone = ?, address = ?, city = ?, state = ?, zip = ?, country = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $phone, $address, $city, $state, $zip, $country, getCurrentUserId()]);
            
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully';
            
            // Refresh user data
            $user = getCurrentUser();
        } catch (PDOException $e) {
            $error = 'Error updating profile';
        }
    }
}

// Handle profile photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } elseif (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Please select a photo to upload';
    } else {
        $file = $_FILES['profile_photo'];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Error uploading file';
        } else {
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $fileType = $file['type'];
            
            if (!in_array($fileType, $allowedTypes)) {
                $error = 'Only JPG, PNG, and GIF files are allowed';
            } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB max
                $error = 'File size must be less than 5MB';
            } else {
                // Create upload directory if it doesn't exist
                $uploadDir = __DIR__ . '/uploads/users/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'user_' . getCurrentUserId() . '_' . time() . '.' . $extension;
                $uploadPath = $uploadDir . $filename;
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    // Delete old profile photo if exists
                    if (!empty($user['image']) && $user['image'] !== 'default.jpg') {
                        $oldFile = $uploadDir . $user['image'];
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
                    
                    // Update database
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET image = ? WHERE id = ?");
                        $stmt->execute([$filename, getCurrentUserId()]);
                        $success = 'Profile photo updated successfully!';
                        
                        // Refresh user data
                        $user = getCurrentUser();
                    } catch (PDOException $e) {
                        $error = 'Error updating profile photo';
                    }
                } else {
                    $error = 'Error uploading file';
                }
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } elseif (empty($currentPassword) || empty($newPassword)) {
        $error = 'Please fill in all password fields';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([getCurrentUserId()]);
            $userData = $stmt->fetch();
            
            // WARNING: Plain text password comparison - NOT SECURE!
            if ($currentPassword === $userData['password']) {
                // WARNING: Storing plain text password - NOT SECURE!
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$newPassword, getCurrentUserId()]);
                $success = 'Password changed successfully';
            } else {
                $error = 'Current password is incorrect';
            }
        } catch (PDOException $e) {
            $error = 'Error changing password';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Profile Section -->
<section class="profile-section py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="profile-sidebar" data-aos="fade-right">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php
                            $avatarPath = 'uploads/users/' . ($user['image'] ?? 'default.jpg');
                            if (!empty($user['image']) && file_exists($avatarPath)) {
                                $avatarUrl = $avatarPath;
                            } else {
                                $avatarUrl = 'uploads/users/default.jpg';
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($avatarUrl); ?>" 
                                 alt="<?php echo htmlspecialchars($user['name']); ?>">
                        </div>
                        <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    
                    <nav class="profile-nav">
                        <a href="profile.php" class="nav-link active">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <a href="orders.php" class="nav-link">
                            <i class="fas fa-box me-2"></i>My Orders
                        </a>
                        <a href="wishlist.php" class="nav-link">
                            <i class="fas fa-heart me-2"></i>Wishlist
                        </a>
                        <a href="#" class="nav-link">
                            <i class="fas fa-map-marker-alt me-2"></i>Addresses
                        </a>
                        <a href="#" class="nav-link">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                        <a href="logout.php" class="nav-link text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="profile-content" data-aos="fade-left">
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
                    
                    <!-- Profile Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3>Profile Information</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="name" class="form-label">Full Name *</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" 
                                                   value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                            <small class="text-muted">Email cannot be changed</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address" 
                                           value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control" id="city" name="city" 
                                                   value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="state" class="form-label">State</label>
                                            <input type="text" class="form-control" id="state" name="state" 
                                                   value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="zip" class="form-label">ZIP Code</label>
                                            <input type="text" class="form-control" id="zip" name="zip" 
                                                   value="<?php echo htmlspecialchars($user['zip'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <select class="form-select" id="country" name="country">
                                        <option value="USA" <?php echo ($user['country'] ?? 'USA') === 'USA' ? 'selected' : ''; ?>>United States</option>
                                        <option value="Canada" <?php echo ($user['country'] ?? '') === 'Canada' ? 'selected' : ''; ?>>Canada</option>
                                        <option value="UK" <?php echo ($user['country'] ?? '') === 'UK' ? 'selected' : ''; ?>>United Kingdom</option>
                                        <option value="Australia" <?php echo ($user['country'] ?? '') === 'Australia' ? 'selected' : ''; ?>>Australia</option>
                                    </select>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Profile Photo -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3>Profile Photo</h3>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center mb-3 mb-md-0">
                                    <div class="current-photo">
                                        <?php
                                        $imagePath = 'uploads/users/' . ($user['image'] ?? 'default.jpg');
                                        if (!empty($user['image']) && file_exists($imagePath)) {
                                            $imageUrl = $imagePath;
                                        } else {
                                            $imageUrl = 'uploads/users/default.jpg';
                                        }
                                        ?>
                                        <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                             alt="Current Photo" 
                                             class="img-thumbnail"
                                             style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        
                                        <div class="form-group mb-3">
                                            <label for="profile_photo" class="form-label">
                                                <i class="fas fa-camera me-2"></i>Choose New Photo
                                            </label>
                                            <input type="file" class="form-control" id="profile_photo" 
                                                   name="profile_photo" accept="image/*" required>
                                            <small class="text-muted">
                                                Accepted formats: JPG, PNG, GIF (Max 5MB)
                                            </small>
                                        </div>
                                        
                                        <button type="submit" name="upload_photo" class="btn btn-primary">
                                            <i class="fas fa-upload me-2"></i>Upload Photo
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Change Password -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Change Password</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="form-group mb-3">
                                    <label for="current_password" class="form-label">Current Password *</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="new_password" class="form-label">New Password *</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" name="change_password" class="btn btn-primary">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.profile-section {
    background: var(--lighter);
}

.profile-sidebar {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    position: sticky;
    top: 100px;
}

.user-info {
    text-align: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--light);
}

.user-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    margin: 0 auto 1rem;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-info h4 {
    margin: 0.5rem 0 0.25rem;
    color: var(--dark);
}

.user-info p {
    margin: 0;
    color: var(--gray);
    font-size: 0.875rem;
}

.profile-nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.profile-nav .nav-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border-radius: var(--radius);
    color: var(--dark);
    text-decoration: none;
    transition: var(--transition);
}

.profile-nav .nav-link:hover,
.profile-nav .nav-link.active {
    background: var(--primary);
    color: var(--white);
}

.profile-content .card {
    background: var(--white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--light);
}

.card-header h3 {
    margin: 0;
    color: var(--dark);
}

.card-body {
    padding: 1.5rem;
}

@media (max-width: 992px) {
    .profile-sidebar {
        position: static;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
