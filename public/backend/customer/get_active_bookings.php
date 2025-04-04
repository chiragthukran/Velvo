<?php
require_once '../config.php';

// Check if user is logged in as a customer
checkRole(['customer']);

// Get the customer ID
$customerId = $_SESSION['user_id'];

// Query for active bookings
$stmt = $conn->prepare("
    SELECT b.booking_id, b.ride_id, b.seats_booked, b.booking_status, b.created_at,
           r.pickup_location, r.dropoff_location, r.ride_date, r.departure_time, r.fare,
           u.name as driver_name
    FROM Bookings b
    JOIN Rides r ON b.ride_id = r.ride_id
    JOIN Users u ON r.driver_id = u.user_id
    WHERE b.customer_id = ? AND b.booking_status IN ('pending', 'approved')
    ORDER BY r.ride_date ASC, r.departure_time ASC
");
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

echo json_encode([
    "success" => true,
    "bookings" => $bookings
]);
?> 