<?php
require_once '../config.php';

// Get search parameters
$pickup = sanitizeInput($_GET['pickup'] ?? '');
$dropoff = sanitizeInput($_GET['dropoff'] ?? '');
$date = sanitizeInput($_GET['date'] ?? '');
$passengers = intval($_GET['passengers'] ?? 1);

// Validate inputs
if (empty($pickup) || empty($dropoff) || empty($date)) {
    echo json_encode([
        "success" => false,
        "error" => "All search fields are required"
    ]);
    exit;
}

// Query for available rides
$stmt = $conn->prepare("
    SELECT r.ride_id, r.driver_id, r.pickup_location, r.dropoff_location, 
           r.ride_date, r.departure_time, r.available_seats, r.fare, 
           u.name as driver_name
    FROM Rides r
    JOIN Users u ON r.driver_id = u.user_id
    WHERE r.status = 'active'
    AND r.available_seats >= ?
    AND r.ride_date = ?
    AND (
        r.pickup_location LIKE ? 
        OR r.dropoff_location LIKE ?
    )
    ORDER BY r.departure_time ASC
");

// Create wildcards for LIKE query
$pickupSearch = "%$pickup%";
$dropoffSearch = "%$dropoff%";

$stmt->bind_param("isss", $passengers, $date, $pickupSearch, $dropoffSearch);
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