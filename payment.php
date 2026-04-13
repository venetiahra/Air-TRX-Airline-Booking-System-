<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Flight.php';
require_once __DIR__ . '/classes/Booking.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/Mailer.php';
require_user_login();
if (!isset($_SESSION['checkout'])) {
    flash_set('warning', 'Please choose a flight and seat first.');
    redirect_to('index.php');
}
$database = new Database();
$db = $database->connect();
$flightModel = new Flight($db);
$bookingModel = new Booking($db);
$userModel = new User($db);
$checkout = $_SESSION['checkout'];
$flight = $flightModel->getById((int)$checkout['flight_id']);
if (!$flight) {
    unset($_SESSION['checkout']);
    flash_set('warning', 'Selected flight could not be found.');
    redirect_to('index.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = trim($_POST['payment_method'] ?? '');
    if ($method === '') {
        $error = 'Please choose a payment method.';
    } elseif (!$bookingModel->isSeatAvailable((int)$checkout['flight_id'], $checkout['seat_no'])) {
        $error = 'Selected seat is no longer available.';
    } else {
        $reference = $bookingModel->generateReference();
        $bookingData = [
            'user_id' => (int)$_SESSION['user_id'],
            'flight_id' => (int)$checkout['flight_id'],
            'booking_reference' => $reference,
            'seat_no' => $checkout['seat_no'],
            'seat_class' => $checkout['seat_class'],
            'booking_status' => 'Confirmed',
            'fare_amount' => $checkout['fare_amount'],
            'payment_method' => $method,
            'payment_status' => 'Paid'
        ];
        $bookingModel->create($bookingData);
        $user = $userModel->getById((int)$_SESSION['user_id']);
        $mailer = new AppMailer();
        $mailer->sendBookingConfirmation($user, $flight, $bookingData);
        unset($_SESSION['checkout']);
        flash_set('success', 'Payment successful. Your booking is confirmed.');
        redirect_to('my_bookings.php');
    }
}
$pageTitle = 'Payment - Air-TRX';
require_once __DIR__ . '/includes/header.php';
?>
<section class="results-section">
    <div class="container-xl">
        <div class="section-header center-header">
            <div class="section-title">Checkout & Payment</div>
            <p class="section-copy">Review your booking details and complete your payment.</p>
        </div>
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="summary-card floating-card">
                    <div class="summary-title">Booking Review</div>
                    <ul class="summary-list">
                        <li><span>Passenger</span><strong><?php echo e(current_user_name()); ?></strong></li>
                        <li><span>Flight</span><strong><?php echo e($flight['flight_code']); ?></strong></li>
                        <li><span>Route</span><strong><?php echo e($flight['origin']); ?> → <?php echo e($flight['destination']); ?></strong></li>
                        <li><span>Date</span><strong><?php echo e($flight['departure_date']); ?></strong></li>
                        <li><span>Time</span><strong><?php echo e(substr($flight['departure_time'],0,5)); ?></strong></li>
                        <li><span>Seat</span><strong><?php echo e($checkout['seat_no']); ?></strong></li>
                        <li><span>Class</span><strong><?php echo e($checkout['seat_class']); ?></strong></li>
                        <li><span>Total</span><strong><?php echo e(format_money($checkout['fare_amount'])); ?></strong></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="form-shell card border-0 floating-card">
                    <div class="card-header"><h3 class="fw-bold mb-0">Payment Method</h3></div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Choose payment</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="">Select method</option>
                                    <option value="GCash">GCash</option>
                                    <option value="Card">Credit/Debit Card</option>
                                    <option value="Pay at Airport">Pay at Airport</option>
                                </select>
                            </div>
                            <div class="notice-box">Your booking will be confirmed immediately after checkout.</div>
                            <div class="inline-actions center-actions mt-3">
                                <button type="submit" class="btn btn-air-primary w-100">Pay & Confirm Booking</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>