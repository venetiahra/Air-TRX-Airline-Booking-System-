<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Flight.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

$database = new Database();
$db = $database->connect();
$flightModel = new Flight($db);

$airportsStmt = $db->query("
    SELECT DISTINCT CONCAT(origin, ' (', UPPER(LEFT(origin,3)), ')') as airport, 
           LEFT(origin,3) as code 
    FROM flights 
    UNION 
    SELECT DISTINCT CONCAT(destination, ' (', UPPER(LEFT(destination,3)), ')') as airport, 
           LEFT(destination,3) as code 
    FROM flights 
    ORDER BY airport
");
$airports = $airportsStmt->fetchAll(PDO::FETCH_ASSOC);

$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');
$date = trim($_GET['date'] ?? '');
$returnDate = trim($_GET['return_date'] ?? '');
$guests = (int)($_GET['guests'] ?? 1);
$cabin = trim($_GET['cabin'] ?? 'Economy');
$tripType = trim($_GET['trip_type'] ?? 'Round-trip');
$promoCode = trim($_GET['promo'] ?? '');

$results = ($from !== '' || $to !== '' || $date !== '') ? $flightModel->search($from, $to, $date) : $flightModel->read();

$flash = flash_get();
$pageTitle = 'Air-TRX - Book Flights';

require_once __DIR__ . '/includes/header.php';
?>

<style>
    :root {
        --font-gold: #d4af37;
        --font-light-pink: #ffd1dc;
        --font-white: #ffffff;
    }

    .hero-booking,
    .hero-booking p,
    .hero-booking span,
    .hero-booking li,
    .hero-booking strong,
    .hero-booking small,
    .hero-booking .label,
    .hero-booking .search-meta,
    .hero-booking .search-meta span,
    .hero-booking .search-meta div,
    .hero-booking .search-pill,
    .hero-booking .flight-card,
    .hero-booking .flight-card * {
        color: var(--font-white) !important;
    }

    .hero-booking .hero-title,
    .hero-booking h1,
    .hero-booking h2,
    .hero-booking h3,
    .hero-booking h4,
    .hero-booking .hero-plane,
    .hero-booking .hero-chip,
    .hero-booking .search-main-btn {
        color: var(--font-gold) !important;
        text-shadow: 0 1px 10px rgba(0, 0, 0, 0.35);
    }

    .hero-booking .hero-subtitle,
    .hero-booking .label,
    .hero-booking .search-meta .label {
        color: var(--font-light-pink) !important;
    }

    .hero-booking .btn,
    .hero-booking button,
    .hero-booking input,
    .hero-booking select,
    .hero-booking textarea,
    .hero-booking option,
    .hero-booking a,
    .hero-booking .alert,
    .hero-booking .alert * {
        color: var(--font-white) !important;
    }

    .hero-booking .hero-title,
    .hero-booking .hero-subtitle,
    .hero-booking .label,
    .hero-booking .search-shell * {
        text-shadow: 0 1px 6px rgba(0, 0, 0, 0.28);
    }

    .results-section,
    .results-section p,
    .results-section span,
    .results-section div,
    .results-section small,
    .results-section a {
        color: var(--font-white) !important;
    }

    .results-section .section-title,
    .results-section .flight-code,
    .results-section .mini-plane,
    .results-section .fare-line strong {
        color: var(--font-gold) !important;
        text-shadow: 0 1px 8px rgba(0, 0, 0, 0.30);
    }

    .results-section .section-copy,
    .results-section .flight-date,
    .results-section .flight-duration,
    .results-section .fare-line span,
    .results-section .empty-state {
        color: var(--font-light-pink) !important;
    }

    .results-section .flight-route-big,
    .results-section .flight-route-big span {
        color: var(--font-white) !important;
    }

    .flight-carousel-shell {
        position: relative;
        width: 100%;
        max-width: 460px;
        margin: 0 auto;
        padding: 0 3.25rem;
    }

    .flight-carousel-viewport {
        overflow: hidden;
        width: 100%;
    }

    .flight-carousel-track {
        display: flex;
        width: 100%;
        transition: transform 0.7s ease;
        will-change: transform;
    }

    .flight-card-slide {
        min-width: 100%;
        flex: 0 0 100%;
        display: flex;
        justify-content: center;
        align-items: stretch;
        padding: 0.45rem;
        box-sizing: border-box;
    }

    .results-section .flight-preview-card {
        margin-left: auto !important;
        margin-right: auto !important;
        max-width: 360px !important;
        width: 100% !important;
        border: 1px solid rgba(212, 175, 55, 0.45);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);
    }

    .results-section .flight-preview-card .btn-air-primary,
    .results-section .flight-preview-card .btn-air-primary * {
        color: var(--font-gold) !important;
        font-weight: 700;
    }

    .results-section .flight-preview-card .btn-air-outline,
    .results-section .flight-preview-card .btn-air-outline * {
        color: var(--font-light-pink) !important;
        font-weight: 700;
    }

    .results-section .flight-preview-card *,
    .results-section .section-header * {
        text-shadow: 0 1px 6px rgba(0, 0, 0, 0.28);
    }

    .flight-carousel-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 42px;
        height: 42px;
        border-radius: 999px;
        border: 1px solid rgba(212, 175, 55, 0.55);
        background: rgba(255, 255, 255, 0.10);
        backdrop-filter: blur(10px);
        color: var(--font-gold) !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        line-height: 1;
        z-index: 2;
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.16);
    }

    .flight-carousel-btn:hover {
        background: rgba(255, 209, 220, 0.14);
        color: var(--font-white) !important;
    }

    .flight-carousel-btn.prev { left: 0; }
    .flight-carousel-btn.next { right: 0; }

    .flight-carousel-dots {
        display: flex;
        justify-content: center;
        gap: 0.45rem;
        margin-top: 1rem;
    }

    .flight-carousel-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        border: none;
        background: rgba(255, 255, 255, 0.32);
        box-shadow: inset 0 0 0 1px rgba(212, 175, 55, 0.18);
        transition: transform 0.25s ease, background 0.25s ease;
    }

    .flight-carousel-dot.is-active {
        background: var(--font-gold);
        transform: scale(1.2);
    }

    @media (max-width: 576px) {
        .flight-carousel-shell {
            padding: 0 2.4rem;
        }

        .flight-carousel-btn {
            width: 38px;
            height: 38px;
        }
    }
