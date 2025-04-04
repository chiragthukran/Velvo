<?php
require_once '../config.php';

// Check if user is logged in as a driver
checkRole(['driver']);

// Get the driver ID
$driverId = $_SESSION['user_id'];

// Get ride details from POST
$pickupLocation = sanitizeInput($_POST['pickup_location'] ?? '');
$dropoffLocation = sanitizeInput($_POST['dropoff_location'] ?? '');
$rideDate = sanitizeInput($_POST['ride_date'] ?? '');
$departureTime = sanitizeInput($_POST['departure_time'] ?? '');
$totalSeats = intval($_POST['total_seats'] ?? 4);
$availableSeats = intval($_POST['available_seats'] ?? 4);
$fare = floatval($_POST['fare'] ?? 0);

// Validate inputs
if (empty($pickupLocation) || empty($dropoffLocation) || empty($rideDate) || empty($departureTime) || $fare <= 0) {
    echo json_encode([
        "success" => false,
        "error" => "All fields are required and fare must be greater than 0"
    ]);
    exit;
}

// Check if driver is available
$stmt = $conn->prepare("SELECT is_available FROM DriverDetails WHERE driver_id = ?");
$stmt->bind_param("i", $driverId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "error" => "Driver profile not found"
    ]);
    exit;
}

$driverDetails = $result->fetch_assoc();
if (!$driverDetails['is_available']) {
    echo json_encode([
        "success" => false,
        "error" => "You are currently marked as unavailable"
    ]);
    exit;
}

// Insert the ride into the database
$stmt = $conn->prepare("INSERT INTO Rides (driver_id, pickup_location, dropoff_location, ride_date, departure_time, total_seats, available_seats, fare, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
$stmt->bind_param("issssiii", $driverId, $pickupLocation, $dropoffLocation, $rideDate, $departureTime, $totalSeats, $availableSeats, $fare);

if ($stmt->execute()) {
    $rideId = $conn->insert_id;
    echo json_encode([
        "success" => true,
        "message" => "Ride published successfully",
        "ride_id" => $rideId
    ]);
} else {
    handleDatabaseError($conn, "Failed to publish ride");
}
?> 