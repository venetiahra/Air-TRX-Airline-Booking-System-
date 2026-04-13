<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Flight.php';
require_once __DIR__ . '/../classes/Booking.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Mailer.php';

require_admin_login();

$database = new Database();
$db = $database->connect();

$flightModel = new Flight($db);
$bookingModel = new Booking($db);
$userModel = new User($db);

$adminSection = $_GET['section'] ?? 'dashboard';
$editFlight = null;
$error = '';
$flash = flash_get();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create_flight':
                $flightModel->create([
                    'flight_code'      => trim($_POST['flight_code'] ?? ''),
                    'origin'           => trim($_POST['origin'] ?? ''),
                    'destination'      => trim($_POST['destination'] ?? ''),
                    'departure_date'   => $_POST['departure_date'] ?? '',
                    'departure_time'   => $_POST['departure_time'] ?? '08:00',
                    'economy_fare'     => (float)($_POST['economy_fare'] ?? 0),
                    'premium_fare'     => (float)($_POST['premium_fare'] ?? 0),
                    'business_fare'    => (float)($_POST['business_fare'] ?? 0),
                    'first_class_fare' => (float)($_POST['first_class_fare'] ?? 0),
                ]);
                flash_set('success', 'Flight added successfully.');
                redirect_to('index.php?section=flights');
                break;

            case 'update_flight':
                $flightModel->update((int)$_POST['id'], [
                    'flight_code'      => trim($_POST['flight_code'] ?? ''),
                    'origin'           => trim($_POST['origin'] ?? ''),
                    'destination'      => trim($_POST['destination'] ?? ''),
                    'departure_date'   => $_POST['departure_date'] ?? '',
                    'departure_time'   => $_POST['departure_time'] ?? '08:00',
                    'economy_fare'     => (float)($_POST['economy_fare'] ?? 0),
                    'premium_fare'     => (float)($_POST['premium_fare'] ?? 0),
                    'business_fare'    => (float)($_POST['business_fare'] ?? 0),
                    'first_class_fare' => (float)($_POST['first_class_fare'] ?? 0),
                ]);
                flash_set('success', 'Flight updated successfully.');
                redirect_to('index.php?section=flights');
                break;

            case 'delete_flight':
                $flightModel->delete((int)$_POST['id']);
                flash_set('warning', 'Flight deleted.');
                redirect_to('index.php?section=flights');
                break;

            case 'delete_booking':
                $bookingModel->delete((int)$_POST['id']);
                flash_set('warning', 'Booking deleted.');
                redirect_to('index.php?section=bookings');
                break;

            case 'send_ticket_email':
                $bookingId = (int)($_POST['id'] ?? 0);
                $ticketRow = $bookingModel->getDetailedById($bookingId);

                if (!$ticketRow) {
                    throw new RuntimeException('Booking ticket not found.');
                }

                $user = [
                    'email' => (string)($ticketRow['email'] ?? ''),
                    'full_name' => (string)($ticketRow['full_name'] ?? 'Passenger'),
                ];

                $flight = [
                    'flight_code' => (string)($ticketRow['flight_code'] ?? ''),
                    'origin' => (string)($ticketRow['origin'] ?? ''),
                    'destination' => (string)($ticketRow['destination'] ?? ''),
                    'departure_date' => (string)($ticketRow['departure_date'] ?? ''),
                    'departure_time' => (string)($ticketRow['departure_time'] ?? ''),
                ];

                $booking = [
                    'booking_reference' => (string)($ticketRow['booking_reference'] ?? ''),
                    'seat_no' => (string)($ticketRow['seat_no'] ?? ''),
                    'seat_class' => (string)($ticketRow['seat_class'] ?? ''),
                    'fare_amount' => (float)($ticketRow['fare_amount'] ?? 0),
                    'booking_status' => (string)($ticketRow['booking_status'] ?? 'Confirmed'),
                ];

                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $basePath = preg_replace('#/admin/?$#', '', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));
                $ticketUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $basePath . '/ticket.php?id=' . $bookingId;

                $mailer = new AppMailer();
                $sent = $mailer->sendAdminTicketEmail($user, $flight, $booking, $ticketUrl);

                if ($sent) {
                    flash_set('success', 'Ticket email sent successfully.');
                } else {
                    flash_set('warning', 'Ticket email failed: ' . $mailer->getLastError());
                }

                redirect_to('index.php?section=bookings');
                break;

            case 'delete_user':
                $userModel->delete((int)$_POST['id']);
                flash_set('warning', 'User removed.');
                redirect_to('index.php?section=users');
                break;
        }
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}

// Handle edit mode
if (isset($_GET['edit_flight'])) {
    $editFlight = $flightModel->getById((int)$_GET['edit_flight']);
    $adminSection = 'flights';
}

// Get counts and data
$flightCount  = $flightModel->countAll();
$bookingCount = $bookingModel->countAll();
$userCount    = $userModel->countAll();

$flights  = $flightModel->read();
$bookings = $bookingModel->read();
$users    = $userModel->read();

$adminPageTitle = 'Air-TRX Admin';

require_once __DIR__ . '/../includes/admin_header.php';
?>

<style>
    body {
        background:
            linear-gradient(rgba(5, 10, 20, 0.20), rgba(5, 10, 20, 0.20)),
            url('../assets/img/admin-slide-1.svg') center center / cover no-repeat fixed !important;
        color: #f8fafc !important;
    }

    .admin-shell {
        position: relative;
        z-index: 2;
        padding-top: 1rem;
        padding-bottom: 2rem;
    }

    body,
    p,
    span,
    div,
    label,
    td,
    th,
    h1, h2, h3, h4, h5, h6,
    .section-title,
    .section-copy,
    .metric-label,
    .metric-value,
    .form-label,
    .card-header h3,
    .table,
    .table th,
    .table td,
    .alert,
    .btn {
        color: #f8fafc !important;
    }

    .section-title,
    .card-header h3,
    .metric-value,
    .table thead th {
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.30);
    }
.flight-form-card,
.flight-list-shell {
    border-radius: 24px;
    background:
        radial-gradient(circle at top left, rgba(56, 189, 248, 0.16), transparent 35%),
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 30%),
        linear-gradient(180deg, rgba(255,255,255,0.16), rgba(255,255,255,0.04)),
        rgba(15, 23, 42, 0.68) !important;
    border: 1px solid rgba(125, 211, 252, 0.16) !important;
    box-shadow:
        0 20px 45px rgba(0, 0, 0, 0.24),
        0 8px 24px rgba(14, 165, 233, 0.08),
        inset 0 1px 0 rgba(255,255,255,0.10);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.booking-list-shell {
    max-width: 1180px;
    margin: 0 auto;
    border-radius: 20px;
    background:
        radial-gradient(circle at top left, rgba(56, 189, 248, 0.16), transparent 35%),
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 30%),
        linear-gradient(180deg, rgba(255,255,255,0.16), rgba(255,255,255,0.04)),
        rgba(15, 23, 42, 0.68) !important;
    border: 1px solid rgba(125, 211, 252, 0.16) !important;
    box-shadow:
        0 16px 34px rgba(0, 0, 0, 0.22),
        0 6px 18px rgba(14, 165, 233, 0.07),
        inset 0 1px 0 rgba(255,255,255,0.10);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.booking-list-shell .card-header {
    background:
        linear-gradient(90deg, rgba(14, 165, 233, 0.12), rgba(59, 130, 246, 0.06)),
        rgba(255, 255, 255, 0.04) !important;
    border-bottom: 1px solid rgba(125, 211, 252, 0.16) !important;
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
    min-height: 62px;
    display: flex;
    align-items: center;
    padding: 0.9rem 1.1rem;
}

.booking-list-shell .card-body {
    padding: 1rem 1.1rem 1.1rem;
}

.booking-list-toolbar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.95rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid rgba(148, 163, 184, 0.18);
}

.booking-search-wrap {
    width: 100%;
    max-width: none;
    flex: 1 1 auto;
}

.booking-search-input {
    background: rgba(255,255,255,0.10) !important;
    border: 1px solid rgba(148, 163, 184, 0.20) !important;
    color: #f8fafc !important;
    border-radius: 12px !important;
    min-height: 40px;
    font-size: 0.94rem;
    font-family: "Segoe UI", "Inter", "Poppins", sans-serif;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.06),
        0 6px 14px rgba(0,0,0,0.10);
}

.booking-search-input::placeholder {
    color: #cbd5e1 !important;
}

.booking-search-input:focus {
    background: rgba(255,255,255,0.14) !important;
    border-color: rgba(56, 189, 248, 0.38) !important;
    box-shadow: 0 0 0 0.20rem rgba(56, 189, 248, 0.16) !important;
    color: #ffffff !important;
}

.booking-carousel-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.booking-carousel-btn {
    width: 38px;
    height: 38px;
    padding: 0;
    border-radius: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.98rem;
    line-height: 1;
}

.booking-carousel-viewport {
    position: relative;
    overflow: hidden;
    width: 100%;
    padding: 0.15rem 0;
}

.booking-carousel-track {
    display: flex;
    flex-wrap: nowrap;
    width: 100%;
    transition: transform 0.65s cubic-bezier(0.22, 1, 0.36, 1);
    will-change: transform;
}

.booking-carousel-track .booking-card-col {
    flex: 0 0 100%;
    max-width: 100%;
    padding: 0 0.15rem;
}

.booking-carousel-track .booking-card-col.d-none {
    display: none !important;
}

