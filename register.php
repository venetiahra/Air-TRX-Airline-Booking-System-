<?php require_once __DIR__ . '/config/database.php'; require_once __DIR__ . '/classes/User.php'; require_once __DIR__ . '/includes/auth.php'; require_once __DIR__ . '/includes/helpers.php'; if(is_user_logged_in()) redirect_to('index.php'); $database=new Database(); $db=$database->connect(); $userModel=new User($db); $error=''; if($_SERVER['REQUEST_METHOD']==='POST'){ $fullName=trim($_POST['full_name']??''); $email=trim($_POST['email']??''); $passport=trim($_POST['passport_no']??''); $birthday=trim($_POST['birthday']??''); $contact=trim($_POST['contact_no']??''); $address=trim($_POST['address']??''); $password=$_POST['password']??''; $confirm=$_POST['confirm_password']??''; if($password!==$confirm) $error='Passwords do not match.'; elseif($userModel->emailExists($email)) $error='Email is already registered.'; else { try { $userModel->create(['full_name'=>$fullName,'email'=>$email,'passport_no'=>$passport,'birthday'=>$birthday,'contact_no'=>$contact,'address'=>$address,'password'=>$password]); flash_set('success','Account created successfully. Please log in to continue.'); redirect_to('login.php'); } catch(Throwable $e){ $error=$e->getMessage(); } } } $pageTitle='Create Account - Air-TRX'; require_once __DIR__ . '/includes/header.php'; ?>
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
    .auth-page .auth-links a,
    .auth-page .helper-text {
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
<section class="auth-page"><div class="container-xl"><div class="auth-card auth-wide floating-card"><div class="section-header center-header mb-4"><div class="section-title">Create your Air-TRX account</div><p class="section-copy">Sign up once, then book flights anytime.</p></div><?php if($error!==''): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?><form method="post" class="row g-3"><div class="col-md-6"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" required></div><div class="col-md-6"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" required></div><div class="col-md-6"><label class="form-label">Passport Number</label><input type="text" name="passport_no" class="form-control" required></div><div class="col-md-6"><label class="form-label">Birthday</label><input type="date" name="birthday" class="form-control"></div><div class="col-md-6"><label class="form-label">Contact Number</label><input type="text" name="contact_no" class="form-control"></div><div class="col-md-6"><label class="form-label">Address</label><input type="text" name="address" class="form-control"></div><div class="col-md-6"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div><div class="col-md-6"><label class="form-label">Confirm Password</label><input type="password" name="confirm_password" class="form-control" required></div><div class="col-12 text-center"><button type="submit" class="btn btn-air-primary">Create Account</button></div></form></div></div></section><?php require_once __DIR__ . '/includes/footer.php'; ?>