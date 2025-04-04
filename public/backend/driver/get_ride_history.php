<?php
require_once '../config.php';

// Check if user is logged in as a driver
checkRole(['driver']);

// Get the driver ID
$driverId = $_SESSION['user_id'];

// Query for completed or cancelled rides
$stmt = $conn->prepare("
    SELECT ride_id, pickup_location, dropoff_location, ride_date, departure_time, 
           total_seats, available_seats, fare, status
    FROM Rides 
    WHERE driver_id = ? AND status IN ('completed', 'cancelled')
    ORDER BY ride_date DESC, departure_time DESC
");
$stmt->bind_param("i", $driverId);
$stmt->execute();
$result = $stmt->get_result();

$rides = [];
while ($row = $result->fetch_assoc()) {
    $rides[] = $row;
}

echo json_encode([
    "success" => true,
    "rides" => $rides
]);
?> 