.booking-carousel-progress {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.45rem;
    margin-top: 1rem;
}

.booking-carousel-dot {
    width: 10px;
    height: 10px;
    border: 0;
    border-radius: 999px;
    background: rgba(148, 163, 184, 0.45);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.2);
    transition: all 0.25s ease;
    padding: 0;
}

.booking-carousel-dot.active {
    width: 28px;
    background: linear-gradient(90deg, #38bdf8, #60a5fa);
    box-shadow: 0 0 14px rgba(56, 189, 248, 0.28);
}

.booking-card {
    padding: 1.05rem;
    border-radius: 18px;
    box-shadow:
        0 14px 28px rgba(0, 0, 0, 0.20),
        inset 0 1px 0 rgba(255,255,255,0.10),
        inset 0 -1px 0 rgba(255,255,255,0.03);
}

.booking-card::after {
    border-radius: 18px;
}

.booking-card-top {
    gap: 12px;
    margin-bottom: 0.85rem;
    padding-bottom: 0.8rem;
}

.booking-card-section {
    margin-bottom: 0.85rem;
}

.booking-label {
    font-size: 0.68rem;
    margin-bottom: 0.28rem;
}

.booking-reference {
    font-size: 1.04rem;
}

.booking-value {
    font-size: 0.94rem;
}

.booking-subvalue {
    font-size: 0.86rem;
}

.booking-grid {
    gap: 10px;
    margin-top: 0.75rem;
}

.booking-mini-card {
    padding: 0.75rem;
    border-radius: 13px;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.08),
        0 6px 14px rgba(0,0,0,0.10);
}

.booking-mini-card .booking-value {
    font-size: 0.9rem;
}

.user-list-shell {
    max-width: 1180px;
    margin: 0 auto;
    border-radius: 20px;
    background:
        radial-gradient(circle at top left, rgba(56, 189, 248, 0.16), transparent 35%),
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 30%),
        linear-gradient(180deg, rgba(255,255,255,0.16), rgba(255,255,255,0.04)),
        rgba(15, 23, 42, 0.68) !important;
    border: 1px solid rgba(125, 211, 252, 0.16) !important;
    box-shadow:
        0 16px 34px rgba(0, 0, 0, 0.22),
        0 6px 18px rgba(14, 165, 233, 0.07),
        inset 0 1px 0 rgba(255,255,255,0.10);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.user-list-shell .card-header {
    background:
        linear-gradient(90deg, rgba(14, 165, 233, 0.12), rgba(59, 130, 246, 0.06)),
        rgba(255, 255, 255, 0.04) !important;
    border-bottom: 1px solid rgba(125, 211, 252, 0.16) !important;
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
    min-height: 62px;
    display: flex;
    align-items: center;
    padding: 0.9rem 1.1rem;
}

.user-list-shell .card-body {
    padding: 1rem 1.1rem 1.1rem;
}

.user-list-toolbar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.95rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid rgba(148, 163, 184, 0.18);
}

.user-search-wrap {
    width: 100%;
    max-width: none;
    flex: 1 1 auto;
}

.user-search-input {
    background: rgba(255,255,255,0.10) !important;
    border: 1px solid rgba(148, 163, 184, 0.20) !important;
    color: #f8fafc !important;
    border-radius: 12px !important;
    min-height: 40px;
    font-size: 0.94rem;
    font-family: "Segoe UI", "Inter", "Poppins", sans-serif;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.06),
        0 6px 14px rgba(0,0,0,0.10);
}

.user-search-input::placeholder {
    color: #cbd5e1 !important;
}

.user-search-input:focus {
    background: rgba(255,255,255,0.14) !important;
    border-color: rgba(56, 189, 248, 0.38) !important;
    box-shadow: 0 0 0 0.20rem rgba(56, 189, 248, 0.16) !important;
    color: #ffffff !important;
}

.user-carousel-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.user-carousel-btn {
    width: 38px;
    height: 38px;
    padding: 0;
    border-radius: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.98rem;
    line-height: 1;
}

.user-carousel-viewport {
    position: relative;
    overflow: hidden;
    width: 100%;
    padding: 0.15rem 0;
}

.user-carousel-track {
    display: flex;
    flex-wrap: nowrap;
    width: 100%;
    transition: transform 0.65s cubic-bezier(0.22, 1, 0.36, 1);
    will-change: transform;
}

.user-carousel-track .user-card-col {
    flex: 0 0 100%;
    max-width: 100%;
    padding: 0 0.15rem;
}

.user-carousel-track .user-card-col.d-none {
    display: none !important;
}

.user-carousel-progress {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.45rem;
    margin-top: 1rem;
}

.user-carousel-dot {
    width: 10px;
    height: 10px;
    border: 0;
    border-radius: 999px;
    background: rgba(148, 163, 184, 0.45);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.2);
    transition: all 0.25s ease;
    padding: 0;
}

.user-carousel-dot.active {
    width: 28px;
    background: linear-gradient(90deg, #38bdf8, #60a5fa);
    box-shadow: 0 0 14px rgba(56, 189, 248, 0.28);
}

.user-card {
    position: relative;
    overflow: hidden;
    padding: 1.05rem;
    border-radius: 18px;
    background:
        linear-gradient(180deg, rgba(255,255,255,0.14), rgba(255,255,255,0.04)),
        rgba(15, 23, 42, 0.58) !important;
    border: 1px solid rgba(255, 255, 255, 0.14) !important;
    box-shadow:
        0 14px 28px rgba(0, 0, 0, 0.20),
        inset 0 1px 0 rgba(255,255,255,0.10),
        inset 0 -1px 0 rgba(255,255,255,0.03);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    color: #f8fafc !important;
    transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
}

.user-card::before {
    content: "";
    position: absolute;
    top: -30%;
    left: -10%;
    width: 120%;
    height: 65%;
    background: linear-gradient(
        120deg,
        rgba(255,255,255,0.22) 0%,
        rgba(255,255,255,0.10) 22%,
        rgba(255,255,255,0.03) 45%,
        rgba(255,255,255,0.00) 70%
    );
    transform: rotate(-8deg);
    pointer-events: none;
}

.user-card:hover {
    transform: translateY(-6px);
    box-shadow:
        0 20px 40px rgba(0, 0, 0, 0.26),
        0 10px 22px rgba(14, 165, 233, 0.10),
        inset 0 1px 0 rgba(255,255,255,0.12),
        inset 0 -1px 0 rgba(255,255,255,0.04);
    border-color: rgba(125, 211, 252, 0.28) !important;
}

.user-card-top,
.user-card-section,
.user-grid {
    position: relative;
    z-index: 2;
}

.user-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 0.85rem;
    padding-bottom: 0.8rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.10);
}

.user-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 42px;
    height: 30px;
    padding: 0 0.65rem;
    border-radius: 999px;
    background: rgba(59, 130, 246, 0.14);
    border: 1px solid rgba(96, 165, 250, 0.24);
    color: #e0f2fe !important;
    font-weight: 700;
    font-size: 0.82rem;
}

.user-label {
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #cbd5e1 !important;
    margin-bottom: 0.28rem;
}

.user-name {
    font-size: 1.04rem;
    font-weight: 800;
    color: #ffffff !important;
    word-break: break-word;
    text-shadow: 0 1px 2px rgba(0,0,0,0.25);
}

.user-value {
    font-size: 0.94rem;
    font-weight: 700;
    color: #ffffff !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.22);
    word-break: break-word;
}

.user-subvalue {
    font-size: 0.86rem;
    color: #dbe4f0 !important;
    word-break: break-word;
}

.user-card-section {
    margin-bottom: 0.85rem;
}

.user-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-top: 0.75rem;
}

.user-mini-card {
    padding: 0.75rem;
    border-radius: 13px;
    background:
        linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.03)),
        rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.10);
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.08),
        0 6px 14px rgba(0,0,0,0.10);
}

.user-mini-card .user-value {
    font-size: 0.9rem;
}

@media (max-width: 991px) {
    .user-list-shell {
        max-width: 100%;
    }
}

@media (max-width: 767px) {
    .user-list-toolbar {
        flex-direction: column;
        align-items: stretch;
    }

    .user-carousel-controls {
        justify-content: flex-end;
    }

    .user-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 991px) {
    .booking-list-shell {
        max-width: 100%;
    }
}

@media (max-width: 767px) {
    .booking-list-toolbar {
        flex-direction: column;
        align-items: stretch;
    }

    .booking-carousel-controls {
        justify-content: flex-end;
    }
}
.flight-form-card .card-header,
.flight-list-shell .card-header {
    background:
        linear-gradient(90deg, rgba(14, 165, 233, 0.12), rgba(59, 130, 246, 0.06)),
        rgba(255, 255, 255, 0.04) !important;
    border-bottom: 1px solid rgba(125, 211, 252, 0.16) !important;
    border-top-left-radius: 24px;
    border-top-right-radius: 24px;
}

.sticky-flight-card {
    position: relative;
    top: 0;
}

.sticky-flight-card {
    min-height: 100%;
}

.flight-search-wrap {
    width: 100%;
    max-width: 370px;
}

.flight-search-input {
    background: rgba(255,255,255,0.10) !important;
    border: 1px solid rgba(148, 163, 184, 0.20) !important;
    color: #f8fafc !important;
    border-radius: 14px !important;
    min-height: 46px;
    font-family: "Segoe UI", "Inter", "Poppins", sans-serif;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.06),
        0 8px 18px rgba(0,0,0,0.10);
}

