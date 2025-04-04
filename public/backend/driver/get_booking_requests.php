<?php
require_once '../config.php';

// Check if user is logged in as a driver
checkRole(['driver']);

// Get the driver ID
$driverId = $_SESSION['user_id'];

// Query for pending booking requests
$stmt = $conn->prepare("
    SELECT b.booking_id, b.ride_id, b.customer_id, b.seats_booked, b.booking_status, 
           r.pickup_location, r.dropoff_location, r.ride_date, r.departure_time, 
           r.fare, u.name as customer_name
    FROM Bookings b
    JOIN Rides r ON b.ride_id = r.ride_id
    JOIN Users u ON b.customer_id = u.user_id
    WHERE r.driver_id = ? AND b.booking_status = 'pending'
    ORDER BY r.ride_date ASC, r.departure_time ASC
");
$stmt->bind_param("i", $driverId);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

echo json_encode([
    "success" => true,
    "requests" => $requests
]);
?> 