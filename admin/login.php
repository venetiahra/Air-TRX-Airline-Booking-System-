<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if (is_admin_logged_in()) {
    redirect_to('index.php');
}

$database = new Database();
$db = $database->connect();
$adminModel = new Admin($db);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $admin = $adminModel->authenticate($username, $password);

    if ($admin) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_username'] = $admin['username'];
        redirect_to('index.php');
    }

    $error = 'Invalid admin credentials.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Air-TRX</title>
    <link rel="icon" type="image/svg+xml" href="../assets/img/favicon.svg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        /* ===== PAGE BACKGROUND ===== */
        body.admin-login-body {
            min-height: 100vh;
            margin: 0;
            background:
                linear-gradient(rgba(5, 10, 20, 0.18), rgba(5, 10, 20, 0.18)),
                url('../assets/img/admin-slide-1.svg') center center / cover no-repeat fixed !important;
            color: #f8fafc !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .slideshow-bg,
        .slide,
        .slide-1,
        .slide-2,
        .slide-3 {
            display: none !important;
        }

        /* ===== LAYOUT ===== */
        .admin-auth-page {
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .admin-login-panel {
            max-width: 460px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem;
            border-radius: 1.25rem;
            background: rgba(15, 23, 42, 0.58) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.22);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: #f8fafc !important;
        }

        /* ===== TEXT ===== */
        .section-title,
        .section-copy,
        .form-label,
        .back-pill,
        .alert,
        .btn,
        p,
        span,
        div,
        label,
        h1, h2, h3, h4, h5, h6 {
            color: #f8fafc !important;
        }

        .section-title {
            font-weight: 800;
            font-size: 2rem;
            color: #ffffff !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.30);
        }

        .section-copy {
            color: #e5e7eb !important;
            margin-bottom: 0;
        }

        /* ===== BACK BUTTON ===== */
        .back-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            text-decoration: none;
            margin-bottom: 1.25rem;
            padding: 0.55rem 1rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.10) !important;
            border: 1px solid rgba(255, 255, 255, 0.20);
            color: #ffffff !important;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .back-pill:hover {
            background: rgba(255, 255, 255, 0.16) !important;
            color: #ffffff !important;
            text-decoration: none;
        }

        /* ===== FORM ===== */
        .form-label {
            font-weight: 600;
            color: #f1f5f9 !important;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.12) !important;
            border: 1px solid rgba(255, 255, 255, 0.22) !important;
            color: #ffffff !important;
            border-radius: 0.85rem;
            padding: 0.8rem 0.95rem;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.72) !important;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.16) !important;
            color: #ffffff !important;
            border-color: #7dd3fc !important;
            box-shadow: 0 0 0 0.2rem rgba(125, 211, 252, 0.20) !important;
        }

        /* ===== BUTTON ===== */
        .btn-air-primary {
            background: #0ea5e9 !important;
            border-color: #0ea5e9 !important;
            color: #ffffff !important;
            font-weight: 700;
            border-radius: 0.85rem;
            padding: 0.85rem 1rem;
        }

        .btn-air-primary:hover {
            background: #0284c7 !important;
            border-color: #0284c7 !important;
            color: #ffffff !important;
        }

        /* ===== ALERT ===== */
        .alert-danger {
            background: rgba(239, 68, 68, 0.18) !important;
            color: #ffffff !important;
            border: 1px solid rgba(239, 68, 68, 0.25) !important;
            border-radius: 0.85rem;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        /* ===== DARK MODE ===== */
        @media (prefers-color-scheme: dark) {
            body.admin-login-body {
                background:
                    linear-gradient(rgba(2, 6, 23, 0.10), rgba(2, 6, 23, 0.10)),
                    url('../assets/img/admin-slide-1.svg') center center / cover no-repeat fixed !important;
            }

            .admin-login-panel {
                background: rgba(2, 6, 23, 0.56) !important;
            }

            .section-copy {
                color: #d1d5db !important;
            }
        }

        /* ===== MOBILE ===== */
        @media (max-width: 576px) {
            .admin-login-panel {
                padding: 1.5rem;
                border-radius: 1rem;
            }

            .section-title {
                font-size: 1.7rem;
            }
        }
    </style>
</head>
<body class="admin-login-body">

<section class="auth-page admin-auth-page">
    <div class="container-xl">
        <div class="auth-card floating-card admin-login-panel">
            <a href="../index.php" class="back-pill">← Back to website</a>

            <div class="section-header center-header mb-4">
                <div class="section-title">Admin Access</div>
                <p class="section-copy">Operations login for Air-TRX.</p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger"><?= e($error); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-air-primary w-100">Login as Admin</button>
            </form>
        </div>
    </div>
</section>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/app.js"></script>
</body>
</html>