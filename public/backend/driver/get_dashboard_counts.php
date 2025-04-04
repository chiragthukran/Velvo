<?php
require_once '../config.php';

// Check if user is logged in as a driver
checkRole(['driver']);

// Get the driver ID
$driverId = $_SESSION['user_id'];

// Initialize counts
$counts = [
    'active_rides' => 0,
    'completed_rides' => 0,
    'pending_requests' => 0
];

// Count active rides
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM Rides WHERE driver_id = ? AND status = 'active'");
$stmt->bind_param("i", $driverId);
$stmt->execute();
$result = $stmt->get_result();
$counts['active_rides'] = $result->fetch_assoc()['count'];

// Count completed rides
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM Rides WHERE driver_id = ? AND status = 'completed'");
$stmt->bind_param("i", $driverId);
$stmt->execute();
$result = $stmt->get_result();
$counts['completed_rides'] = $result->fetch_assoc()['count'];

// Count pending booking requests
$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM Bookings b
    JOIN Rides r ON b.ride_id = r.ride_id
    WHERE r.driver_id = ? AND b.booking_status = 'pending'
");
$stmt->bind_param("i", $driverId);
$stmt->execute();
$result = $stmt->get_result();
$counts['pending_requests'] = $result->fetch_assoc()['count'];

echo json_encode([
    "success" => true,
    "counts" => $counts
]);
?> 