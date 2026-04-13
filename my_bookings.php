<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Booking.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

require_user_login();

$database = new Database();
$db = $database->connect();
$bookingModel = new Booking($db);
$bookings = $bookingModel->getByUserId((int)$_SESSION['user_id']);
$flash = flash_get();

$pageTitle = 'My Bookings - Air-TRX';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    .results-section {
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.10), rgba(255, 255, 255, 0.10)),
            url('assets/img/admin-slide-2.svg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        min-height: 100vh;
        position: relative;
        padding: 3rem 0;
    }

    .results-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(2, 6, 23, 0.34);
        z-index: 1;
    }

    .results-section > .container-xl {
        position: relative;
        z-index: 2;
    }

    .section-title {
        color: #fce7f3 !important;
        font-weight: 800;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
    }

    .section-copy {
        color: #fce7f3 !important;
        font-weight: 500;
        text-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
    }

    .booking-list-shell,
    .form-shell {
        max-width: 1180px;
        margin: 0 auto;
        background:
            radial-gradient(circle at top left, rgba(244, 114, 182, 0.18), transparent 35%),
            radial-gradient(circle at top right, rgba(251, 207, 232, 0.16), transparent 30%),
            linear-gradient(180deg, rgba(255,255,255,0.14), rgba(255,255,255,0.04)),
            rgba(0, 0, 0, 0.80) !important;
        border: 1px solid rgba(251, 207, 232, 0.18) !important;
        border-radius: 20px;
        box-shadow:
            0 18px 40px rgba(0, 0, 0, 0.24),
            0 8px 22px rgba(244, 114, 182, 0.08),
            inset 0 1px 0 rgba(255,255,255,0.10);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    .booking-list-shell,
    .booking-list-shell .card-body,
    .booking-list-shell small,
    .booking-list-shell span,
    .booking-list-shell p,
    .booking-list-shell h1,
    .booking-list-shell h2,
    .booking-list-shell h3,
    .booking-list-shell h4,
    .booking-list-shell h5,
    .booking-list-shell h6,
    .booking-list-shell div,
    .form-shell,
    .form-shell .card-body,
    .form-shell p,
    .form-shell h5,
    .form-shell div {
        color: #ffffff !important;
    }

    .booking-list-shell .card-header {
        background:
            linear-gradient(90deg, rgba(244, 114, 182, 0.16), rgba(251, 207, 232, 0.08)),
            rgba(255, 255, 255, 0.05) !important;
        border-bottom: 1px solid rgba(251, 207, 232, 0.18) !important;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
        min-height: 62px;
        display: flex;
        align-items: center;
        padding: 0.95rem 1.1rem;
    }

    .booking-list-shell .card-header h3 {
        color: #ffffff !important;
        font-weight: 800;
        margin: 0;
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
        border-bottom: 1px solid rgba(255, 255, 255, 0.10);
    }

    .booking-search-wrap {
        width: 100%;
        max-width: none;
        flex: 1 1 auto;
    }

    .booking-search-input {
        background: rgba(255,255,255,0.48) !important; /* transparent light gray */
        border: 1px solid rgba(236, 72, 153, 0.12) !important; /* light pink tint */
        color: #ffffff !important;
        border-radius: 12px !important;
        min-height: 42px;
        font-size: 0.94rem;
        font-family: "Segoe UI", "Inter", "Poppins", sans-serif;
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.40),
            0 6px 14px rgba(17,24,39,0.05);
    }

    .booking-search-input::placeholder {
        color: #cbd5e1 !important;
        opacity: 0.78;
    }

    .booking-search-input:focus {
        background: rgba(255,255,255,0.66) !important;
        border-color: rgba(244, 114, 182, 0.32) !important;
        box-shadow: 0 0 0 0.20rem rgba(252, 211, 77, 0.18) !important; /* yellow glow */
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
        background: rgba(252, 231, 243, 0.72) !important; /* light pink */
        border: 1px solid rgba(244, 114, 182, 0.28) !important; /* yellow */
        color: #ffffff !important; /* black */
    }

    .booking-carousel-btn:hover {
        background: rgba(254, 249, 195, 0.86) !important; /* yellow */
        border-color: rgba(244, 114, 182, 0.30) !important; /* pink */
        color: #ffffff !important;
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
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.25);
        transition: all 0.25s ease;
        padding: 0;
    }

    .booking-carousel-dot.active {
        width: 28px;
        background: linear-gradient(90deg, #f472b6, #f9a8d4);
        box-shadow: 0 0 16px rgba(244, 114, 182, 0.34);
    }

    .booking-card {
        position: relative;
        overflow: hidden;
        padding: 1.1rem;
        border-radius: 18px;
        background:
            linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02)),
            rgba(0, 0, 0, 0.88); /* deeper black cards */
        border: 1px solid rgba(251, 207, 232, 0.22) !important;
        box-shadow:
            0 14px 28px rgba(0,0,0,0.28),
            0 8px 18px rgba(0,0,0,0.18),
            inset 0 1px 0 rgba(255,255,255,0.08);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
    }

    .booking-card::before {
        content: "";
        position: absolute;
        top: -30%;
        left: -10%;
        width: 120%;
        height: 65%;
        background: linear-gradient(120deg, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0.08) 22%, rgba(255,255,255,0.03) 42%, rgba(255,255,255,0.00) 70%);
        transform: rotate(-8deg);
        pointer-events: none;
    }

    .booking-card:hover {
        transform: translateY(-6px);
        box-shadow:
            0 24px 48px rgba(0,0,0,0.36),
            0 14px 28px rgba(255,255,255,0.05);
        border-color: rgba(255,255,255,0.22) !important;
    }

    .booking-card-top,
    .booking-card-section,
    .booking-grid,
    .booking-card-footer {
        position: relative;
        z-index: 2;
    }

    .booking-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 0.85rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid rgba(254, 249, 195, 0.18);
    }

    .booking-card-section {
        margin-bottom: 0.85rem;
    }

    .booking-label {
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #fda4af !important;
        margin-bottom: 0.28rem;
    }

    .booking-reference {
        font-size: 1.04rem;
        font-weight: 800;
        color: #ffffff !important;
        word-break: break-word;
    }

    .booking-value {
        font-size: 0.96rem;
        font-weight: 700;
        color: #ffffff !important;
        word-break: break-word;
    }

    .booking-subvalue {
        font-size: 0.86rem;
        color: #fda4af !important;
        word-break: break-word;
    }

    .booking-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-top: 0.75rem;
    }

    .booking-mini-card {
        padding: 0.75rem;
        border-radius: 13px;
        background:
            linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02)),
            rgba(0, 0, 0, 0.72);
        border: 1px solid rgba(251, 207, 232, 0.18);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.06),
            0 8px 18px rgba(0,0,0,0.16);
    }

    .seat-badge {
        background: rgba(252, 231, 243, 0.92) !important; /* light pink */
        color: #ffffff !important;
        font-weight: 800 !important;
        border: 1px solid rgba(244, 114, 182, 0.28);
        padding: 0.45rem 0.65rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.22), 0 4px 10px rgba(244, 114, 182, 0.12);
    }

    .payment-badge {
        background: rgba(252, 231, 243, 0.92) !important; /* light pink */
        color: #ffffff !important;
        font-weight: 800 !important;
        border: 1px solid rgba(244, 114, 182, 0.28);
        padding: 0.45rem 0.65rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.22), 0 4px 10px rgba(244, 114, 182, 0.12);
    }

    .fare-text {
        color: #fda4af !important;
        font-weight: 800;
    }

    .btn-air-primary {
        background: rgba(17, 24, 39, 0.92) !important; /* black */
        border-color: rgba(17, 24, 39, 0.92) !important;
        color: #ffffff !important; /* soft yellow */
        font-weight: 700;
    }

    .btn-air-primary:hover {
        background: rgba(0, 0, 0, 0.92) !important;
        border-color: rgba(0, 0, 0, 0.92) !important;
        color: #ffffff !important;
    }

    .alert-success {
        background: rgba(255,255,255,0.72) !important;
        color: #ffffff !important;
        border: 1px solid rgba(244, 114, 182, 0.18) !important;
    }

    .alert-warning {
        background: rgba(254,249,195,0.88) !important;
        color: #ffffff !important;
        border: 1px solid rgba(244, 114, 182, 0.20) !important;
        font-weight: 700;
    }

    .alert-danger {
        background: rgba(252,231,243,0.88) !important;
        color: #ffffff !important;
        border: 1px solid rgba(244, 114, 182, 0.18) !important;
    }

    .empty-state {
        background: rgba(0, 0, 0, 0.58);
        padding: 2rem;
        border-radius: 16px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(251, 207, 232, 0.18);
        text-align: center;
    }

    .empty-state i {
        color: #ffffff !important;
    }

    .empty-state h5 {
        color: #ffffff !important;
        font-weight: 800;
    }

    .empty-state p {
        color: #cbd5e1 !important;
        font-weight: 600;
    }

    @media (max-width: 767px) {
        .results-section {
            padding: 2rem 0;
        }

        .form-shell,
        .booking-list-shell {
            border-radius: 14px;
        }

        .booking-list-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .booking-carousel-controls {
            justify-content: flex-end;
        }

        .booking-grid {
            grid-template-columns: 1fr;
        }
    }


    /* ===== CONTRAST FONT OVERRIDES: WHITE + PINK + GOLD ===== */
    .results-section .section-title,
    .results-section .booking-list-shell .card-header h3,
    .results-section .empty-state h5,
    .results-section .booking-reference,
    .results-section .booking-value,
    .results-section #bookingNoResults,
    .results-section .btn-air-primary,
    .results-section .booking-search-input {
        color: #ffffff !important;
    }

    .results-section .section-copy,
    .results-section .empty-state p,
    .results-section .booking-subvalue,
    .results-section .booking-search-input::placeholder {
        color: #fbcfe8 !important;
    }

    .results-section .booking-label,
    .results-section .fare-text,
    .results-section .payment-badge,
    .results-section .seat-badge,
    .results-section .booking-carousel-btn,
    .results-section .booking-carousel-dot.active,
    .results-section .empty-state i {
        color: #facc15 !important;
    }

    .results-section .booking-search-input {
        caret-color: #facc15 !important;
    }

    .results-section .booking-reference,
    .results-section .booking-value,
    .results-section .booking-subvalue,
    .results-section .booking-label,
    .results-section .section-title,
    .results-section .section-copy,
    .results-section #bookingNoResults,
    .results-section .empty-state h5,
    .results-section .empty-state p {
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
    }