.flight-search-input::placeholder {
    color: #cbd5e1 !important;
}

.flight-search-input:focus {
    background: rgba(255,255,255,0.14) !important;
    border-color: rgba(56, 189, 248, 0.38) !important;
    box-shadow: 0 0 0 0.20rem rgba(56, 189, 248, 0.16) !important;
    color: #ffffff !important;
}

.flight-item-card {
    position: relative;
    overflow: hidden;
    padding: 1.35rem;
    border-radius: 22px;
    background:
        radial-gradient(circle at top left, rgba(125, 211, 252, 0.14), transparent 30%),
        linear-gradient(180deg, rgba(255,255,255,0.16), rgba(255,255,255,0.04)),
        rgba(17, 24, 39, 0.72);
    border: 1px solid rgba(125,211,252,0.14);
    box-shadow:
        0 18px 38px rgba(0,0,0,0.22),
        0 10px 22px rgba(14, 165, 233, 0.08),
        inset 0 1px 0 rgba(255,255,255,0.09);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
}

.flight-item-card:hover {
    transform: translateY(-7px);
    box-shadow:
        0 24px 48px rgba(0,0,0,0.28),
        0 14px 28px rgba(14,165,233,0.14),
        inset 0 1px 0 rgba(255,255,255,0.11);
    border-color: rgba(125, 211, 252, 0.30);
}

.flight-item-glow {
    position: absolute;
    top: -25%;
    left: -10%;
    width: 120%;
    height: 65%;
    background: linear-gradient(
        120deg,
        rgba(255,255,255,0.24) 0%,
        rgba(186,230,253,0.10) 22%,
        rgba(255,255,255,0.03) 42%,
        rgba(255,255,255,0) 70%
    );
    transform: rotate(-8deg);
    pointer-events: none;
}

.flight-item-top,
.flight-route-row,
.flight-meta-grid {
    position: relative;
    z-index: 2;
}

.flight-item-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 1rem;
    padding-bottom: 0.95rem;
    border-bottom: 1px solid rgba(148, 163, 184, 0.18);
}

.flight-label {
    font-size: 0.74rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #93c5fd !important;
    margin-bottom: 0.35rem;
    font-family: "Segoe UI", "Inter", "Poppins", sans-serif;
}

.flight-code {
    font-size: 1.22rem;
    font-weight: 800;
    color: #f8fafc !important;
    letter-spacing: 0.02em;
    text-shadow: 0 2px 6px rgba(0,0,0,0.24);
    font-family: "Segoe UI", "Inter", "Poppins", sans-serif;
}

.flight-route-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 1.15rem;
    flex-wrap: wrap;
}

.flight-airport-block {
    flex: 1;
    min-width: 140px;
}

.flight-airport {
    font-size: 1.12rem;
    font-weight: 800;
    color: #ffffff !important;
    letter-spacing: 0.01em;
    font-family: "Segoe UI", "Inter", "Poppins", sans-serif;
}

.flight-route-arrow {
    font-size: 1.55rem;
    font-weight: 900;
    color: #38bdf8 !important;
    line-height: 1;
    text-shadow: 0 0 12px rgba(56, 189, 248, 0.22);
}

.flight-meta-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.flight-mini-card {
    padding: 0.92rem;
    border-radius: 16px;
    background:
        linear-gradient(180deg, rgba(255,255,255,0.13), rgba(255,255,255,0.04)),
        rgba(30, 41, 59, 0.56);
    border: 1px solid rgba(148, 163, 184, 0.14);
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.08),
        0 8px 18px rgba(0,0,0,0.12);
}

.flight-value {
    font-size: 0.98rem;
    font-weight: 800;
    color: #f8fafc !important;
    font-family: "Segoe UI", "Inter", "Poppins", sans-serif;
}

.flight-mini-card .flight-label {
    color: #7dd3fc !important;
}

.flight-actions .btn-air-outline {
    background: rgba(59, 130, 246, 0.12) !important;
    border: 1px solid rgba(96, 165, 250, 0.30) !important;
    color: #e0f2fe !important;
    font-weight: 700;
}

.flight-actions .btn-air-outline:hover {
    background: rgba(59, 130, 246, 0.20) !important;
    border-color: rgba(125, 211, 252, 0.42) !important;
    color: #ffffff !important;
}

.flight-actions .btn-air-soft {
    background: rgba(239, 68, 68, 0.18) !important;
    border: 1px solid rgba(248, 113, 113, 0.30) !important;
    color: #fff1f2 !important;
    font-weight: 700;
}

.flight-actions .btn-air-soft:hover {
    background: rgba(239, 68, 68, 0.28) !important;
    color: #ffffff !important;
}

.flight-form-card .form-label {
    color: #bae6fd !important;
    font-weight: 700;
    letter-spacing: 0.02em;
    font-family: "Segoe UI", "Inter", "Poppins", sans-serif;
}

.flight-form-card .form-control {
    background: rgba(255,255,255,0.10) !important;
    border: 1px solid rgba(148, 163, 184, 0.18) !important;
    color: #f8fafc !important;
    font-family: "Segoe UI", "Inter", "Poppins", sans-serif;
}

.flight-form-card .form-control:focus {
    background: rgba(255,255,255,0.14) !important;
    border-color: rgba(56, 189, 248, 0.38) !important;
    box-shadow: 0 0 0 0.20rem rgba(56, 189, 248, 0.16) !important;
}

.flight-form-card h3,
.flight-list-shell h3 {
    color: #f8fafc !important;
    font-weight: 800;
    letter-spacing: 0.01em;
    font-family: "Segoe UI", "Inter", "Poppins", sans-serif;
}

@media (max-width: 991px) {
    .sticky-flight-card {
        position: static;
        top: auto;
    }

    .flight-form-card,
    .flight-list-shell {
        border-radius: 20px;
    }

    .flight-form-card .card-header,
    .flight-list-shell .card-header {
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
    }

    .flight-search-wrap {
        max-width: 100%;
    }

    .flight-list-toolbar {
        align-items: stretch;
        flex-direction: column;
    }

    .flight-carousel-controls {
        justify-content: flex-end;
    }
}

@media (max-width: 767px) {
    .flight-meta-grid {
        grid-template-columns: 1fr;
    }

    .flight-item-top {
        flex-direction: column;
        align-items: stretch;
    }

    .flight-route-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .flight-route-arrow {
        transform: rotate(90deg);
    }

    .flight-list-toolbar {
        flex-direction: column;
        align-items: stretch;
    }

    .flight-carousel-controls {
        justify-content: flex-end;
    }
}
    
.dashboard-carousel-shell {
    max-width: 1180px;
    margin: 0 auto 1.5rem;
    border-radius: 20px;
    background:
        radial-gradient(circle at top left, rgba(56, 189, 248, 0.16), transparent 35%),
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 30%),
        linear-gradient(180deg, rgba(255,255,255,0.16), rgba(255,255,255,0.04)),
        rgba(15, 23, 42, 0.68) !important;
    border: 1px solid rgba(125, 211, 252, 0.16) !important;
    box-shadow:
        0 16px 34px rgba(0, 0, 0, 0.22),
        0 6px 18px rgba(14, 165, 233, 0.07),
        inset 0 1px 0 rgba(255,255,255,0.10);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.dashboard-carousel-shell .card-header {
    background:
        linear-gradient(90deg, rgba(14, 165, 233, 0.12), rgba(59, 130, 246, 0.06)),
        rgba(255, 255, 255, 0.04) !important;
    border-bottom: 1px solid rgba(125, 211, 252, 0.16) !important;
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
    min-height: 62px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.9rem 1.1rem;
}

.dashboard-carousel-shell .card-body {
    padding: 1rem 1.1rem 1.1rem;
}

.dashboard-carousel-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.dashboard-carousel-btn {
    width: 38px;
    height: 38px;
    padding: 0;
    border-radius: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.98rem;
    line-height: 1;
}

.dashboard-carousel-viewport {
    position: relative;
    overflow: hidden;
    width: 100%;
    padding: 0.15rem 0;
}

.dashboard-carousel-track {
    display: flex;
    flex-wrap: nowrap;
    width: 100%;
    transition: transform 0.65s cubic-bezier(0.22, 1, 0.36, 1);
    will-change: transform;
}

.dashboard-carousel-track .dashboard-card-col {
    flex: 0 0 100%;
    max-width: 100%;
    padding: 0 0.15rem;
}

.dashboard-carousel-progress {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.45rem;
    margin-top: 1rem;
}

.dashboard-carousel-dot {
    width: 10px;
    height: 10px;
    border: 0;
    border-radius: 999px;
    background: rgba(148, 163, 184, 0.45);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.2);
    transition: all 0.25s ease;
    padding: 0;
}

