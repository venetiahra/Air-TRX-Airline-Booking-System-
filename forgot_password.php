<?php require_once __DIR__ . '/config/database.php'; require_once __DIR__ . '/classes/User.php'; require_once __DIR__ . '/includes/auth.php'; require_once __DIR__ . '/includes/helpers.php'; $database=new Database(); $db=$database->connect(); $userModel=new User($db); $error=''; $success=''; if($_SERVER['REQUEST_METHOD']==='POST'){ $email=trim($_POST['email']??''); $passport=trim($_POST['passport_no']??''); $newPassword=$_POST['new_password']??''; $confirmPassword=$_POST['confirm_password']??''; if($newPassword!==$confirmPassword) $error='Passwords do not match.'; else { $user=$userModel->findByEmail($email); if(!$user || trim($user['passport_no'])!==$passport) $error='Account verification failed.'; else { $userModel->resetPassword($email,$passport,$newPassword); $success='Password updated successfully. You may now log in.'; } } } $pageTitle='Forgot Password - Air-TRX'; require_once __DIR__ . '/includes/header.php'; ?>
<style>
    :root {
        --font-gold: #d4af37;
        --font-light-pink: #ffd1dc;
        --font-white: #ffffff;
    }

    .auth-page,
    .auth-page p,
    .auth-page span,
    .auth-page div,
    .auth-page a,
    .auth-page label,
    .auth-page small,
    .auth-page button {
        color: var(--font-white) !important;
    }

    .auth-page .section-title,
    .auth-page h1,
    .auth-page h2,
    .auth-page h3,
    .auth-page .btn-air-primary,
    .auth-page .btn-air-primary * {
        color: var(--font-gold) !important;
        text-shadow: 0 1px 8px rgba(0, 0, 0, 0.28);
        font-weight: 700;
    }

    .auth-page .section-copy,
    .auth-page .form-label,
    .auth-page .auth-links a,
    .auth-page .helper-text,
    .auth-page .form-text {
        color: var(--font-light-pink) !important;
    }

    .auth-page .form-control,
    .auth-page input,
    .auth-page textarea,
    .auth-page select,
    .auth-page input::placeholder,
    .auth-page textarea::placeholder {
        color: var(--font-white) !important;
    }

    .auth-page .auth-card,
    .auth-page .auth-card * {
        text-shadow: 0 1px 6px rgba(0, 0, 0, 0.22);
    }

    .auth-page .alert,
    .auth-page .alert * {
        color: var(--font-white) !important;
    }
</style>
<section class="auth-page"><div class="container-xl"><div class="auth-card auth-wide floating-card"><div class="section-header center-header mb-4"><div class="section-title">Reset your password</div><p class="section-copy">Verify your account using your email and passport number.</p></div><?php if($error!==''): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?><?php if($success!==''): ?><div class="alert alert-success"><?php echo e($success); ?></div><?php endif; ?><form method="post" class="row g-3"><div class="col-md-6"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" required></div><div class="col-md-6"><label class="form-label">Passport Number</label><input type="text" name="passport_no" class="form-control" required></div><div class="col-md-6"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" required></div><div class="col-md-6"><label class="form-label">Confirm Password</label><input type="password" name="confirm_password" class="form-control" required></div><div class="col-12 text-center"><button type="submit" class="btn btn-air-primary">Update Password</button></div></form></div></div></section><?php require_once __DIR__ . '/includes/footer.php'; ?>