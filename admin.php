<?php
session_start();
require_once 'storage.php';


if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit();
}

$cars_storage = new Storage(new JsonIO('data/cars.json'));
$bookings_storage = new Storage(new JsonIO('data/bookings.json'));
$message = '';
$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                
                $brand = $_POST['brand'] ?? '';
                $model = $_POST['model'] ?? '';
                $year = $_POST['year'] ?? '';
                $transmission = $_POST['transmission'] ?? '';
                $fuel_type = $_POST['fuel_type'] ?? '';
                $passengers = $_POST['passengers'] ?? '';
                $daily_price_huf = $_POST['daily_price_huf'] ?? '';
                $image = $_POST['image'] ?? '';

                if (!$brand || !$model || !$year || !$transmission || !$fuel_type || !$passengers || !$daily_price_huf || !$image) {
                    $error = "All fields are required";
                } elseif (!is_numeric($year) || $year < 1900 || $year > 2025) {
                    $error = "Invalid year";
                } elseif (!is_numeric($passengers) || $passengers < 1 || $passengers > 9) {
                    $error = "Invalid number of passengers";
                } elseif (!is_numeric($daily_price_huf) || $daily_price_huf < 0) {
                    $error = "Invalid price";
                } else {
                    $car = [
                        'brand' => $brand,
                        'model' => $model,
                        'year' => (int)$year,
                        'transmission' => $transmission,
                        'fuel_type' => $fuel_type,
                        'passengers' => (int)$passengers,
                        'daily_price_huf' => (int)$daily_price_huf,
                        'image' => $image
                    ];

                    if ($_POST['action'] === 'add') {
                        $cars_storage->add($car);
                        $message = "Car added successfully";
                    } else {
                        $id = $_POST['id'] ?? '';
                        if ($id) {
                            $car['id'] = $id;
                            $cars_storage->update($id, $car);
                            $message = "Car updated successfully";
                        }
                    }
                }
                break;

            case 'delete_car':
                $id = $_POST['id'] ?? '';
                if ($id) {
                    $cars_storage->delete($id);
                    
                    $bookings_storage->deleteMany(function($booking) use ($id) {
                        return $booking['car_id'] === $id;
                    });
                    $message = "Car and related bookings deleted successfully";
                }
                break;

            case 'delete_booking':
                $booking_id = $_POST['booking_id'] ?? '';
                if ($booking_id) {
                    $bookings_storage->delete($booking_id);
                    $message = "Booking deleted successfully";
                }
                break;
        }
    }
}


$cars = $cars_storage->findAll();
$bookings = $bookings_storage->findAll();


usort($bookings, function($a, $b) {
    return strtotime($b['start_date']) - strtotime($a['start_date']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - iKarRental</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">iKarRental</a>
        <div class="auth-buttons">
            <div class="user-menu">
                <span class="user-name">Welcome, Administrator</span>
                <a href="logout.php" class="button logout-button">Logout</a>
            </div>
        </div>
    </header>

    <main class="container admin-dashboard">
        <h1>Admin Dashboard</h1>

        <?php if ($message): ?>
            <div class="success-message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

       
        <section class="bookings-section">
            <h2>All Bookings</h2>
            <div class="table-container">
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Car</th>
                            <th>User</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Total Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): 
                            $car = $cars_storage->findById($booking['car_id']);
                            if (!$car) continue;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></td>
                                <td><?= htmlspecialchars($booking['user_email']) ?></td>
                                <td><?= htmlspecialchars($booking['start_date']) ?></td>
                                <td><?= htmlspecialchars($booking['end_date']) ?></td>
                                <td><?= number_format($booking['total_price'], 0, '.', ',') ?> Ft</td>
                                <td>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_booking">
                                        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['id']) ?>">
                                        <button type="submit" class="button delete-btn" 
                                                onclick="return confirm('Are you sure you want to delete this booking?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="6" class="no-data">No bookings found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

       
        <section class="admin-form">
            <h2>Add New Car</h2>
            <form method="POST" action="" class="car-form">
                <input type="hidden" name="action" value="add">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="brand">Brand</label>
                        <input type="text" id="brand" name="brand" required>
                    </div>
                    <div class="form-group">
                        <label for="model">Model</label>
                        <input type="text" id="model" name="model" required>
                    </div>
                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" id="year" name="year" required min="1900" max="2025">
                    </div>
                    <div class="form-group">
                        <label for="transmission">Transmission</label>
                        <select id="transmission" name="transmission" required>
                            <option value="Automatic">Automatic</option>
                            <option value="Manual">Manual</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fuel_type">Fuel Type</label>
                        <select id="fuel_type" name="fuel_type" required>
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Electric">Electric</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="passengers">Passengers</label>
                        <input type="number" id="passengers" name="passengers" required min="1" max="9">
                    </div>
                    <div class="form-group">
                        <label for="daily_price_huf">Daily Price (HUF)</label>
                        <input type="number" id="daily_price_huf" name="daily_price_huf" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="image">Image URL</label>
                        <input type="url" id="image" name="image" required>
                    </div>
                </div>
                <button type="submit" class="button primary">Add Car</button>
            </form>
        </section>

     
        <section class="cars admin-cars">
            <h2>Manage Cars : </h2>
            <?php foreach ($cars as $car): ?>
                <div class="car-card admin-car-card">
                    <div class="car-image">
                        <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['model']) ?>">
                        <div class="price"><?= number_format($car['daily_price_huf'], 0, '.', ',') ?> Ft</div>
                    </div>
                    <div class="car-info">
                        <h3><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h3>
                        <div class="specs">
                            <span><?= htmlspecialchars($car['passengers']) ?> seats</span>
                            <span><?= htmlspecialchars($car['transmission']) ?></span>
                        </div>
                        <div class="admin-actions">
                            <button class="button edit-btn" onclick="editCar(<?= htmlspecialchars(json_encode($car)) ?>)">Edit</button>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="action" value="delete_car">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($car['id']) ?>">
                                <button type="submit" class="button delete-btn" 
                                        onclick="return confirm('Are you sure you want to delete this car? This will also delete all related bookings.')">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    </main>

    <script>
    function editCar(car) {
      
        document.getElementById('brand').value = car.brand;
        document.getElementById('model').value = car.model;
        document.getElementById('year').value = car.year;
        document.getElementById('transmission').value = car.transmission;
        document.getElementById('fuel_type').value = car.fuel_type;
        document.getElementById('passengers').value = car.passengers;
        document.getElementById('daily_price_huf').value = car.daily_price_huf;
        document.getElementById('image').value = car.image;
        
        
        const form = document.querySelector('.car-form');
        const actionInput = form.querySelector('input[name="action"]');
        actionInput.value = 'edit';
        
        
        if (!form.querySelector('input[name="id"]')) {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            form.appendChild(idInput);
        }
        form.querySelector('input[name="id"]').value = car.id;
        
        
        form.querySelector('button[type="submit"]').textContent = 'Update Car';
        
       
        form.scrollIntoView({ behavior: 'smooth' });
    }
    </script>
</body>
</html>

<?php include 'includes/footer.php'; ?>