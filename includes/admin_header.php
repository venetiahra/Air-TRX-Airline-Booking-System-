<?php 
require_once __DIR__ . '/auth.php'; 
require_once __DIR__ . '/helpers.php'; 

$adminPageTitle = $adminPageTitle ?? 'Air-TRX Admin'; 
$adminSection = $adminSection ?? 'dashboard'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($adminPageTitle); ?></title>
    <link rel="icon" type="image/svg+xml" href="../assets/img/favicon.svg">
    
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/style-fixes.css">
    <link rel="stylesheet" href="../assets/css/style-fixes-v2.css">
    <link rel="stylesheet" href="../assets/css/style-fixes-v3.css">
    <link rel="stylesheet" href="../assets/css/style-admin-fix.css">

    <style>
        :root {
            --font-gold: #d4af37;
            --font-light-pink: #ffd1dc;
            --font-white: #ffffff;
            --soft-black: #111111;
        }

        body {
            background: linear-gradient(135deg, #fff8fb 0%, #fffdf7 45%, #fff4f8 100%);
        }

        .admin-navbar {
            background: rgba(0, 0, 0, 0.96) !important;
            border-bottom: 1px solid rgba(212, 175, 55, 0.26) !important;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.22), inset 0 -1px 0 rgba(255,255,255,0.04) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .admin-navbar .navbar-brand {
            color: var(--font-white) !important;
        }

        .admin-navbar .brand-title {
            color: var(--font-gold) !important;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-shadow: 0 1px 8px rgba(0, 0, 0, 0.55);
        }

        .admin-navbar .brand-subtitle {
            color: var(--font-light-pink) !important;
            font-weight: 600;
            text-shadow: 0 1px 8px rgba(0, 0, 0, 0.45);
        }

        .admin-navbar .brand-logo {
            filter: drop-shadow(0 4px 12px rgba(255,255,255,0.10));
        }

        .admin-navbar .nav-link {
            color: var(--font-light-pink) !important;
            font-weight: 700;
            border-radius: 999px;
            padding: 0.55rem 0.95rem !important;
            transition: all 0.25s ease;
        }

        .admin-navbar .nav-link:hover,
        .admin-navbar .nav-link.active {
            color: var(--font-gold) !important;
            background: rgba(255, 255, 255, 0.06) !important;
            box-shadow: inset 0 0 0 1px rgba(212, 175, 55, 0.18);
        }

        .admin-navbar .user-pill {
            color: var(--font-white) !important;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(212, 175, 55, 0.22);
            border-radius: 999px;
            padding: 0.55rem 0.95rem;
            font-weight: 700;
        }

        .admin-navbar .btn-air-soft,
        .admin-navbar .toggle-btn {
            background: #111111 !important;
            border: 1px solid rgba(212, 175, 55, 0.32) !important;
            color: var(--font-gold) !important;
            font-weight: 700;
            box-shadow: 0 8px 18px rgba(0,0,0,0.16);
        }

        .admin-navbar .btn-air-soft:hover,
        .admin-navbar .toggle-btn:hover {
            background: #000000 !important;
            color: var(--font-light-pink) !important;
            border-color: rgba(255, 209, 220, 0.30) !important;
        }

        .admin-navbar .navbar-toggler {
            border-color: rgba(212, 175, 55, 0.28) !important;
            background: rgba(255,255,255,0.04);
        }

        .admin-navbar .navbar-toggler-icon {
            filter: invert(1) brightness(1.15);
        }

        @media (max-width: 991px) {
            .admin-navbar .navbar-collapse {
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(255,255,255,0.08);
            }

            .admin-navbar .navbar-nav {
                align-items: stretch !important;
            }

            .admin-navbar .user-pill,
            .admin-navbar .btn-air-soft,
            .admin-navbar .toggle-btn {
                width: 100%;
                text-align: center;
                justify-content: center;
            }
        }
    </style>

</head>
<body>

<nav class="navbar navbar-expand-lg air-navbar sticky-top shadow-sm admin-navbar" id="adminNavbar">
    <div class="container-xl">
        <a class="navbar-brand d-flex align-items-center gap-3" href="index.php">
            <img src="../assets/img/air-trx-logo.svg" alt="Air-TRX" class="brand-logo">
            <div>
                <div class="brand-title">Air-TRX Admin</div>
                <div class="brand-subtitle">Operations Dashboard</div>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav ms-auto gap-lg-2 align-items-lg-center">
                <li class="nav-item"><a class="nav-link <?php echo $adminSection==='dashboard'?'active':''; ?>" href="index.php?section=dashboard">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $adminSection==='flights'?'active':''; ?>" href="index.php?section=flights">Flights</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $adminSection==='bookings'?'active':''; ?>" href="index.php?section=bookings">Bookings</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $adminSection==='users'?'active':''; ?>" href="index.php?section=users">Users</a></li>
                
          
                <li class="nav-item user-pill">Admin: <?php echo e(current_admin_name()); ?></li>
                <li class="nav-item"><a class="btn btn-air-soft" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>