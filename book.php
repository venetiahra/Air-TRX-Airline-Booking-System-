<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Flight.php';
require_once __DIR__ . '/classes/Booking.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

$database = new Database();
$db = $database->connect();
$flightModel = new Flight($db);
$bookingModel = new Booking($db);

$flightId = (int)($_GET['flight_id'] ?? 0);
$flight = $flightModel->getById($flightId);

if(!$flight) {
    flash_set('warning', 'Flight not found.');
    redirect_to('index.php');
}

if(!is_user_logged_in()) {
    flash_set('warning', 'Please log in or create an account to continue booking.');
    redirect_to('login.php?redirect=' . urlencode('book.php?flight_id=' . $flightId));
}

$error = '';
$selectedSeat = '';
$selectedClass = 'Economy';
$selectedFare = $flight['economy_fare'];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedSeat = strtoupper(trim($_POST['seat_no'] ?? ''));
    $selectedClass = trim($_POST['seat_class'] ?? 'Economy');
    $selectedFare = $_POST['fare_amount'] ?? $flight['economy_fare'];
    $payment_method = $_POST['payment_method'] ?? 'Cash (24 hours)';
    
    try {
        if($selectedSeat === '') throw new Exception('Please select a seat.');
        if(!$bookingModel->isSeatAvailable($flightId, $selectedSeat)) {
            throw new Exception('Selected seat is already occupied.');
        }
        
        $ref = $bookingModel->generateReference();
        $bookingModel->create([
            'user_id' => (int)$_SESSION['user_id'],
            'flight_id' => $flightId,
            'booking_reference' => $ref,
            'seat_no' => $selectedSeat,
            'seat_class' => $selectedClass,
            'booking_status' => 'Confirmed',
            'fare_amount' => $selectedFare,
            'payment_method' => $payment_method
        ]);
        
        flash_set('success', 'Flight booked successfully! Payment: ' . $payment_method);
        redirect_to('my_bookings.php');
        
    } catch(Throwable $e) {
        $error = $e->getMessage();
    }
}

$bookedSeats = $bookingModel->getBookedSeatsByFlight($flightId);
$pageTitle = 'Book Flight - Air-TRX';
require_once __DIR__ . '/includes/header.php';
?>

<style>
.payment-options .form-check { 
    margin-bottom: 12px; 
    padding: 12px; 
    border: 1px solid #e9ecef; 
    border-radius: 8px; 
    background: #f8f9fa;
    transition: all 0.3s ease;
}
.payment-options .form-check:hover { 
    background: #e3f2fd; 
    border-color: #2196f3;
}
.payment-options .form-check-input { margin-top: 0.2em; }
.selected-seat-pill {
    display: inline-block;
    background: #e3f2fd;
    padding: 8px 16px;
    border-radius: 25px;
    margin: 0 8px 8px 0;
    font-size: 14px;
    font-weight: 500;
}
</style>

<section class="booking-page">
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="section-header center-header mb-4">
                    <div class="section-title">Complete your booking</div>
                    <p class="section-copy">
                        <?php echo e($flight['flight_code']); ?> · 
                        <?php echo e($flight['origin']); ?> → <?php echo e($flight['destination']); ?> · 
                        <?php echo e($flight['departure_date']); ?> · 
                        <?php echo e(substr($flight['departure_time'], 0, 5)); ?>
                    </p>
                </div>
                
                <?php if($error !== ''): ?>
                    <div class="alert alert-danger"><?php echo e($error); ?></div>
                <?php endif; ?>

                <div class="row g-4 justify-content-center">
                    <div class="col-lg-8 col-xl-7">
                        <div class="form-shell card border-0 floating-card mx-auto seat-map-wrap booking-seat-wrap" id="seat-map" 
                             data-public-seat-map 
                             data-booked-seats='<?php echo e(json_encode(array_values($bookedSeats))); ?>'
                             data-economy-fare='<?php echo e(number_format((float)$flight['economy_fare'], 2, '.', '')); ?>'
                             data-premium-fare='<?php echo e(number_format((float)($flight['premium_fare'] ?? 0), 2, '.', '')); ?>'
                             data-business-fare='<?php echo e(number_format((float)$flight['business_fare'], 2, '.', '')); ?>'
                             data-first-fare='<?php echo e(number_format((float)($flight['first_class_fare'] ?? 0), 2, '.', '')); ?>'>
                            
                            <div class="card-body p-4">
                                <div class="seat-map-header mb-3">
                                    <div class="selected-seat-pill">Selected Seat: <span id="selectedSeatLabel"><?php echo e($selectedSeat ?: '—'); ?></span></div>
                                    <div class="selected-seat-pill">Class: <span id="selectedSeatClass"><?php echo e($selectedClass); ?></span></div>
                                    <div class="selected-seat-pill">Fare: <span id="selectedFareLabel"><?php echo e(format_money($selectedFare)); ?></span></div>
                                </div>
                                
                                <div class="seat-legend mb-3">
                                    <span><i class="legend-available"></i> Available</span>
                                    <span><i class="legend-selected"></i> Selected</span>
                                    <span><i class="legend-booked"></i> Occupied</span>
                                </div>
                                
                                <div class="cabin-bands mb-3">
                                    <span class="band business-band">First Class · Row 1</span>
                                    <span class="band premium-band">Business · Rows 2-3</span>
                                    <span class="band economy-band">Premium · Rows 4-5</span>
                                    <span class="band economy-band">Economy · Rows 6-12</span>
                                </div>
                                
                                <div id="publicSeatGrid" class="seat-grid public-grid"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-xl-4">
                        <div class="form-shell card border-0 floating-card mx-auto summary-card">
                            <div class="card-header">
                                <div class="summary-title">Booking Summary</div>
                            </div>
                            <div class="card-body p-4">
                                <ul class="summary-list mb-4">
                                    <li><span>Passenger</span><strong><?php echo e(current_user_name()); ?></strong></li>
                                    <li><span>Route</span><strong><?php echo e($flight['origin']); ?> → <?php echo e($flight['destination']); ?></strong></li>
                                    <li><span>Flight</span><strong><?php echo e($flight['flight_code']); ?></strong></li>
                                    <li><span>Date</span><strong><?php echo e($flight['departure_date']); ?></strong></li>
                                    <li><span>Time</span><strong><?php echo e(substr($flight['departure_time'], 0, 5)); ?></strong></li>
                                </ul>
                                
                                <!-- YOUR PAYMENT OPTIONS -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold mb-2">Payment Method</label>
                                    <div class="payment-options">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="cash" value="Cash (24 hours)" checked>
                                            <label class="form-check-label" for="cash">
                                                💰 Cash (24 hours)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="credit" value="Credit Card">
                                            <label class="form-check-label" for="credit">
                                                💳 Credit Card
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="center" value="Payment Center">
                                            <label class="form-check-label" for="center">
                                                🏪 Payment Center
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <form method="post">
                                    <input type="hidden" name="seat_no" id="seat_no" value="<?php echo e($selectedSeat); ?>">
                                    <input type="hidden" name="seat_class" id="seat_class" value="<?php echo e($selectedClass); ?>">
                                    <input type="hidden" name="fare_amount" id="fare_amount" value="<?php echo e(number_format((float)$selectedFare, 2, '.', '')); ?>">
                                    <button type="submit" class="btn btn-air-primary w-100 py-3 fs-5">
                                        Confirm & Pay <strong><?php echo e(format_money($selectedFare)); ?></strong>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>