</style>


<section class="hero-booking">
    <div class="container-xl">
        <?php if ($flash): ?>
            <div class="alert <?php echo $flash['type'] === 'danger' ? 'alert-danger' : ($flash['type'] === 'warning' ? 'alert-warning' : 'alert-success'); ?>">
                <?php echo e($flash['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="hero-center floating-main">
            <span class="hero-chip"></span>
            <h1 class="hero-title">Find Your Next Flight</h1>
            <p class="hero-subtitle">Book premium flights with ease</p>
            <div class="hero-visuals">
                <div class="cloud cloud-1"></div>
                <div class="cloud cloud-2"></div>
                <div class="cloud cloud-3"></div>
                <div class="hero-plane">★</div>
            </div>
        </div>
        
             <!-- FIXED RESPONSIVE SEARCH FORM -->
        <div class="search-shell bubbly-shell" id="search-box">
            <form method="get">
                
                <!-- Top Bar -->
                <div class="search-top-bar d-flex flex-wrap gap-3 align-items-end mb-4">
                    
                    <!-- Trip Type -->
                    <div class="search-pill flex-grow-1" style="min-width: 160px;">
                        <div class="search-meta">
                            <span class="label">Trip type</span>
                            <select name="trip_type" class="form-select form-select-lite w-100">
                                <option value="Round-trip" <?php echo $tripType === 'Round-trip' ? 'selected' : ''; ?>>Round-trip</option>
                                <option value="One-way" <?php echo $tripType === 'One-way' ? 'selected' : ''; ?>>One-way</option>
                            </select>
                        </div>
                    </div>

                    <!-- Guests & Cabin -->
                    <div class="search-pill flex-grow-1" style="min-width: 220px;">
                        <div class="search-meta">
                            <span class="label">Guests & Cabin</span>
                            <div class="d-flex gap-2">
                                <select name="guests" class="form-select form-select-lite" style="max-width: 90px;">
                                    <?php for($i = 1; $i <= 99; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo $guests === $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                                <select name="cabin" class="form-select form-select-lite flex-grow-1">
                                    <option value="Economy" <?php echo $cabin === 'Economy' ? 'selected' : ''; ?>>Economy</option>
                                    <option value="Premium" <?php echo $cabin === 'Premium' ? 'selected' : ''; ?>>Premium</option>
                                    <option value="Business" <?php echo $cabin === 'Business' ? 'selected' : ''; ?>>Business</option>
                                    <option value="First Class" <?php echo $cabin === 'First Class' ? 'selected' : ''; ?>>First Class</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Promo Code - NOW PROPERLY ALIGNED -->
                    <div class="search-pill flex-grow-1" style="min-width: 160px;">
                        <div class="search-meta">
                            <span class="label">Promo code</span>
                            <input type="text" name="promo" class="form-control promo-input w-100" 
                                   placeholder="Enter promo code" value="<?php echo e($promoCode); ?>">
                        </div>
                    </div>

                    <!-- Direct flights checkbox -->
                    <div class="search-pill search-check flex-shrink-0">
                        <label class="check-inline mb-0">
                            <input type="checkbox" name="direct_only" value="1" <?php echo isset($_GET['direct_only']) ? 'checked' : ''; ?>>
                            Direct flights only
                        </label>
                    </div>
                </div>

                <!-- Bottom Bar -->
                <div class="search-bottom-bar d-flex flex-wrap gap-3 align-items-end">
                    
                    <!-- From -->
                    <div class="search-box-card flex-grow-1" style="min-width: 180px;">
                        <div class="search-meta">
                            <span class="label">From</span>
                            <select name="from" class="form-select form-control-lite w-100">
                                <option value="">Any airport</option>
                                <?php foreach($airports as $airport): ?>
                                    <option value="<?php echo e($airport['code']); ?>" <?php echo $from === $airport['code'] ? 'selected' : ''; ?>>
                                        <?php echo e($airport['airport']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Swap -->
                    <div class="swap-star d-none d-md-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        ★
                    </div>

                    <!-- To -->
                    <div class="search-box-card flex-grow-1" style="min-width: 180px;">
                        <div class="search-meta">
                            <span class="label">To</span>
                            <select name="to" class="form-select form-control-lite w-100">
                                <option value="">Any airport</option>
                                <?php foreach($airports as $airport): ?>
                                    <option value="<?php echo e($airport['code']); ?>" <?php echo $to === $airport['code'] ? 'selected' : ''; ?>>
                                        <?php echo e($airport['airport']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="date-box-card date-grid-card flex-grow-1" style="min-width: 260px;">
                        <div class="d-flex flex-column flex-sm-row gap-3">
                            <div class="date-col flex-grow-1">
                                <span class="label">Depart</span>
                                <input type="date" name="date" class="form-control form-control-lite w-100" value="<?php echo e($date); ?>">
                            </div>
                            <div class="date-divider d-none d-sm-block"></div>
                            <div class="date-col flex-grow-1">
                                <span class="label">Return</span>
                                <input type="date" name="return_date" class="form-control form-control-lite w-100" value="<?php echo e($returnDate); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Search Button -->
                    <div class="flex-grow-1 flex-shrink-0" style="min-width: 160px;">
                        <button type="submit" class="btn btn-air-primary w-100 search-main-btn py-3">
                            Search Flights
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<section class="results-section">
    <div class="container-xl">
        <div class="section-header center-header">
            <div class="section-title"><?php echo count($results); ?> Available Flights</div>
            <p class="section-copy">Browse your options with a smooth flight card carousel.</p>
        </div>

        <?php if (!empty($results)): ?>
            <div class="flight-carousel-shell" id="flight-carousel-shell">
                <button type="button" class="flight-carousel-btn prev" id="flight-prev" aria-label="Previous flight">&#10094;</button>

                <div class="flight-carousel-viewport">
                    <div class="flight-carousel-track" id="flight-carousel-track">
                        <?php foreach ($results as $flight): ?>
                            <div class="flight-card-slide">
                                <div class="flight-preview-card floating-card">
                                    <div class="flight-preview-top">
                                        <div>
                                            <div class="flight-code"><?php echo e($flight['flight_code']); ?></div>
                                            <div class="flight-date"><?php echo e($flight['departure_date']); ?> · <?php echo e(substr($flight['departure_time'],0,5)); ?></div>
                                        </div>
                                        <div class="mini-plane">★</div>
                                    </div>
                                    <div class="flight-route-big"><?php echo e($flight['origin']); ?> <span>→</span> <?php echo e($flight['destination']); ?></div>
                                    <?php if (isset($flight['duration'])): ?>
                                        <div class="flight-duration"><?php echo e($flight['duration']); ?></div>
                                    <?php endif; ?>
                                    <div class="fare-stack">
                                        <div class="fare-line"><span>Economy</span><strong><?php echo e(format_money($flight['economy_fare'])); ?></strong></div>
                                        <div class="fare-line"><span>Premium</span><strong><?php echo e(format_money($flight['premium_fare'] ?? 0)); ?></strong></div>
                                        <div class="fare-line"><span>Business</span><strong><?php echo e(format_money($flight['business_fare'])); ?></strong></div>
                                        <div class="fare-line"><span>First Class</span><strong><?php echo e(format_money($flight['first_class_fare'] ?? 0)); ?></strong></div>
                                    </div>
                                    <div class="inline-actions center-actions mt-3">
                                        <a href="book.php?flight_id=<?php echo (int)$flight['id']; ?>&guests=<?php echo $guests; ?>&cabin=<?php echo urlencode($cabin); ?>" class="btn btn-air-primary">Book Flight</a>
                                        <a href="book.php?flight_id=<?php echo (int)$flight['id']; ?>#seat-map" class="btn btn-air-outline">View Seats</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="button" class="flight-carousel-btn next" id="flight-next" aria-label="Next flight">&#10095;</button>
            </div>

            <?php if (count($results) > 1): ?>
                <div class="flight-carousel-dots" id="flight-carousel-dots">
                    <?php foreach ($results as $index => $flight): ?>
                        <button type="button" class="flight-carousel-dot <?php echo $index === 0 ? 'is-active' : ''; ?>" data-slide="<?php echo $index; ?>" aria-label="Go to flight <?php echo $index + 1; ?>"></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state text-center">No flights matched your search. Try different dates or airports.</div>
        <?php endif; ?>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const track = document.getElementById('flight-carousel-track');
        const prevBtn = document.getElementById('flight-prev');
        const nextBtn = document.getElementById('flight-next');
        const shell = document.getElementById('flight-carousel-shell');
        const dots = Array.from(document.querySelectorAll('.flight-carousel-dot'));

        if (!track || !prevBtn || !nextBtn || !shell) {
            return;
        }

        const slides = Array.from(track.querySelectorAll('.flight-card-slide'));
        if (slides.length <= 1) {
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            return;
        }

        let currentIndex = 0;
        let autoPlay = null;

        function updateCarousel(index) {
            currentIndex = (index + slides.length) % slides.length;
            track.style.transform = 'translateX(-' + (currentIndex * 100) + '%)';

            dots.forEach(function (dot, dotIndex) {
                dot.classList.toggle('is-active', dotIndex === currentIndex);
            });
        }

        function nextSlide() {
            updateCarousel(currentIndex + 1);
        }

        function prevSlide() {
            updateCarousel(currentIndex - 1);
        }

        function startAutoPlay() {
            stopAutoPlay();
            autoPlay = window.setInterval(nextSlide, 3500);
        }

        function stopAutoPlay() {
            if (autoPlay) {
                window.clearInterval(autoPlay);
                autoPlay = null;
            }
        }

        prevBtn.addEventListener('click', function () {
            prevSlide();
            startAutoPlay();
        });

        nextBtn.addEventListener('click', function () {
            nextSlide();
            startAutoPlay();
        });

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                updateCarousel(parseInt(dot.getAttribute('data-slide'), 10) || 0);
                startAutoPlay();
            });
        });

        shell.addEventListener('mouseenter', stopAutoPlay);
        shell.addEventListener('mouseleave', startAutoPlay);
        shell.addEventListener('focusin', stopAutoPlay);
        shell.addEventListener('focusout', startAutoPlay);

        window.addEventListener('resize', function () {
            updateCarousel(currentIndex);
        });

        updateCarousel(0);
        startAutoPlay();
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>