.dashboard-carousel-dot.active {
    width: 28px;
    background: linear-gradient(90deg, #38bdf8, #60a5fa);
    box-shadow: 0 0 14px rgba(56, 189, 248, 0.28);
}

.dashboard-metric-card {
    position: relative;
    overflow: hidden;
    padding: 1.1rem;
    border-radius: 18px;
    background:
        linear-gradient(180deg, rgba(255,255,255,0.14), rgba(255,255,255,0.04)),
        rgba(15, 23, 42, 0.58) !important;
    border: 1px solid rgba(255, 255, 255, 0.14) !important;
    box-shadow:
        0 14px 28px rgba(0, 0, 0, 0.20),
        inset 0 1px 0 rgba(255,255,255,0.10),
        inset 0 -1px 0 rgba(255,255,255,0.03);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    color: #f8fafc !important;
}

.dashboard-metric-card::before {
    content: "";
    position: absolute;
    top: -28%;
    left: -10%;
    width: 120%;
    height: 62%;
    background: linear-gradient(
        120deg,
        rgba(255,255,255,0.22) 0%,
        rgba(255,255,255,0.10) 22%,
        rgba(255,255,255,0.03) 45%,
        rgba(255,255,255,0.00) 70%
    );
    transform: rotate(-8deg);
    pointer-events: none;
}

.dashboard-metric-top,
.dashboard-metric-footer {
    position: relative;
    z-index: 2;
}

.dashboard-metric-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 0.85rem;
    padding-bottom: 0.8rem;
    border-bottom: 1px solid rgba(255,255,255,0.10);
}

.dashboard-metric-icon {
    width: 44px;
    height: 44px;
    border-radius: 13px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(59, 130, 246, 0.16);
    border: 1px solid rgba(96, 165, 250, 0.22);
    font-size: 1.05rem;
}

.dashboard-metric-label {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.10em;
    color: #93c5fd !important;
    margin-bottom: 0.3rem;
}

.dashboard-metric-value {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1;
    color: #ffffff !important;
    margin-bottom: 0.55rem;
}

.dashboard-metric-copy {
    color: #dbeafe !important;
    font-size: 0.9rem;
    margin-bottom: 0;
}

.dashboard-metric-footer {
    margin-top: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.dashboard-metric-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.45rem 0.7rem;
    border-radius: 999px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.10);
    color: #bfdbfe !important;
    font-size: 0.78rem;
    font-weight: 700;
}

@media (max-width: 767px) {
    .dashboard-carousel-shell .card-header {
        flex-direction: column;
        align-items: stretch;
    }

    .dashboard-carousel-controls {
        justify-content: flex-end;
    }

    .dashboard-metric-footer {
        flex-direction: column;
        align-items: flex-start;
    }
}

    .floating-card,
    .form-shell,
    .table-shell,
    .metric-card,
    .card {
        background: rgba(15, 23, 42, 0.60) !important;
        border: 1px solid rgba(255, 255, 255, 0.10) !important;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        color: #f8fafc !important;
    }

    .card-header {
        background: rgba(255, 255, 255, 0.04) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #ffffff !important;
    }

    /* ===== DASHBOARD TITLES ===== */
    .section-title {
        font-weight: 800;
        color: #ffffff !important;
    }

    .section-copy {
        color: #e5e7eb !important;
    }

    .metric-label {
        font-size: 0.95rem;
        font-weight: 600;
        color: #dbe4f0 !important;
    }

    .metric-value {
        font-size: 2rem;
        font-weight: 800;
        color: #ffffff !important;
    }

    .table {
        --bs-table-bg: transparent !important;
        --bs-table-striped-bg: rgba(255, 255, 255, 0.03) !important;
        --bs-table-hover-bg: rgba(255, 255, 255, 0.06) !important;
        --bs-table-color: #f8fafc !important;
        color: #f8fafc !important;
        margin-bottom: 0;
    }

    .table thead th {
        color: #ffffff !important;
        font-weight: 700;
        border-bottom: 1px solid rgba(255, 255, 255, 0.18) !important;
    }

    .table td {
        border-color: rgba(255, 255, 255, 0.10) !important;
        vertical-align: middle;
    }

    .text-muted,
    .small.text-muted {
        color: #d1d5db !important;
    }

    .form-label {
        color: #f1f5f9 !important;
        font-weight: 600;
    }

    .form-control,
    .form-select {
        background: rgba(255, 255, 255, 0.12) !important;
        border: 1px solid rgba(255, 255, 255, 0.20) !important;
        color: #ffffff !important;
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.75) !important;
    }

    .form-control:focus,
    .form-select:focus {
        background: rgba(255, 255, 255, 0.16) !important;
        color: #ffffff !important;
        border-color: #7dd3fc !important;
        box-shadow: 0 0 0 0.20rem rgba(125, 211, 252, 0.20) !important;
    }

    input[type="date"]::-webkit-calendar-picker-indicator,
    input[type="time"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        cursor: pointer;
    }

    /* ===== BUTTONS ===== */
    .btn-air-primary {
        background: #0ea5e9 !important;
        border-color: #0ea5e9 !important;
        color: #ffffff !important;
        font-weight: 700;
    }

    .btn-air-primary:hover {
        background: #0284c7 !important;
        border-color: #0284c7 !important;
        color: #ffffff !important;
    }

    .btn-air-outline {
        background: rgba(255, 255, 255, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.35) !important;
        color: #ffffff !important;
        font-weight: 600;
    }

    .btn-air-outline:hover {
        background: rgba(255, 255, 255, 0.16) !important;
        color: #ffffff !important;
    }

    .btn-air-soft {
        background: rgba(239, 68, 68, 0.18) !important;
        border: 1px solid rgba(239, 68, 68, 0.35) !important;
        color: #ffffff !important;
        font-weight: 600;
    }

    .btn-air-soft:hover {
        background: rgba(239, 68, 68, 0.28) !important;
        color: #ffffff !important;
    }
    .flight-form-card,
.flight-table-card {
    border-radius: 24px;
    background:
        linear-gradient(180deg, rgba(255,255,255,0.12), rgba(255,255,255,0.04)),
        rgba(15, 23, 42, 0.60) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    box-shadow:
        0 18px 40px rgba(0, 0, 0, 0.22),
        inset 0 1px 0 rgba(255,255,255,0.08);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.flight-form-card .card-header,
.flight-table-card .card-header {
    background: rgba(255, 255, 255, 0.04) !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.10) !important;
    border-top-left-radius: 24px;
    border-top-right-radius: 24px;
}

.sticky-flight-card {
    position: relative;
    top: 0;
}

.flight-table-card .table-responsive {
    overflow-x: auto;
}

.flight-table-card table {
    min-width: 900px;
}

@media (max-width: 991px) {
    .sticky-flight-card {
        position: static;
        top: auto;
    }

    .flight-form-card,
    .flight-table-card {
        border-radius: 20px;
    }

    .flight-form-card .card-header,
    .flight-table-card .card-header {
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
    }
}            
    .alert {
        border: none !important;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }

    .alert-success {
        background: rgba(34, 197, 94, 0.18) !important;
        color: #ffffff !important;
    }

    .alert-warning {
        background: rgba(245, 158, 11, 0.18) !important;
        color: #ffffff !important;
    }

    .alert-danger {
        background: rgba(239, 68, 68, 0.18) !important;
        color: #ffffff !important;
    }

    /* ===== BOOKING STATUS BADGE ===== */
    .confirmed-yellow-text {
        color: #facc15 !important;
        font-weight: 700 !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
    }
    
    .booking-card {
    position: relative;
    overflow: hidden;
    padding: 1.35rem;
    border-radius: 24px;
    background:
        linear-gradient(180deg, rgba(255,255,255,0.14), rgba(255,255,255,0.04)),
        rgba(15, 23, 42, 0.58) !important;
    border: 1px solid rgba(255, 255, 255, 0.14) !important;
    box-shadow:
        0 18px 40px rgba(0, 0, 0, 0.24),
        inset 0 1px 0 rgba(255,255,255,0.10),
        inset 0 -1px 0 rgba(255,255,255,0.03);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    color: #f8fafc !important;
    transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
}

.booking-card::before {
    content: "";
    position: absolute;
    top: -30%;
    left: -10%;
    width: 120%;
    height: 65%;
    background: linear-gradient(
        120deg,
        rgba(255,255,255,0.22) 0%,
        rgba(255,255,255,0.10) 22%,
        rgba(255,255,255,0.03) 45%,
        rgba(255,255,255,0.00) 70%
    );
    transform: rotate(-8deg);
    pointer-events: none;
}

.booking-card::after {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 24px;
    pointer-events: none;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,0.05);
}

.booking-card:hover {
    transform: translateY(-8px);
    box-shadow:
        0 24px 50px rgba(0, 0, 0, 0.30),
        0 10px 22px rgba(14, 165, 233, 0.10),
        inset 0 1px 0 rgba(255,255,255,0.12),
        inset 0 -1px 0 rgba(255,255,255,0.04);
    border-color: rgba(125, 211, 252, 0.28) !important;
}

.booking-card-top {
    position: relative;
    z-index: 2;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 1rem;
    padding-bottom: 0.95rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.10);
}

.booking-card-section {
    position: relative;
    z-index: 2;
    margin-bottom: 1rem;
}

.booking-label {
    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #cbd5e1 !important;
    margin-bottom: 0.32rem;
}

.booking-reference {
    font-size: 1.08rem;
    font-weight: 800;
    color: #ffffff !important;
    word-break: break-word;
    text-shadow: 0 1px 2px rgba(0,0,0,0.25);
}

.booking-value {
    font-size: 1rem;
    font-weight: 700;
    color: #ffffff !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.22);
}

.booking-subvalue {
    font-size: 0.9rem;
    color: #dbe4f0 !important;
    word-break: break-word;
}

.booking-grid {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-top: 0.85rem;
}

.booking-mini-card {
    position: relative;
    overflow: hidden;
    padding: 0.9rem;
    border-radius: 16px;
    background:
        linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.03)),
        rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.10);
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.08),
        0 8px 18px rgba(0,0,0,0.10);
    transition: transform 0.25s ease, background 0.25s ease;
}

