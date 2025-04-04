<?php
require_once '../config.php';

// Check if user is logged in as a customer
checkRole(['customer']);

// Get the customer ID
$customerId = $_SESSION['user_id'];

// Get booking details from POST
$rideId = intval($_POST['ride_id'] ?? 0);
$seats = intval($_POST['seats'] ?? 1);

// Validate inputs
if ($rideId <= 0 || $seats <= 0) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid ride ID or number of seats"
    ]);
    exit;
}

// Get ride details
$stmt = $conn->prepare("
    SELECT driver_id, available_seats, status 
    FROM Rides 
    WHERE ride_id = ?
");
$stmt->bind_param("i", $rideId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "error" => "Ride not found"
    ]);
    exit;
}

$ride = $result->fetch_assoc();

// Check if ride is active
if ($ride['status'] !== 'active') {
    echo json_encode([
        "success" => false,
        "error" => "This ride is no longer available"
    ]);
    exit;
}

// Check if there are enough available seats
if ($seats > $ride['available_seats']) {
    echo json_encode([
        "success" => false,
        "error" => "Not enough available seats. Only " . $ride['available_seats'] . " seat(s) available."
    ]);
    exit;
}

// Check if customer is not booking their own ride
if ($ride['driver_id'] == $customerId) {
    echo json_encode([
        "success" => false,
        "error" => "You cannot book your own ride"
    ]);
    exit;
}

// Check if customer already has an active booking for this ride
$stmt = $conn->prepare("
    SELECT booking_id 
    FROM Bookings 
    WHERE ride_id = ? AND customer_id = ? AND booking_status IN ('pending', 'approved')
");
$stmt->bind_param("ii", $rideId, $customerId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "error" => "You already have an active booking for this ride"
    ]);
    exit;
}

// Create booking
$stmt = $conn->prepare("
    INSERT INTO Bookings (ride_id, customer_id, seats_booked, booking_status) 
    VALUES (?, ?, ?, 'pending')
");
$stmt->bind_param("iii", $rideId, $customerId, $seats);

if ($stmt->execute()) {
    $bookingId = $conn->insert_id;
    echo json_encode([
        "success" => true,
        "message" => "Booking request sent. Awaiting driver approval.",
        "booking_id" => $bookingId
    ]);
} else {
    handleDatabaseError($conn, "Failed to create booking");
}
?> 