</style>

<section class="results-section">
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="section-header center-header">
                    <div class="section-title">My Bookings</div>
                    <p class="section-copy">View your confirmed tickets and print boarding passes anytime.</p>
                </div>

                <?php if ($flash): ?>
                    <div class="alert <?php echo $flash['type'] === 'danger' ? 'alert-danger' : ($flash['type'] === 'warning' ? 'alert-warning' : 'alert-success'); ?>">
                        <?php echo e($flash['message']); ?>
                    </div>
                <?php endif; ?>

                <div class="row justify-content-center">
                    <div class="col-12 col-lg-11">
                        <?php if (empty($bookings)): ?>
                            <div class="form-shell card border-0 floating-card mx-auto">
                                <div class="card-body p-4">
                                    <div class="empty-state">
                                        <i class="fas fa-plane fs-1 mb-3"></i>
                                        <h5>No bookings yet</h5>
                                        <p>Book your first flight and it will appear here.</p>
                                        <a href="index.php" class="btn btn-air-primary">Find Flights</a>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="table-shell card border-0 floating-card booking-list-shell mx-auto">
                                <div class="card-header">
                                    <h3 class="fw-bold mb-0">My Booking Cards</h3>
                                </div>

                                <div class="card-body">
                                    <div class="booking-list-toolbar">
                                        <div class="booking-search-wrap">
                                            <input
                                                type="text"
                                                id="bookingSearchInput"
                                                class="form-control booking-search-input"
                                                placeholder="Search your bookings..."
                                                autocomplete="off"
                                            >
                                        </div>

                                        <div class="booking-carousel-controls">
                                            <button type="button" class="btn btn-sm booking-carousel-btn" id="bookingPrevBtn" aria-label="Previous booking">&#10094;</button>
                                            <button type="button" class="btn btn-sm booking-carousel-btn" id="bookingNextBtn" aria-label="Next booking">&#10095;</button>
                                        </div>
                                    </div>

                                    <div class="booking-carousel-viewport">
                                        <div class="booking-carousel-track" id="bookingCardsContainer">
                                            <?php foreach ($bookings as $row): ?>
                                                <?php
                                                    $bookingSearchText = strtolower(trim(
                                                        ($row['booking_reference'] ?? '') . ' ' .
                                                        ($row['flight_code'] ?? '') . ' ' .
                                                        ($row['origin'] ?? '') . ' ' .
                                                        ($row['destination'] ?? '') . ' ' .
                                                        ($row['departure_date'] ?? '') . ' ' .
                                                        substr(($row['departure_time'] ?? ''), 0, 5) . ' ' .
                                                        ($row['seat_no'] ?? '') . ' ' .
                                                        ($row['seat_class'] ?? '') . ' ' .
                                                        ($row['payment_method'] ?? '') . ' ' .
                                                        ($row['fare_amount'] ?? '')
                                                    ));
                                                ?>
                                                <div class="booking-card-col" data-search="<?php echo htmlspecialchars($bookingSearchText, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <div class="booking-card h-100">
                                                        <div class="booking-card-top">
                                                            <div>
                                                                <div class="booking-label">Reference</div>
                                                                <div class="booking-reference"><?php echo e($row['booking_reference']); ?></div>
                                                            </div>
                                                            <span class="payment-badge"><?php echo e($row['payment_method']); ?></span>
                                                        </div>

                                                        <div class="booking-card-section">
                                                            <div class="booking-label">Flight</div>
                                                            <div class="booking-value"><?php echo e($row['flight_code']); ?></div>
                                                            <div class="booking-subvalue"><?php echo e($row['origin']); ?> → <?php echo e($row['destination']); ?></div>
                                                        </div>

                                                        <div class="booking-card-section">
                                                            <div class="booking-label">Departure</div>
                                                            <div class="booking-value"><?php echo e($row['departure_date']); ?></div>
                                                            <div class="booking-subvalue"><?php echo e(substr($row['departure_time'], 0, 5)); ?></div>
                                                        </div>

                                                        <div class="booking-grid">
                                                            <div class="booking-mini-card">
                                                                <div class="booking-label">Seat</div>
                                                                <div class="booking-value"><span class="seat-badge"><?php echo e($row['seat_no']); ?></span></div>
                                                            </div>

                                                            <div class="booking-mini-card">
                                                                <div class="booking-label">Class</div>
                                                                <div class="booking-value"><?php echo e($row['seat_class']); ?></div>
                                                            </div>

                                                            <div class="booking-mini-card">
                                                                <div class="booking-label">Fare</div>
                                                                <div class="booking-value fare-text"><?php echo e(format_money($row['fare_amount'])); ?></div>
                                                            </div>
                                                        </div>

                                                        <div class="booking-card-footer mt-3 d-flex justify-content-end">
                                                            <a class="btn btn-sm btn-air-primary" href="ticket.php?id=<?php echo (int)$row['id']; ?>" target="_blank">
                                                                📄 Open
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <div class="booking-carousel-progress" id="bookingCarouselDots"></div>

                                    <div id="bookingNoResults" class="text-center py-4" style="display:none; color:#ffffff; font-weight:700; text-shadow:0 1px 2px rgba(0,0,0,0.35);">
                                        No matching bookings found.
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('bookingSearchInput');
    const track = document.getElementById('bookingCardsContainer');
    const noResults = document.getElementById('bookingNoResults');
    const dots = document.getElementById('bookingCarouselDots');
    const prevBtn = document.getElementById('bookingPrevBtn');
    const nextBtn = document.getElementById('bookingNextBtn');

    if (!searchInput || !track) return;

    const allCards = Array.from(track.querySelectorAll('.booking-card-col'));
    let currentIndex = 0;
    let autoplayTimer = null;

    function normalizeText(text) {
        return (text || '')
            .toLowerCase()
            .replace(/\s+/g, ' ')
            .trim();
    }

    function getVisibleCards() {
        return allCards.filter(function (card) {
            return !card.classList.contains('d-none');
        });
    }

    function renderDots() {
        if (!dots) return;
        const visibleCards = getVisibleCards();
        dots.innerHTML = '';

        visibleCards.forEach(function (_, index) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'booking-carousel-dot' + (index === currentIndex ? ' active' : '');
            dot.setAttribute('aria-label', 'Go to booking ' + (index + 1));
            dot.addEventListener('click', function () {
                currentIndex = index;
                updateCarousel();
                restartAutoplay();
            });
            dots.appendChild(dot);
        });

        dots.style.display = visibleCards.length > 1 ? 'flex' : 'none';
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

    function filterBookings() {
        const query = normalizeText(searchInput.value);
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
    searchInput.addEventListener('input', function () {
        filterBookings();
        startAutoplay();
    });

    filterBookings();
    startAutoplay();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
