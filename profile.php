<?php
require_once 'includes/header.php';
require_once 'storage.php';
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}


$cars_storage = new Storage(new JsonIO('data/cars.json'));
$bookings_storage = new Storage(new JsonIO('data/bookings.json'));


$user_bookings = $bookings_storage->findMany(function($booking) {
    return $booking['user_id'] === $_SESSION['user']['id'];
});


usort($user_bookings, function($a, $b) {
    return strtotime($b['start_date']) - strtotime($a['start_date']);
});

foreach ($user_bookings as &$booking) {
    $booking['car'] = $cars_storage->findById($booking['car_id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>My Profile - iKarRental</title>
</head>
<body>
    <main class="container">
        <a href="index.php" class="back-button">← Back to Homepage</a>
        
        <div class="profile-header">
            <h1>My Profile</h1>
            <div class="user-info">
                <p>Welcome, <?= isset($_SESSION['user']['fullname']) ? htmlspecialchars($_SESSION['user']['fullname']) : htmlspecialchars($_SESSION['user']['email']) ?></p>
                <p class="user-email"><?= htmlspecialchars($_SESSION['user']['email']) ?></p>
            </div>
        </div>

        <div class="profile-content">
            <h2>My Reservations</h2>
            <?php if (empty($user_bookings)): ?>
                <div class="no-bookings">
                    <p>You haven't made any reservations yet.</p>
                    <a href="index.php" class="button primary">Browse Cars</a>
                </div>
            <?php else: ?>
                <div class="bookings-list">
                    <?php foreach ($user_bookings as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-car-image">
                                <img src="<?= htmlspecialchars($booking['car']['image']) ?>" 
                                     alt="<?= htmlspecialchars($booking['car']['brand'] . ' ' . $booking['car']['model']) ?>">
                            </div>
                            <div class="booking-details">
                                <h3><?= htmlspecialchars($booking['car']['brand'] . ' ' . $booking['car']['model']) ?> (<?= htmlspecialchars($booking['car']['year']) ?>)</h3>
                                <div class="booking-info-grid">
                                    <div class="info-item">
                                        <span class="label">Reservation Period</span>
                                        <span class="value">
                                            <?= htmlspecialchars($booking['start_date']) ?> - <?= htmlspecialchars($booking['end_date']) ?>
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <span class="label">Total Price</span>
                                        <span class="value"><?= number_format($booking['total_price'], 0, '.', ',') ?> Ft</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

<?php include 'includes/footer.php'; ?>