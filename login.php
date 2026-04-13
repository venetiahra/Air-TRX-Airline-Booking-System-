<?php require_once __DIR__ . '/config/database.php'; require_once __DIR__ . '/classes/User.php'; require_once __DIR__ . '/includes/auth.php'; require_once __DIR__ . '/includes/helpers.php'; if(is_user_logged_in()) redirect_to('index.php'); $database=new Database(); $db=$database->connect(); $userModel=new User($db); $error=''; $flash=flash_get(); if($_SERVER['REQUEST_METHOD']==='POST'){ $email=trim($_POST['email']??''); $password=$_POST['password']??''; $redirect=$_POST['redirect']??'index.php'; $user=$userModel->authenticate($email,$password); if($user){ session_regenerate_id(true); $_SESSION['user_id']=$user['id']; $_SESSION['user_name']=$user['full_name']; $_SESSION['user_email']=$user['email']; redirect_to($redirect!==''?$redirect:'index.php'); } $error='Invalid email or password.'; } $pageTitle='Login - Air-TRX'; require_once __DIR__ . '/includes/header.php'; ?>
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
    .auth-page small {
        color: var(--font-white) !important;
    }

    .auth-page .section-title,
    .auth-page h1,
    .auth-page h2,
    .auth-page h3,
    .auth-page .btn-air-primary {
        color: var(--font-gold) !important;
        text-shadow: 0 1px 8px rgba(0, 0, 0, 0.28);
        font-weight: 700;
    }

    .auth-page .section-copy,
    .auth-page .form-label,
    .auth-page .auth-links a {
        color: var(--font-light-pink) !important;
    }

    .auth-page .form-control,
    .auth-page input,
    .auth-page input::placeholder {
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
<section class="auth-page"><div class="container-xl"><div class="auth-card floating-card"><div class="section-header center-header mb-4"><div class="section-title">User Login</div><p class="section-copy">Log in to continue with your booking.</p></div><?php if($flash): ?><div class="alert <?php echo $flash['type']==='danger'?'alert-danger':'alert-success'; ?>"><?php echo e($flash['message']); ?></div><?php endif; ?><?php if($error!==''): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?><form method="post"><input type="hidden" name="redirect" value="<?php echo e($_GET['redirect'] ?? 'index.php'); ?>"><div class="mb-3"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" required></div><div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div><div class="auth-links mb-3"><a href="forgot_password.php">Forgot password?</a><a href="register.php">Create account</a></div><button type="submit" class="btn btn-air-primary w-100">Login</button></form></div></div></section><?php require_once __DIR__ . '/includes/footer.php'; ?>