.booking-mini-card:hover {
    transform: translateY(-3px);
    background:
        linear-gradient(180deg, rgba(255,255,255,0.13), rgba(255,255,255,0.05)),
        rgba(255, 255, 255, 0.07);
}

.booking-mini-card .booking-value {
    font-size: 0.96rem;
}

.booking-card .btn-air-soft {
    position: relative;
    z-index: 2;
    border-radius: 12px;
    padding: 0.55rem 0.95rem;
    box-shadow: 0 8px 18px rgba(239, 68, 68, 0.14);
}

@media (max-width: 767px) {
    .booking-grid {
        grid-template-columns: 1fr;
    }

    .booking-card {
        border-radius: 20px;
    }

    .booking-card::after {
        border-radius: 20px;
    }
}

.flight-two-col-layout {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.5rem;
    align-items: stretch;
}

.flight-left-col {
    min-width: 0;
}

.flight-right-col {
    min-width: 0;
}


.flight-left-col,
.flight-right-col {
    display: flex;
}

.flight-left-col > .flight-form-card,
.flight-right-col > .flight-list-shell {
    width: 100%;
    min-height: 100%;
    display: flex;
    flex-direction: column;
}

.flight-form-card .card-body,
.flight-list-shell .card-body {
    flex: 1 1 auto;
}

.flight-list-shell .card-body {
    padding-top: 1.25rem;
}

.flight-list-shell .card-header,
.flight-form-card .card-header {
    min-height: 72px;
    display: flex;
    align-items: center;
}

.flight-form-card .card-body {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.flight-form-card form {
    width: 100%;
}

.flight-list-shell .card-body {
    display: flex;
    flex-direction: column;
}

.flight-search-wrap {
    width: 100%;
    max-width: 360px;
}

.flight-search-wrap-full {
    max-width: none;
    flex: 1 1 auto;
}

.flight-list-toolbar {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    margin-bottom: 1.1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(148, 163, 184, 0.18);
}

.flight-carousel-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.flight-carousel-btn {
    width: 42px;
    height: 42px;
    padding: 0;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    line-height: 1;
}

.flight-carousel-viewport {
    position: relative;
    overflow: hidden;
    width: 100%;
    padding: 0.25rem 0;
}

.flight-carousel-track {
    display: flex;
    flex-wrap: nowrap;
    width: 100%;
    transition: transform 0.65s cubic-bezier(0.22, 1, 0.36, 1);
    will-change: transform;
}

.flight-carousel-track .flight-card-col {
    flex: 0 0 100%;
    max-width: 100%;
    padding: 0 0.15rem;
}

.flight-carousel-track .flight-card-col.d-none {
    display: none !important;
}

.flight-carousel-progress {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.45rem;
    margin-top: 1rem;
}

.flight-carousel-dot {
    width: 10px;
    height: 10px;
    border: 0;
    border-radius: 999px;
    background: rgba(148, 163, 184, 0.45);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.2);
    transition: all 0.25s ease;
    padding: 0;
}

