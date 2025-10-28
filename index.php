<?php
require_once 'includes/header.php';
require_once 'storage.php';


$cars_storage = new Storage(new JsonIO('data/cars.json'));
$cars = $cars_storage->findAll();

$users_storage = new Storage(new JsonIO('data/users.json'));
$bookings_storage = new Storage(new JsonIO('data/bookings.json'));


$filters = $_GET;
if (!empty($filters)) {
    $cars = array_filter($cars, function ($car) use ($filters, $bookings_storage) {
        if (isset($filters['transmission']) && !empty($filters['transmission'])) {
            if (strtolower($car['transmission']) !== strtolower($filters['transmission'])) {
                return false;
            }
        }
        if (isset($filters['passengers']) && $filters['passengers'] > 0) {
            if ($car['passengers'] < $filters['passengers']) {
                return false;
            }
        }
        if (isset($filters['min_price']) && $filters['min_price'] > 0) {
            if ($car['daily_price_huf'] < $filters['min_price']) {
                return false;
            }
        }
        if (isset($filters['max_price']) && $filters['max_price'] > 0) {
            if ($car['daily_price_huf'] > $filters['max_price']) {
                return false;
            }
        }
        
        if (isset($filters['from_date']) && isset($filters['to_date'])) {
            $bookings = $bookings_storage->findMany(function($booking) use ($car) {
                return $booking['car_id'] === $car['id'];
            });
            
            foreach ($bookings as $booking) {
                
                if ($filters['from_date'] <= $booking['end_date'] && $filters['to_date'] >= $booking['start_date']) {
                    return false;
                }
            }
        }
        
        return true;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    
</head>
<body>
    <main class="container">
        <h1>Rent cars easily!</h1>

        
        <form class="filter-section" method="GET" action="">
            <div class="filter-group">
                <div class="seats-filter">
                    <button type="button" class="decrement">-</button>
                    <input type="number" name="passengers" id="seats" value="<?= $_GET['passengers'] ?? 0 ?>" min="0" max="9">
                    <button type="button" class="increment">+</button>
                    <label for="seats">seats</label>
                </div>
                
                <div class="date-filter">
                    <div class="date-input">
                        <label for="from_date">From</label>
                        <input type="date" id="from_date" name="from_date" 
                               value="<?= $_GET['from_date'] ?? '' ?>"
                               min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="date-input">
                        <label for="to_date">Until</label>
                        <input type="date" id="to_date" name="to_date" 
                               value="<?= $_GET['to_date'] ?? '' ?>"
                               min="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="type-filter">
                    <select name="transmission" id="transmission">
                        <option value="">Transmission</option>
                        <option value="Manual" <?= ($_GET['transmission'] ?? '') === 'Manual' ? 'selected' : '' ?>>Manual</option>
                        <option value="Automatic" <?= ($_GET['transmission'] ?? '') === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                    </select>
                </div>

                <div class="price-filter">
                    <input type="number" name="min_price" id="price_from" 
                           placeholder="Price from" 
                           value="<?= $_GET['min_price'] ?? '' ?>"
                           min="0">
                    <input type="number" name="max_price" id="price_to" 
                           placeholder="Price to" 
                           value="<?= $_GET['max_price'] ?? '' ?>"
                           min="0">
                    <span>Ft</span>
                </div>
            </div>
            <button type="submit" class="filter-button">Filter</button>
        </form>

      
<div class="cars">
    <?php foreach ($cars as $car): ?>
        <a href="car_details.php?id=<?= urlencode($car['id']) ?>" class="car-card">
            <div class="car-image">
                <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['model']) ?>">
                <div class="price"><?= number_format($car['daily_price_huf'], 0, '.', ',') ?> Ft</div>
            </div>
            <div class="car-info">
                <h2><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h2>
                <div class="specs">
                    <span><?= htmlspecialchars($car['passengers']) ?> seats</span>
                    <span><?= htmlspecialchars($car['transmission']) ?></span>
                </div>
                <button class="book-button">Book</button>
            </div>
        </a>
    <?php endforeach; ?>
</div>

    </main>

    <script>
        function incrementSeats() {
            const input = document.getElementById('seats');
            input.value = parseInt(input.value) + 1;
        }

        function decrementSeats() {
            const input = document.getElementById('seats');
            if (parseInt(input.value) > 0) {
                input.value = parseInt(input.value) - 1;
            }
        }
    </script>
</body>
</html>

<?php include 'includes/footer.php'; ?>