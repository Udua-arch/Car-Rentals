<?php
require_once 'includes/header.php';
require_once 'storage.php';


$id = $_GET['id'] ?? '';


if (empty($id)) {
    header('Location: index.php');
    exit();
}


$cars_storage = new Storage(new JsonIO('data/cars.json'));
$bookings_storage = new Storage(new JsonIO('data/bookings.json'));
$car = $cars_storage->findById($id);


if (!$car) {
    header('Location: index.php');
    exit();
}


$existing_bookings = $bookings_storage->findMany(function($booking) use ($id) {
    return $booking['car_id'] === $id;
});


$disabled_dates = [];
foreach ($existing_bookings as $booking) {
    $start = new DateTime($booking['start_date']);
    $end = new DateTime($booking['end_date']);
    $interval = new DateInterval('P1D');
    $date_range = new DatePeriod($start, $interval, $end->modify('+1 day'));
    
    foreach ($date_range as $date) {
        $disabled_dates[] = $date->format('Y-m-d');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">
    <title><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?> - iKarRental</title>
</head>
<body>
    <main class="container">
        <a href="index.php" class="back-button">← Back to Homepage</a>
        
        <div class="car-details-page">
            <div class="car-details-grid">
                <div class="car-image-section">
                    <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['model']) ?>">
                </div>
                <div class="car-info-section">
                    <h1><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h1>
                    <div class="car-specs">
                        <div class="spec-item">
                            <span class="label">Year</span>
                            <span class="value"><?= htmlspecialchars($car['year']) ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="label">Transmission</span>
                            <span class="value"><?= htmlspecialchars($car['transmission']) ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="label">Fuel Type</span>
                            <span class="value"><?= htmlspecialchars($car['fuel_type']) ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="label">Passengers</span>
                            <span class="value"><?= htmlspecialchars($car['passengers']) ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="label">Daily Price</span>
                            <span class="value"><?= number_format($car['daily_price_huf'], 0, '.', ',') ?> Ft</span>
                        </div>
                    </div>

                    <?php if (!isset($_SESSION['user'])): ?>
                        <div class="guest-message">
                            <p>Please <a href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">log in</a> or <a href="register.php">register</a> to book this car.</p>
                        </div>
                    <?php else: ?>
                        <div class="booking-actions">
                            <button class="button" id="selectDateBtn">Select a date</button>
                            <button type="submit" form="bookingForm" class="button primary" id="bookBtn">Book it</button>
                        </div>
                        
                        <form id="bookingForm" method="POST" class="booking-form" style="display: none;" novalidate>
                            <input type="hidden" name="car_id" value="<?= htmlspecialchars($id) ?>">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="text" id="start_date" name="start_date" class="flatpickr" 
                                           placeholder="Select start date" required>
                                </div>
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="text" id="end_date" name="end_date" class="flatpickr"
                                           placeholder="Select end date" required>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectDateBtn = document.getElementById('selectDateBtn');
            const bookingForm = document.getElementById('bookingForm');
            const bookBtn = document.getElementById('bookBtn');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const modal = document.getElementById('bookingModal');
            const modalContent = document.getElementById('modalContent');
            const closeBtn = document.getElementsByClassName('close')[0];

           
            const disabledDates = <?= json_encode($disabled_dates) ?>;

           
            const commonConfig = {
                dateFormat: "Y-m-d",
                minDate: "today",
                disable: disabledDates,
                theme: "dark",
                onChange: function(selectedDates, dateStr, instance) {
                    if (instance.input.id === 'start_date' && selectedDates[0]) {
                        endDatePicker.set('minDate', dateStr);
                    }
                }
            };

            
            const startDatePicker = flatpickr(startDateInput, {
                ...commonConfig,
                onClose: function(selectedDates, dateStr) {
                    if (selectedDates[0]) {
                        endDateInput.focus();
                    }
                }
            });

            
            const endDatePicker = flatpickr(endDateInput, {
                ...commonConfig
            });

            if (selectDateBtn && bookingForm) {
                selectDateBtn.addEventListener('click', function() {
                    bookingForm.style.display = bookingForm.style.display === 'none' ? 'block' : 'none';
                    selectDateBtn.textContent = bookingForm.style.display === 'none' ? 'Select a date' : 'Hide dates';
                    if (bookingForm.style.display === 'block') {
                        startDateInput.focus();
                    }
                });
            }

            
            closeBtn.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            
            if (bookingForm) {
                bookingForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(bookingForm);
                    
                    fetch('process_booking.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const booking = data.booking;
                            modalContent.innerHTML = `
                                <div class="booking-success">
                                    <h2>Booking Confirmed!</h2>
                                    <div class="booking-details">
                                        <img src="${booking.car.image}" alt="${booking.car.brand} ${booking.car.model}">
                                        <h3>${booking.car.brand} ${booking.car.model} (${booking.car.year})</h3>
                                        <div class="details-grid">
                                            <div class="detail-item">
                                                <span class="label">Start Date:</span>
                                                <span class="value">${booking.dates.start}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="label">End Date:</span>
                                                <span class="value">${booking.dates.end}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="label">Total Price:</span>
                                                <span class="value">${booking.total_price.toLocaleString()} Ft</span>
                                            </div>
                                        </div>
                                        <div class="success-actions">
                                            <a href="profile.php" class="button primary">View My Bookings</a>
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else {
                            modalContent.innerHTML = `
                                <div class="booking-error">
                                    <h2>Booking Failed</h2>
                                    <p>${data.message}</p>
                                    <button onclick="window.location.reload()" class="button primary" style="background-color: #ffd700; color: #000;">Back to Booking</button>
                                </div>
                            `;
                        }
                        modal.style.display = "block";
                    })
                    .catch(error => {
                        modalContent.innerHTML = `
                            <div class="booking-error">
                                <h2>Error</h2>
                                <p>An unexpected error occurred. Please try again later.</p>
                                <button onclick="window.location.reload()" class="button primary" style="background-color: #ffd700; color: #000;">Back to Booking</button>
                            </div>
                        `;
                        modal.style.display = "block";
                    });
                });
            }
        });
    </script>
</body>
</html>

<?php include 'includes/footer.php'; ?>