.flight-carousel-dot.active {
    width: 28px;
    background: linear-gradient(90deg, #38bdf8, #60a5fa);
    box-shadow: 0 0 14px rgba(56, 189, 248, 0.28);
}

@media (max-width: 991px) {
    .flight-two-col-layout {
        grid-template-columns: 1fr;
    }

    .flight-search-wrap {
        max-width: 100%;
    }
}
    @media (prefers-color-scheme: dark) {
        body {
            background:
                linear-gradient(rgba(2, 6, 23, 0.12), rgba(2, 6, 23, 0.12)),
                url('../assets/img/admin-slide-1.svg') center center / cover no-repeat fixed !important;
        }

        .floating-card,
        .form-shell,
        .table-shell,
        .metric-card,
        .card {
            background: rgba(2, 6, 23, 0.58) !important;
        }

        .section-copy,
        .metric-label,
        .text-muted,
        .small.text-muted {
            color: #d1d5db !important;
        }
    }


:root {
    --font-gold: #d4af37;
    --font-light-pink: #ffd1dc;
    --font-white: #ffffff;
}

body,
.admin-shell,
.admin-shell p,
.admin-shell span,
.admin-shell div,
.admin-shell td,
.admin-shell th,
.admin-shell label,
.admin-shell small,
.admin-shell li,
.admin-shell a,
.admin-shell .card,
.admin-shell .table,
.admin-shell .table td,
.admin-shell .table th,
.admin-shell .form-control,
.admin-shell .form-select,
.admin-shell .alert,
.admin-shell .alert * {
    color: var(--font-white) !important;
}

.admin-shell .section-title,
.admin-shell h1,
.admin-shell h2,
.admin-shell h3,
.admin-shell h4,
.admin-shell h5,
.admin-shell h6,
.admin-shell .metric-value,
.admin-shell .dashboard-metric-value,
.admin-shell .flight-code,
.admin-shell .flight-airport,
.admin-shell .flight-value,
.admin-shell .booking-reference,
.admin-shell .booking-value,
.admin-shell .user-name,
.admin-shell .user-value,
.admin-shell .dashboard-metric-icon,
.admin-shell .user-badge,
.admin-shell .flight-route-arrow,
.admin-shell .confirmed-yellow-text,
.admin-shell .btn-air-primary,
.admin-shell .btn-air-primary * {
    color: var(--font-gold) !important;
    text-shadow: 0 1px 8px rgba(0, 0, 0, 0.28);
}

.admin-shell .section-copy,
.admin-shell .metric-label,
.admin-shell .dashboard-metric-label,
.admin-shell .dashboard-metric-copy,
.admin-shell .dashboard-metric-chip,
.admin-shell .form-label,
.admin-shell .flight-label,
.admin-shell .booking-label,
.admin-shell .booking-subvalue,
.admin-shell .user-label,
.admin-shell .user-subvalue,
.admin-shell .text-muted,
.admin-shell .small.text-muted,
.admin-shell .flight-search-input::placeholder,
.admin-shell .booking-search-input::placeholder,
.admin-shell .user-search-input::placeholder,
.admin-shell .form-control::placeholder {
    color: var(--font-light-pink) !important;
}

.admin-shell .card-header,
.admin-shell .card-header h3,
.admin-shell .table thead th,
.admin-shell .table td,
.admin-shell .table th,
.admin-shell .booking-card *,
.admin-shell .user-card *,
.admin-shell .flight-item-card *,
.admin-shell .dashboard-metric-card * {
    text-shadow: 0 1px 6px rgba(0, 0, 0, 0.22);
}

.admin-shell .btn-air-outline,
.admin-shell .btn-air-outline *,
.admin-shell .dashboard-carousel-btn,
.admin-shell .booking-carousel-btn,
.admin-shell .user-carousel-btn,
.admin-shell .flight-carousel-btn {
    color: var(--font-light-pink) !important;
    font-weight: 700;
}

.admin-shell .btn-air-soft,
.admin-shell .btn-air-soft * {
    color: var(--font-white) !important;
    font-weight: 700;
}

.admin-shell .form-control,
.admin-shell .form-select,
.admin-shell .flight-search-input,
.admin-shell .booking-search-input,
.admin-shell .user-search-input,
.admin-shell input,
.admin-shell select,
.admin-shell textarea {
    color: var(--font-white) !important;
}

.admin-shell .dashboard-carousel-dot.active,
.admin-shell .flight-carousel-dot.active,
.admin-shell .booking-carousel-dot.active,
.admin-shell .user-carousel-dot.active {
    background: linear-gradient(90deg, #d4af37, #ffd1dc) !important;
    box-shadow: 0 0 14px rgba(212, 175, 55, 0.28) !important;
}


/* ===== ADMIN SECTION TITLE MINIMAL LUXURY FIX ===== */
.admin-shell .section-header.center-header {
    text-align: center;
}

.admin-shell .section-header.center-header .section-title {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.52rem 1rem;
    border-radius: 12px;
    background: rgba(18, 18, 18, 0.76) !important;
    border: 1px solid rgba(212, 175, 55, 0.14) !important;
    color: #f5de8a !important;
    font-weight: 700;
    font-size: 1.05rem;
    letter-spacing: 0.01em;
    text-shadow: 0 1px 6px rgba(0, 0, 0, 0.22);
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.admin-shell .section-header.center-header .section-copy {
    margin-top: 0.55rem;
    color: #ead8de !important;
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.16);
}

@media (max-width: 576px) {
    .admin-shell .section-header.center-header .section-title {
        padding: 0.48rem 0.85rem;
        font-size: 0.94rem;
        border-radius: 10px;
    }
}



    /* ===== ADMIN SEND TICKET BUTTON ===== */
    .btn-send-ticket {
        background: linear-gradient(180deg, #ffd1dc, #f7b8c9) !important;
        border: 1px solid #f3a9bf !important;
        color: #d4af37 !important;
        font-weight: 800;
        box-shadow: 0 10px 20px rgba(247, 184, 201, 0.20);
    }

    .btn-send-ticket:hover,
    .btn-send-ticket:focus {
        background: linear-gradient(180deg, #ffc2d2, #f3a9bf) !important;
        border-color: #ea99b4 !important;
        color: #c79d1f !important;
    }

</style>

<div class="container-xl admin-shell">

    <?php if ($flash): ?>
        <div class="alert <?= $flash['type'] === 'danger' ? 'alert-danger' : ($flash['type'] === 'warning' ? 'alert-warning' : 'alert-success') ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($adminSection === 'dashboard'): ?>
        <div class="section-header center-header mt-4">
            <div class="section-title">Admin Dashboard</div>
            <p class="section-copy">Swipe through your summary cards.</p>
        </div>

        <div class="table-shell card border-0 floating-card dashboard-carousel-shell mb-4">
            <div class="card-header">
                <h3 class="fw-bold mb-0">Dashboard Overview</h3>

                <div class="dashboard-carousel-controls">
                    <button type="button" class="btn btn-sm btn-air-outline dashboard-carousel-btn" id="dashboardPrevBtn" aria-label="Previous dashboard card">&#10094;</button>
                    <button type="button" class="btn btn-sm btn-air-outline dashboard-carousel-btn" id="dashboardNextBtn" aria-label="Next dashboard card">&#10095;</button>
                </div>
            </div>

            <div class="card-body">
                <div class="dashboard-carousel-viewport">
                    <div class="dashboard-carousel-track" id="dashboardCardsContainer">
                        <div class="dashboard-card-col">
                            <div class="dashboard-metric-card">
                                <div class="dashboard-metric-top">
                                    <div>
                                        <div class="dashboard-metric-label">Total Flights</div>
                                        <div class="dashboard-metric-value"><?= number_format($flightCount) ?></div>
                                        <p class="dashboard-metric-copy mb-0">Quick view of all available routes and schedules in the system.</p>
                                    </div>
                                    <span class="dashboard-metric-icon">✈️</span>
                                </div>

                                <div class="dashboard-metric-footer">
                                    <span class="dashboard-metric-chip">Flights Module</span>
                                    <a href="index.php?section=flights" class="btn btn-sm btn-air-outline">Open Flights</a>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-card-col">
                            <div class="dashboard-metric-card">
                                <div class="dashboard-metric-top">
                                    <div>
                                        <div class="dashboard-metric-label">Total Bookings</div>
                                        <div class="dashboard-metric-value"><?= number_format($bookingCount) ?></div>
                                        <p class="dashboard-metric-copy mb-0">Track current reservations and review passenger booking details faster.</p>
                                    </div>
                                    <span class="dashboard-metric-icon">🎫</span>
                                </div>

                                <div class="dashboard-metric-footer">
                                    <span class="dashboard-metric-chip">Bookings Module</span>
                                    <a href="index.php?section=bookings" class="btn btn-sm btn-air-outline">Open Bookings</a>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-card-col">
                            <div class="dashboard-metric-card">
                                <div class="dashboard-metric-top">
                                    <div>
                                        <div class="dashboard-metric-label">Registered Users</div>
                                        <div class="dashboard-metric-value"><?= number_format($userCount) ?></div>
                                        <p class="dashboard-metric-copy mb-0">Browse account records, profile details, and user information in one place.</p>
                                    </div>
                                    <span class="dashboard-metric-icon">👤</span>
                                </div>

                                <div class="dashboard-metric-footer">
                                    <span class="dashboard-metric-chip">Users Module</span>
                                    <a href="index.php?section=users" class="btn btn-sm btn-air-outline">Open Users</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-carousel-progress" id="dashboardCarouselDots"></div>
            </div>
        </div>
    <?php endif; ?>
<?php if ($adminSection === 'flights'): ?>
    <div class="section-header center-header mt-4">
        <div class="section-title">Manage Flights</div>
    </div>

    <div class="flight-two-col-layout">
        <div class="flight-left-col">
            <div class="form-shell card border-0 floating-card flight-form-card sticky-flight-card">
                <div class="card-header">
                    <h3 class="fw-bold mb-0"><?php echo $editFlight ? 'Edit Flight' : 'Add New Flight'; ?></h3>
                </div>

                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="<?php echo $editFlight ? 'update_flight' : 'create_flight'; ?>">

                        <?php if ($editFlight): ?>
                            <input type="hidden" name="id" value="<?php echo (int)$editFlight['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Flight Code</label>
                            <input type="text" name="flight_code" class="form-control" required
                                   value="<?php echo e($editFlight['flight_code'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Origin</label>
                            <input type="text" name="origin" class="form-control" required
                                   value="<?php echo e($editFlight['origin'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Destination</label>
                            <input type="text" name="destination" class="form-control" required
                                   value="<?php echo e($editFlight['destination'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Departure Date</label>
                            <input type="date" name="departure_date" class="form-control" required
                                   value="<?php echo e($editFlight['departure_date'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Departure Time</label>
                            <input type="time" name="departure_time" class="form-control" required
                                   value="<?php echo isset($editFlight['departure_time']) ? substr($editFlight['departure_time'], 0, 5) : '08:00'; ?>">
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Economy Fare</label>
                                <input type="number" step="0.01" min="0" name="economy_fare" class="form-control" required
                                       value="<?php echo e($editFlight['economy_fare'] ?? '0.00'); ?>">
                            </div>

                            <div class="col-6 mb-3">
                                <label class="form-label">Premium Fare</label>
                                <input type="number" step="0.01" min="0" name="premium_fare" class="form-control"
                                       value="<?php echo e($editFlight['premium_fare'] ?? '0.00'); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Business Fare</label>
                                <input type="number" step="0.01" min="0" name="business_fare" class="form-control" required
                                       value="<?php echo e($editFlight['business_fare'] ?? '0.00'); ?>">
                            </div>

                            <div class="col-6 mb-3">
                                <label class="form-label">First Class Fare</label>
                                <input type="number" step="0.01" min="0" name="first_class_fare" class="form-control"
                                       value="<?php echo e($editFlight['first_class_fare'] ?? '0.00'); ?>">
                            </div>
                        </div>

                        <div class="inline-actions mt-4 d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-air-primary">
                                <?php echo $editFlight ? 'Save Changes' : 'Add Flight'; ?>
                            </button>

                            <?php if ($editFlight): ?>
                                <a href="index.php?section=flights" class="btn btn-air-outline">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        
<div class="flight-right-col">
    <div class="table-shell card border-0 floating-card flight-list-shell">
        <div class="card-header">
            <h3 class="fw-bold mb-0">Flights List</h3>
        </div>

        <div class="card-body">
            <div class="flight-list-toolbar">
                <div class="flight-search-wrap flight-search-wrap-full">
                    <input
                        type="text"
                        id="flightSearchInput"
                        class="form-control flight-search-input"
                        placeholder="Search flights..."
                        autocomplete="off"
                    >
                </div>

                <div class="flight-carousel-controls">
                    <button type="button" class="btn btn-sm btn-air-outline flight-carousel-btn" id="flightPrevBtn" aria-label="Previous flight">&#10094;</button>
                    <button type="button" class="btn btn-sm btn-air-outline flight-carousel-btn" id="flightNextBtn" aria-label="Next flight">&#10095;</button>
                </div>
            </div>

            <?php if (empty($flights)): ?>
                <p class="mb-0">No flights found.</p>
            <?php else: ?>
                <div class="flight-carousel-viewport">
                    <div class="flight-carousel-track" id="flightCardsContainer">
                        <?php foreach ($flights as $row): ?>
                            <?php
                                $searchText = strtolower(trim(
                                    ($row['flight_code'] ?? '') . ' ' .
                                    ($row['origin'] ?? '') . ' ' .
                                    ($row['destination'] ?? '') . ' ' .
                                    ($row['departure_date'] ?? '') . ' ' .
                                    substr(($row['departure_time'] ?? ''), 0, 5) . ' ' .
                                    ($row['economy_fare'] ?? '') . ' ' .
                                    ($row['premium_fare'] ?? '') . ' ' .
                                    ($row['business_fare'] ?? '') . ' ' .
                                    ($row['first_class_fare'] ?? '')
                                ));
                            ?>
                            <div class="flight-card-col" data-search="<?php echo htmlspecialchars($searchText, ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="flight-item-card">
                                    <div class="flight-item-glow"></div>

                                    <div class="flight-item-top">
                                        <div>
                                            <div class="flight-label">Flight Code</div>
                                            <div class="flight-code"><?php echo e($row['flight_code']); ?></div>
                                        </div>

                                        <div class="flight-actions d-flex gap-2 flex-wrap">
                                            <a href="index.php?section=flights&edit_flight=<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-air-outline">
                                                Edit
                                            </a>

                                            <form method="post" class="d-inline-flex" onsubmit="return confirm('Delete this flight?')">
                                                <input type="hidden" name="action" value="delete_flight">
                                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-air-soft">Delete</button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="flight-route-row">
                                        <div class="flight-airport-block">
                                            <div class="flight-label">Origin</div>
                                            <div class="flight-airport"><?php echo e($row['origin']); ?></div>
                                        </div>

                                        <div class="flight-route-arrow">→</div>

                                        <div class="flight-airport-block text-lg-end">
                                            <div class="flight-label">Destination</div>
                                            <div class="flight-airport"><?php echo e($row['destination']); ?></div>
                                        </div>
                                    </div>

                                    <div class="flight-meta-grid">
                                        <div class="flight-mini-card">
                                            <div class="flight-label">Date</div>
                                            <div class="flight-value"><?php echo e($row['departure_date']); ?></div>
                                        </div>

                                        <div class="flight-mini-card">
                                            <div class="flight-label">Time</div>
                                            <div class="flight-value"><?php echo substr($row['departure_time'] ?? '', 0, 5); ?></div>
                                        </div>

                                        <div class="flight-mini-card">
                                            <div class="flight-label">Economy</div>
                                            <div class="flight-value"><?php echo format_money($row['economy_fare']); ?></div>
                                        </div>

                                        <div class="flight-mini-card">
                                            <div class="flight-label">Premium</div>
                                            <div class="flight-value"><?php echo format_money($row['premium_fare'] ?? 0); ?></div>
                                        </div>

                                        <div class="flight-mini-card">
                                            <div class="flight-label">Business</div>
                                            <div class="flight-value"><?php echo format_money($row['business_fare']); ?></div>
                                        </div>

                                        <div class="flight-mini-card">
                                            <div class="flight-label">First Class</div>
                                            <div class="flight-value"><?php echo format_money($row['first_class_fare'] ?? 0); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flight-carousel-progress" id="flightCarouselDots"></div>

                <div id="flightNoResults" class="text-center py-4" style="display:none;">
                    No matching flights found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<?php if ($adminSection === 'bookings'): ?>
    <div class="section-header center-header mt-4">
        <div class="section-title">All Bookings</div>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="table-shell card border-0 floating-card booking-list-shell">
            <div class="card-header">
                <h3 class="fw-bold mb-0">Bookings List</h3>
            </div>
            <div class="card-body">
                <p class="mb-0">No bookings found.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="table-shell card border-0 floating-card booking-list-shell">
            <div class="card-header">
                <h3 class="fw-bold mb-0">Bookings List</h3>
            </div>

            <div class="card-body">
                <div class="booking-list-toolbar">
                    <div class="booking-search-wrap">
                        <input
                            type="text"
                            id="bookingSearchInput"
                            class="form-control booking-search-input"
                            placeholder="Search bookings..."
                            autocomplete="off"
                        >
                    </div>

                    <div class="booking-carousel-controls">
                        <button type="button" class="btn btn-sm btn-air-outline booking-carousel-btn" id="bookingPrevBtn" aria-label="Previous booking">&#10094;</button>
                        <button type="button" class="btn btn-sm btn-air-outline booking-carousel-btn" id="bookingNextBtn" aria-label="Next booking">&#10095;</button>
                    </div>
                </div>

                <div class="booking-carousel-viewport">
                    <div class="booking-carousel-track" id="bookingCardsContainer">
                        <?php foreach ($bookings as $row): ?>
                            <?php
                                $bookingSearchText = strtolower(trim(
                                    ($row['booking_reference'] ?? '') . ' ' .
                                    ($row['full_name'] ?? '') . ' ' .
                                    ($row['email'] ?? '') . ' ' .
                                    ($row['flight_code'] ?? '') . ' ' .
                                    ($row['departure_date'] ?? '') . ' ' .
                                    substr(($row['departure_time'] ?? ''), 0, 5) . ' ' .
                                    ($row['origin'] ?? '') . ' ' .
                                    ($row['destination'] ?? '') . ' ' .
                                    ($row['seat_no'] ?? '') . ' ' .
                                    ($row['seat_class'] ?? '') . ' ' .
                                    ($row['booking_status'] ?? '') . ' ' .
                                    ($row['fare_amount'] ?? '')
                                ));
                            ?>
                            <div class="booking-card-col" data-search="<?php echo htmlspecialchars($bookingSearchText, ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="booking-card floating-card h-100">
                                    <div class="booking-card-top">
                                        <div>
                                            <div class="booking-label">Reference</div>
                                            <div class="booking-reference"><?php echo e($row['booking_reference']); ?></div>
                                        </div>

                                        <span class="<?php echo booking_status_badge($row['booking_status']); ?> <?php echo strtolower(trim($row['booking_status'])) === 'confirmed' ? 'confirmed-yellow-text' : ''; ?>">
                                            <?php echo e($row['booking_status']); ?>
                                        </span>
                                    </div>

                                    <div class="booking-card-section">
                                        <div class="booking-label">Passenger</div>
                                        <div class="booking-value"><?php echo e($row['full_name']); ?></div>
                                        <div class="booking-subvalue"><?php echo e($row['email']); ?></div>
                                    </div>

                                    <div class="booking-card-section">
                                        <div class="booking-label">Flight</div>
                                        <div class="booking-value"><?php echo e($row['flight_code']); ?></div>
                                        <div class="booking-subvalue">
                                            <?php echo e($row['departure_date']); ?> · <?php echo substr($row['departure_time'] ?? '', 0, 5); ?>
                                        </div>
                                    </div>

                                    <div class="booking-card-section">
                                        <div class="booking-label">Route</div>
                                        <div class="booking-value"><?php echo e($row['origin']); ?> → <?php echo e($row['destination']); ?></div>
                                    </div>

                                    <div class="booking-grid">
                                        <div class="booking-mini-card">
                                            <div class="booking-label">Seat</div>
                                            <div class="booking-value"><?php echo e($row['seat_no']); ?></div>
                                        </div>

                                        <div class="booking-mini-card">
                                            <div class="booking-label">Class</div>
                                            <div class="booking-value"><?php echo e($row['seat_class']); ?></div>
                                        </div>

                                        <div class="booking-mini-card">
                                            <div class="booking-label">Fare</div>
                                            <div class="booking-value"><?php echo format_money($row['fare_amount']); ?></div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="d-flex gap-2 flex-wrap">
                                            <form method="post" class="d-inline-flex">
                                                <input type="hidden" name="action" value="send_ticket_email">
                                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-send-ticket">Send Ticket</button>
                                            </form>

                                            <form method="post" class="d-inline-flex" onsubmit="return confirm('Delete this booking?')">
                                                <input type="hidden" name="action" value="delete_booking">
                                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-air-soft">Delete Booking</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="booking-carousel-progress" id="bookingCarouselDots"></div>

                <div id="bookingNoResults" class="text-center py-4" style="display:none;">
                    No matching bookings found.
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
    <?php if ($adminSection === 'users'): ?>
        <div class="section-header center-header mt-4">
            <div class="section-title">Registered Users</div>
        </div>

        <?php if (empty($users)): ?>
            <div class="table-shell card border-0 floating-card user-list-shell">
                <div class="card-header">
                    <h3 class="fw-bold mb-0">Users List</h3>
                </div>
                <div class="card-body">
                    <p class="mb-0">No users found.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="table-shell card border-0 floating-card user-list-shell">
                <div class="card-header">
                    <h3 class="fw-bold mb-0">Users List</h3>
                </div>

                <div class="card-body">
                    <div class="user-list-toolbar">
                        <div class="user-search-wrap">
                            <input
                                type="text"
                                id="userSearchInput"
                                class="form-control user-search-input"
                                placeholder="Search users..."
                                autocomplete="off"
                            >
                        </div>

                        <div class="user-carousel-controls">
                            <button type="button" class="btn btn-sm btn-air-outline user-carousel-btn" id="userPrevBtn" aria-label="Previous user">&#10094;</button>
                            <button type="button" class="btn btn-sm btn-air-outline user-carousel-btn" id="userNextBtn" aria-label="Next user">&#10095;</button>
                        </div>
                    </div>

                    <div class="user-carousel-viewport">
                        <div class="user-carousel-track" id="userCardsContainer">
                            <?php foreach ($users as $row): ?>
                                <?php
                                    $userSearchText = strtolower(trim(
                                        ($row['id'] ?? '') . ' ' .
                                        ($row['full_name'] ?? '') . ' ' .
                                        ($row['email'] ?? '') . ' ' .
                                        ($row['contact_no'] ?? '') . ' ' .
                                        ($row['birthday'] ?? '') . ' ' .
                                        ($row['passport_no'] ?? '')
                                    ));
                                ?>
                                <div class="user-card-col" data-search="<?php echo htmlspecialchars($userSearchText, ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="user-card floating-card h-100">
                                        <div class="user-card-top">
                                            <div>
                                                <div class="user-label">User</div>
                                                <div class="user-name"><?php echo e($row['full_name']); ?></div>
                                            </div>
                                            <span class="user-badge">#<?php echo (int)$row['id']; ?></span>
                                        </div>

                                        <div class="user-card-section">
                                            <div class="user-label">Email</div>
                                            <div class="user-value"><?php echo e($row['email']); ?></div>
                                        </div>

                                        <div class="user-grid">
                                            <div class="user-mini-card">
                                                <div class="user-label">Contact</div>
                                                <div class="user-value"><?php echo e($row['contact_no'] ?? '—'); ?></div>
                                            </div>

                                            <div class="user-mini-card">
                                                <div class="user-label">Birthday</div>
                                                <div class="user-value"><?php echo e($row['birthday'] ?? '—'); ?></div>
                                            </div>

                                            <div class="user-mini-card">
                                                <div class="user-label">Passport</div>
                                                <div class="user-value"><?php echo e($row['passport_no'] ?? '—'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="user-carousel-progress" id="userCarouselDots"></div>

                    <div id="userNoResults" class="text-center py-4" style="display:none;">
                        No matching users found.
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    function normalizeText(text) {
        return (text || '')
            .toLowerCase()
            .replace(/\s+/g, ' ')
            .trim();
    }

    function initSimpleSearch(inputId, cardSelector, noResultsId) {
        const input = document.getElementById(inputId);
        const cards = document.querySelectorAll(cardSelector);
        const noResults = document.getElementById(noResultsId);

        if (!input || !cards.length) return;

        function filterItems() {
            const query = normalizeText(input.value);
            let visibleCount = 0;

            cards.forEach(function (card) {
                const searchableText = normalizeText(card.getAttribute('data-search'));
                const isMatch = query === '' || searchableText.includes(query);

                card.style.display = isMatch ? '' : 'none';
                if (isMatch) visibleCount++;
            });

            if (noResults) {
                noResults.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        input.addEventListener('input', filterItems);
        filterItems();
    }

    function initCarouselSearch(config) {
        const input = document.getElementById(config.inputId);
        const track = document.getElementById(config.trackId);
        const noResults = document.getElementById(config.noResultsId);
        const dotsWrap = document.getElementById(config.dotsId);
        const prevBtn = document.getElementById(config.prevBtnId);
        const nextBtn = document.getElementById(config.nextBtnId);

        if (!input || !track) return;

        const allCards = Array.from(track.querySelectorAll(config.cardSelector));
        if (!allCards.length) return;

        let currentIndex = 0;
        let autoplayTimer = null;

        function getVisibleCards() {
            return allCards.filter(function (card) {
                return !card.classList.contains('d-none');
            });
        }

        function renderDots() {
            if (!dotsWrap) return;

            const visibleCards = getVisibleCards();
            dotsWrap.innerHTML = '';

            visibleCards.forEach(function (_, index) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = config.dotClass + (index === currentIndex ? ' active' : '');
                dot.setAttribute('aria-label', 'Go to item ' + (index + 1));
                dot.addEventListener('click', function () {
                    currentIndex = index;
                    updateCarousel();
                    restartAutoplay();
                });
                dotsWrap.appendChild(dot);
            });

            dotsWrap.style.display = visibleCards.length > 1 ? 'flex' : 'none';
        }

        function updateButtons() {
            const disabled = getVisibleCards().length <= 1;
            if (prevBtn) prevBtn.disabled = disabled;
            if (nextBtn) nextBtn.disabled = disabled;
        }

        function updateCarousel() {
            const visibleCards = getVisibleCards();

            if (visibleCards.length === 0) {
                currentIndex = 0;
                track.style.transform = 'translateX(0)';
                if (noResults) noResults.style.display = 'block';
                renderDots();
                updateButtons();
                return;
            }

            if (currentIndex >= visibleCards.length) {
                currentIndex = 0;
            }

            track.style.transform = 'translateX(-' + (currentIndex * 100) + '%)';
            if (noResults) noResults.style.display = 'none';
            renderDots();
            updateButtons();
        }

        function filterItems() {
            const query = normalizeText(input.value);
            let visibleCount = 0;

            allCards.forEach(function (card) {
                const searchableText = normalizeText(card.getAttribute('data-search'));
                const isMatch = query === '' || searchableText.includes(query);

                card.classList.toggle('d-none', !isMatch);
                if (isMatch) visibleCount++;
            });

            currentIndex = 0;
            updateCarousel();

            if (noResults) {
                noResults.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        function nextSlide() {
            const visibleCards = getVisibleCards();
            if (visibleCards.length <= 1) return;
            currentIndex = (currentIndex + 1) % visibleCards.length;
            updateCarousel();
        }

        function prevSlide() {
            const visibleCards = getVisibleCards();
            if (visibleCards.length <= 1) return;
            currentIndex = (currentIndex - 1 + visibleCards.length) % visibleCards.length;
            updateCarousel();
        }

        function stopAutoplay() {
            if (autoplayTimer) {
                clearInterval(autoplayTimer);
                autoplayTimer = null;
            }
        }

        function startAutoplay() {
            stopAutoplay();
            if (getVisibleCards().length <= 1) return;
            autoplayTimer = setInterval(nextSlide, 4500);
        }

        function restartAutoplay() {
            updateCarousel();
            startAutoplay();
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                prevSlide();
                startAutoplay();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                nextSlide();
                startAutoplay();
            });
        }

        track.addEventListener('mouseenter', stopAutoplay);
        track.addEventListener('mouseleave', startAutoplay);

        input.addEventListener('input', function () {
            filterItems();
            startAutoplay();
        });

        filterItems();
        startAutoplay();
    }

    /* =========================
       DASHBOARD
       ========================= */

    const dashboardTrack = document.getElementById('dashboardCardsContainer');
    const dashboardDots = document.getElementById('dashboardCarouselDots');
    const dashboardPrevBtn = document.getElementById('dashboardPrevBtn');
    const dashboardNextBtn = document.getElementById('dashboardNextBtn');

    if (dashboardTrack && dashboardPrevBtn && dashboardNextBtn) {
        const dashboardCards = Array.from(dashboardTrack.querySelectorAll('.dashboard-card-col'));
        let dashboardIndex = 0;
        let dashboardTimer = null;

        function renderDashboardDots() {
            if (!dashboardDots) return;
            dashboardDots.innerHTML = '';

            dashboardCards.forEach(function (_, index) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'dashboard-carousel-dot' + (index === dashboardIndex ? ' active' : '');
                dot.setAttribute('aria-label', 'Go to dashboard card ' + (index + 1));
                dot.addEventListener('click', function () {
                    dashboardIndex = index;
                    updateDashboardCarousel();
                    restartDashboardAutoplay();
                });
                dashboardDots.appendChild(dot);
            });

            dashboardDots.style.display = dashboardCards.length > 1 ? 'flex' : 'none';
        }

        function updateDashboardButtons() {
            const disabled = dashboardCards.length <= 1;
            dashboardPrevBtn.disabled = disabled;
            dashboardNextBtn.disabled = disabled;
        }

        function updateDashboardCarousel() {
            if (!dashboardCards.length) return;
            if (dashboardIndex >= dashboardCards.length) dashboardIndex = 0;
            dashboardTrack.style.transform = 'translateX(-' + (dashboardIndex * 100) + '%)';
            renderDashboardDots();
            updateDashboardButtons();
        }

        function nextDashboardSlide() {
            if (dashboardCards.length <= 1) return;
            dashboardIndex = (dashboardIndex + 1) % dashboardCards.length;
            updateDashboardCarousel();
        }

        function prevDashboardSlide() {
            if (dashboardCards.length <= 1) return;
            dashboardIndex = (dashboardIndex - 1 + dashboardCards.length) % dashboardCards.length;
            updateDashboardCarousel();
        }

        function stopDashboardAutoplay() {
            if (dashboardTimer) {
                clearInterval(dashboardTimer);
                dashboardTimer = null;
            }
        }

        function startDashboardAutoplay() {
            stopDashboardAutoplay();
            if (dashboardCards.length <= 1) return;
            dashboardTimer = setInterval(nextDashboardSlide, 4500);
        }

        function restartDashboardAutoplay() {
            updateDashboardCarousel();
            startDashboardAutoplay();
        }

        dashboardPrevBtn.addEventListener('click', function () {
            prevDashboardSlide();
            startDashboardAutoplay();
        });

        dashboardNextBtn.addEventListener('click', function () {
            nextDashboardSlide();
            startDashboardAutoplay();
        });

        dashboardTrack.addEventListener('mouseenter', stopDashboardAutoplay);
        dashboardTrack.addEventListener('mouseleave', startDashboardAutoplay);

        updateDashboardCarousel();
        startDashboardAutoplay();
    }

    /* =========================
       FLIGHTS
       ========================= */

    const hasFlightCarousel =
        document.getElementById('flightPrevBtn') &&
        document.getElementById('flightNextBtn') &&
        document.getElementById('flightCarouselDots');

    if (hasFlightCarousel) {
        initCarouselSearch({
            inputId: 'flightSearchInput',
            trackId: 'flightCardsContainer',
            cardSelector: '.flight-card-col',
            noResultsId: 'flightNoResults',
            dotsId: 'flightCarouselDots',
            prevBtnId: 'flightPrevBtn',
            nextBtnId: 'flightNextBtn',
            dotClass: 'flight-carousel-dot'
        });
    } else {
        initSimpleSearch('flightSearchInput', '.flight-card-col', 'flightNoResults');
    }

    /* =========================
       BOOKINGS
       ========================= */

    const hasBookingCarousel =
        document.getElementById('bookingPrevBtn') &&
        document.getElementById('bookingNextBtn') &&
        document.getElementById('bookingCarouselDots');

    if (hasBookingCarousel) {
        initCarouselSearch({
            inputId: 'bookingSearchInput',
            trackId: 'bookingCardsContainer',
            cardSelector: '.booking-card-col',
            noResultsId: 'bookingNoResults',
            dotsId: 'bookingCarouselDots',
            prevBtnId: 'bookingPrevBtn',
            nextBtnId: 'bookingNextBtn',
            dotClass: 'booking-carousel-dot'
        });
    } else {
        initSimpleSearch('bookingSearchInput', '.booking-card-col', 'bookingNoResults');
    }

    /* =========================
       USERS
       ========================= */

    const hasUserCarousel =
        document.getElementById('userPrevBtn') &&
        document.getElementById('userNextBtn') &&
        document.getElementById('userCarouselDots');

    if (hasUserCarousel) {
        initCarouselSearch({
            inputId: 'userSearchInput',
            trackId: 'userCardsContainer',
            cardSelector: '.user-card-col',
            noResultsId: 'userNoResults',
            dotsId: 'userCarouselDots',
            prevBtnId: 'userPrevBtn',
            nextBtnId: 'userNextBtn',
            dotClass: 'user-carousel-dot'
        });
    } else {
        initSimpleSearch('userSearchInput', '.user-card-col', 'userNoResults');
    }
});
</script>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>