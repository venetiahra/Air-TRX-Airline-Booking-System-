<?php 
require_once __DIR__ . '/auth.php'; 
require_once __DIR__ . '/helpers.php'; 

// Get current user profile photo (SUPER SAFE - NO ERRORS)
$userPhoto = '';
$userInitial = '';
if (is_user_logged_in()) {
    $userInitial = strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1));
    
    // Use cached session photo first (FASTEST)
    if (!empty($_SESSION['user_photo']) && file_exists(__DIR__ . '/../' . $_SESSION['user_photo'])) {
        $userPhoto = $_SESSION['user_photo'];
    }
    
    // Only check database on profile page (current page check)
    $currentPage = basename($_SERVER['PHP_SELF']);
    if ($currentPage === 'profile.php') {
        // CORRECT PATHS - database.php is in ROOT
        $databasePath = __DIR__ . '/../config/database.php';
        $userClassPath = __DIR__ . '/../classes/User.php';
        
        if (file_exists($databasePath) && file_exists($userClassPath)) {
            try {
                require_once $databasePath;
                require_once $userClassPath;
                
                $database = new Database();
                $db = $database->connect();
                $userModel = new User($db);
                $user = $userModel->getById((int)$_SESSION['user_id']);
                
                if (!empty($user['profile_photo']) && file_exists(__DIR__ . '/../' . $user['profile_photo'])) {
                    $userPhoto = $user['profile_photo'];
                    $_SESSION['user_photo'] = $userPhoto;
                }
            } catch (Exception $e) {
                // Silently continue - use placeholder
            }
        }
    }
}

$pageTitle = $pageTitle ?? 'Air-TRX'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease;
        }
        .user-avatar:hover {
            transform: scale(1.1);
        }
        .user-avatar-placeholder {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            font-weight: bold;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: transform 0.2s ease;
        }
        .user-avatar-placeholder:hover {
            transform: scale(1.1);
        }
        .user-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .user-pill:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-1px);
        }
        @media (max-width: 991px) {
            .user-avatar, .user-avatar-placeholder {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }
        }
    

        /* ===== FILLED BUBBLE ACTIVE / HOVER STATE ===== */
        .air-navbar .nav-link {
            position: relative;
            border-radius: 999px;
            padding: 0.55rem 0.95rem !important;
            transition: background-color 0.22s ease, color 0.22s ease, box-shadow 0.22s ease;
        }

        .air-navbar .nav-link:hover,
        .air-navbar .nav-link:focus,
        .air-navbar .nav-link.active {
            background: rgba(255, 209, 220, 0.16) !important;
            box-shadow: inset 0 0 0 1px rgba(212, 175, 55, 0.20);
            color: #d4af37 !important;
        }

    </style>
</head>
<body>
    <div id="pageLoader" class="page-loader">
        <div class="loader-card">
            <img src="assets/img/favicon.svg" alt="Air-TRX" class="loader-logo">
            <div class="loader-title">Air-TRX</div>
            <div class="loader-subtitle">Preparing your flight experience...</div>
            <div class="loader-bar"><span></span></div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg air-navbar sticky-top shadow-sm">
        <div class="container-xl">
            <a class="navbar-brand d-flex align-items-center gap-3" href="index.php">
                <img src="assets/img/air-trx-logo.svg" alt="Air-TRX" class="brand-logo">
                <div>
                    <div class="brand-title">Air-TRX</div>
                    <div class="brand-subtitle">Book Your Next Escape</div>
                </div>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto gap-lg-2 align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#search-box">Flights</a></li>
                    
                    <?php if (is_user_logged_in()): ?>
                        <li class="nav-item"><a class="nav-link" href="my_bookings.php">My Bookings</a></li>
                        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                        
                        <li class="nav-item user-pill" onclick="window.location.href='profile.php'">
                            <?php if (!empty($userPhoto)): ?>
                                <img src="<?php echo htmlspecialchars($userPhoto); ?>" alt="<?php echo e(current_user_name()); ?>" class="user-avatar" title="Go to Profile">
                            <?php else: ?>
                                <div class="user-avatar-placeholder" title="Upload Profile Photo">
                                    <?php echo $userInitial; ?>
                                </div>
                            <?php endif; ?>
                            <span>Hi, <?php echo e(current_user_name()); ?></span>
                        </li>
                        
                        <li class="nav-item">
                            <a class="btn btn-air-soft" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="btn btn-air-primary" href="register.php">Create Account</a></li>
                    <?php endif; ?>
                    
                    
                    <li class="nav-item">
                        <a class="nav-link admin-link" href="admin/login.php">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>