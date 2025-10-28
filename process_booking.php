<?php
require_once 'storage.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to book a car']);
    exit();
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = $_POST['car_id'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    
    if (empty($car_id) || empty($start_date) || empty($end_date)) {
        $response['message'] = "Please fill in all required fields.";
    } else {
        $cars_storage = new Storage(new JsonIO('data/cars.json'));
        $bookings_storage = new Storage(new JsonIO('data/bookings.json'));
        
        $car = $cars_storage->findById($car_id);
        if (!$car) {
            $response['message'] = "Car not found.";
        } else {
            $start_timestamp = strtotime($start_date);
            $end_timestamp = strtotime($end_date);
            $today = strtotime('today');
            
            if ($start_timestamp < $today) {
                $response['message'] = "Start date cannot be in the past.";
            } elseif ($end_timestamp <= $start_timestamp) {
                $response['message'] = "End date must be after start date.";
            } else {
                
                $existing_bookings = $bookings_storage->findMany(function($booking) use ($car_id, $start_date, $end_date) {
                    return $booking['car_id'] === $car_id &&
                           $booking['end_date'] >= $start_date &&
                           $booking['start_date'] <= $end_date;
                });
                
                if (!empty($existing_bookings)) {
                    $response['message'] = "Car is not available for the selected dates.";
                } else {
                 
                    $days = ceil(($end_timestamp - $start_timestamp) / (60 * 60 * 24));
                    $total_price = $days * $car['daily_price_huf'];
                    
                   
                    $booking = [
                        'car_id' => $car_id,
                        'user_id' => $_SESSION['user']['id'],
                        'user_email' => $_SESSION['user']['email'],
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'total_price' => $total_price
                    ];
                    
                    $bookings_storage->add($booking);
                    
                    $response = [
                        'success' => true,
                        'message' => 'Booking successful!',
                        'booking' => [
                            'car' => [
                                'brand' => $car['brand'],
                                'model' => $car['model'],
                                'image' => $car['image'],
                                'year' => $car['year'],
                                'transmission' => $car['transmission'],
                                'fuel_type' => $car['fuel_type'],
                                'passengers' => $car['passengers']
                            ],
                            'dates' => [
                                'start' => $start_date,
                                'end' => $end_date
                            ],
                            'total_price' => $total_price
                        ]
                    ];
                }
            }
        }
    }
}

echo json_encode($response);
