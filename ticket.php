<?php require_once __DIR__ . '/config/database.php'; require_once __DIR__ . '/classes/Booking.php'; require_once __DIR__ . '/includes/auth.php'; require_once __DIR__ . '/includes/helpers.php'; if(!is_user_logged_in() && !is_admin_logged_in()) redirect_to('login.php'); $database=new Database(); $db=$database->connect(); $bookingModel=new Booking($db); $bookingId=(int)($_GET['id'] ?? 0); $ticket=$bookingModel->getDetailedById($bookingId); if(!$ticket) die('Ticket not found.'); if(is_user_logged_in() && !is_admin_logged_in() && (int)$ticket['user_id'] !== (int)$_SESSION['user_id']) die('Access denied.'); ?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Air-TRX Boarding Pass</title><link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg"><link rel="stylesheet" href="assets/css/bootstrap.min.css"><link rel="stylesheet" href="assets/css/style.css"><style>
    :root {
        --font-gold: #d4af37;
        --font-light-pink: #ffd1dc;
        --font-white: #ffffff;
        --soft-black: #111111;
        --soft-blush: #fff4f8;
        --soft-cream: #fffdf7;
    }

    .ticket-page {
        background:
            linear-gradient(rgba(255, 248, 251, 0.58), rgba(255, 253, 247, 0.58)),
            url('assets/img/admin-slide-2.svg') center center / cover no-repeat fixed !important;
        min-height: 100vh;
        color: var(--soft-black);
    }

    .ticket-page .ticket-shell.boarding-pass {
        background: linear-gradient(180deg, rgba(255,255,255,0.90), rgba(255,248,251,0.86)) !important;
        border: 1px solid rgba(212, 175, 55, 0.30) !important;
        border-radius: 20px !important;
        box-shadow:
            0 20px 50px rgba(0, 0, 0, 0.10),
            0 8px 24px rgba(212, 175, 55, 0.10),
            inset 0 1px 0 rgba(255,255,255,0.92) !important;
        overflow: hidden;
    }

    /* Black header for strong contrast */
    .ticket-page .ticket-top {
        background: #000000 !important;
        border-bottom: 1px solid rgba(255, 209, 220, 0.28) !important;
        padding: 1.25rem 1.4rem !important;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
        box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.05);
    }

    .ticket-page .ticket-top .brand-title,
    .ticket-page .ticket-top .ticket-value {
        color: var(--font-gold) !important;
        text-shadow: 0 1px 8px rgba(0, 0, 0, 0.55) !important;
        font-weight: 800;
    }

    .ticket-page .ticket-top .brand-subtitle,
    .ticket-page .ticket-top .ticket-label {
        color: var(--font-light-pink) !important;
        text-shadow: 0 1px 8px rgba(0, 0, 0, 0.55) !important;
        font-weight: 700;
    }

    .ticket-page .ticket-top img {
        filter: drop-shadow(0 4px 12px rgba(255,255,255,0.08));
    }

    /* Luxury body */
    .ticket-page .ticket-body {
        background: linear-gradient(180deg, rgba(255,255,255,0.88), rgba(255,245,248,0.82)) !important;
        padding: 1.5rem !important;
    }

    .ticket-page .ticket-route {
        color: var(--font-gold) !important;
        background: rgba(255, 245, 248, 0.96);
        border: 1px solid rgba(212, 175, 55, 0.24);
        border-radius: 14px;
        padding: 0.85rem 1rem;
        margin-bottom: 1.2rem;
        text-align: center;
        letter-spacing: 0.02em;
        font-weight: 800;
        box-shadow: 0 6px 18px rgba(212, 175, 55, 0.08);
    }

    .ticket-page .ticket-box,
    .ticket-page .barcode-box {
        background: linear-gradient(180deg, rgba(255,255,255,0.99), rgba(255,241,246,0.95)) !important;
        border: 1px solid rgba(212, 175, 55, 0.22) !important;
        border-radius: 16px !important;
        box-shadow:
            0 8px 20px rgba(0, 0, 0, 0.06),
            inset 0 1px 0 rgba(255,255,255,0.94);
    }

    .ticket-page .ticket-label,
    .ticket-page .barcode-box .ticket-label {
        color: #c98da2 !important;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .ticket-page .ticket-body .ticket-value,
    .ticket-page .ticket-box .ticket-value,
    .ticket-page .barcode-box .ticket-value {
        color: var(--soft-black) !important;
        text-shadow: none !important;
        font-weight: 800;
    }

    .ticket-page .boarding-grid {
        gap: 1.15rem;
    }

    .ticket-page .barcode-lines {
        filter: contrast(1.25) saturate(1.05);
        opacity: 0.95;
    }

    .ticket-page .ticket-actions {
        margin-top: 1.35rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(212, 175, 55, 0.16);
    }

    .ticket-page .btn-air-primary {
        background: #111111 !important;
        border-color: #111111 !important;
        color: var(--font-gold) !important;
        font-weight: 700;
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }

    .ticket-page .btn-air-primary:hover {
        background: #000000 !important;
        border-color: #000000 !important;
        color: var(--font-light-pink) !important;
    }

    .ticket-page .btn-air-outline {
        background: rgba(255, 245, 248, 0.95) !important;
        border: 1px solid rgba(212, 175, 55, 0.34) !important;
        color: var(--soft-black) !important;
        font-weight: 700;
    }

    .ticket-page .btn-air-outline:hover {
        background: #fff0f5 !important;
        border-color: rgba(212, 175, 55, 0.55) !important;
        color: var(--font-gold) !important;
    }

    .ticket-page .ticket-body *,
    .ticket-page .ticket-box *,
    .ticket-page .barcode-box *,
    .ticket-page .ticket-actions * {
        text-shadow: none !important;
    }


    .ticket-page .ticket-shell.boarding-pass {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .ticket-page .ticket-body {
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }

</style></head><body class="ticket-page"><div class="ticket-shell floating-card boarding-pass"><div class="ticket-top"><div class="d-flex justify-content-between align-items-center flex-wrap g-2"><div class="d-flex align-items-center g-2"><img src="assets/img/air-trx-logo.svg" alt="Air-TRX" style="width:72px;height:72px;"><div><div class="brand-title">Air-TRX</div><div class="brand-subtitle">Boarding Pass</div></div></div><div class="text-end"><div class="ticket-label">Booking Reference</div><div class="ticket-value"><?php echo e($ticket['booking_reference']); ?></div></div></div></div><div class="ticket-body"><div class="ticket-route"><?php echo e($ticket['origin']); ?> → <?php echo e($ticket['destination']); ?></div><div class="boarding-grid"><div class="boarding-left"><div class="ticket-grid"><div class="ticket-box"><div class="ticket-label">Passenger</div><div class="ticket-value"><?php echo e($ticket['full_name']); ?></div></div><div class="ticket-box"><div class="ticket-label">Passport</div><div class="ticket-value"><?php echo e($ticket['passport_no']); ?></div></div><div class="ticket-box"><div class="ticket-label">Flight</div><div class="ticket-value"><?php echo e($ticket['flight_code']); ?></div></div><div class="ticket-box"><div class="ticket-label">Seat</div><div class="ticket-value"><?php echo e($ticket['seat_no']); ?></div></div><div class="ticket-box"><div class="ticket-label">Class</div><div class="ticket-value"><?php echo e($ticket['seat_class']); ?></div></div><div class="ticket-box"><div class="ticket-label">Departure</div><div class="ticket-value"><?php echo e($ticket['departure_date']); ?> · <?php echo e(substr($ticket['departure_time'],0,5)); ?></div></div></div></div><div class="boarding-right"><div class="barcode-box"><div class="barcode-lines"></div><div class="ticket-label">Gate opens 45 minutes before departure</div></div></div></div><div class="ticket-actions"><button type="button" class="btn btn-air-primary" onclick="window.print()">Print Boarding Pass</button><a href="<?php echo is_admin_logged_in() ? 'admin/index.php?section=bookings' : 'my_bookings.php'; ?>" class="btn btn-air-outline">Back</a></div></div></div></body></html>