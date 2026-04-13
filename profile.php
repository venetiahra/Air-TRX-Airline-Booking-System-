<?php 
require_once __DIR__ . '/config/database.php'; 
require_once __DIR__ . '/classes/User.php'; 
require_once __DIR__ . '/includes/auth.php'; 
require_once __DIR__ . '/includes/helpers.php'; 
require_user_login(); 

$database = new Database(); 
$db = $database->connect(); 
$userModel = new User($db); 
$user = $userModel->getById((int)$_SESSION['user_id']); 

$error = ''; 
$success = ''; 

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_profile'; 
    
    if($action === 'update_profile') {
        $email = trim($_POST['email'] ?? ''); 
        
        if($userModel->emailExists($email, (int)$_SESSION['user_id'])) {
            $error = 'Email is already used by another account.';
        } else {
            $updateData = [
                'full_name' => $_POST['full_name'] ?? '', 
                'email' => $email, 
                'passport_no' => $_POST['passport_no'] ?? '', 
                'birthday' => $_POST['birthday'] ?? '', 
                'contact_no' => $_POST['contact_no'] ?? '', 
                'address' => $_POST['address'] ?? ''
            ];
            
            $userModel->update((int)$_SESSION['user_id'], $updateData); 
            $_SESSION['user_name'] = $_POST['full_name'] ?? $_SESSION['user_name']; 
            $_SESSION['user_email'] = $email; 
            $success = 'Profile updated successfully.';
            $user = $userModel->getById((int)$_SESSION['user_id']); 
        } 
    } elseif($action === 'change_password') {
        $current = $_POST['current_password'] ?? ''; 
        $new = $_POST['new_password'] ?? ''; 
        $confirm = $_POST['confirm_password'] ?? ''; 
        
        if($new !== $confirm) {
            $error = 'New passwords do not match.';
        } elseif(!$userModel->updatePassword((int)$_SESSION['user_id'], $current, $new)) {
            $error = 'Current password is incorrect.';
        } else {
            $success = 'Password updated successfully.';
        } 
    } 
}

$pageTitle = 'My Profile - Air-TRX'; 
require_once __DIR__ . '/includes/header.php'; 
?>

<style>

    :root {
        --font-gold: #d4af37;
        --font-light-pink: #ffd1dc;
        --font-white: #ffffff;
    }

    .results-section,
    .results-section p,
    .results-section span,
    .results-section div,
    .results-section label,
    .results-section small,
    .results-section a,
    .results-section li,
    .results-section .card,
    .results-section .card-body,
    .results-section .card-header {
        color: var(--font-white) !important;
    }

    .results-section .section-title,
    .results-section h1,
    .results-section h2,
    .results-section h3,
    .results-section .fw-bold,
    .results-section .btn-air-primary,
    .results-section .btn-air-primary *,
    .results-section .btn-air-outline,
    .results-section .btn-air-outline * {
        color: var(--font-gold) !important;
        text-shadow: 0 1px 8px rgba(0, 0, 0, 0.28);
        font-weight: 700;
    }

    .results-section .section-copy,
    .results-section .form-label,
    .results-section .card-header .subtitle,
    .results-section .helper-text,
    .results-section .form-text {
        color: var(--font-light-pink) !important;
    }

    .results-section .form-control,
    .results-section input,
    .results-section textarea,
    .results-section select,
    .results-section input::placeholder,
    .results-section textarea::placeholder {
        color: var(--font-white) !important;
    }

    .results-section .alert,
    .results-section .alert * {
        color: var(--font-white) !important;
    }

    .results-section .form-shell *,
    .results-section .section-header * {
        text-shadow: 0 1px 6px rgba(0, 0, 0, 0.22);
    }

    .form-shell .form-control, .form-shell .form-label { text-align: center; }
    
    .results-section {
        background-image: url('assets/img/admin-slide-2.svg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        min-height: 100vh;
        position: relative;
    }
    
    .results-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5); /* Dark overlay for better text readability */
        z-index: 1;
    }
    
    .results-section > .container-xl {
        position: relative;
        z-index: 2;
    }
    
    /* Ensure cards remain readable over background */
    .form-shell {
        background: rgba(20, 18, 19, 0.95) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(239, 143, 143, 0.83);
    }
</style>

<section class="results-section">
    <div class="container-xl">
        <div class="section-header center-header">
            <div class="section-title">My Profile</div>
            <p class="section-copy">Update your personal information and account security.</p>
        </div>

        <?php if($error !== ''): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <?php if($success !== ''): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif; ?>

        <div class="row g-4 justify-content-center">
            <div class="col-lg-7 col-xl-6">
                <div class="form-shell card border-0 floating-card mx-auto">
                    <div class="card-header">
                        <h3 class="fw-bold mb-0 text-center">Profile Details</h3>
                    </div>
                    <div class="card-body">
                        <!-- NO PHOTO SECTION AT ALL -->
                        <form method="post" class="row g-3">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="col-12">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo e($user['full_name'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo e($user['email'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Passport Number</label>
                                <input type="text" name="passport_no" class="form-control" value="<?php echo e($user['passport_no'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Birthday</label>
                                <input type="date" name="birthday" class="form-control" value="<?php echo e($user['birthday'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_no" class="form-control" value="<?php echo e($user['contact_no'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="3"><?php echo e($user['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-air-primary px-5 py-2 fs-6">💾 Save Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 col-xl-5">
                <div class="form-shell card border-0 floating-card mx-auto">
                    <div class="card-header">
                        <h3 class="fw-bold mb-0 text-center">Change Password</h3>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="action" value="change_password">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-air-primary w-100">🔒 Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>