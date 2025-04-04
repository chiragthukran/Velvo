<?php
require_once '../config.php';

// Check if user is logged in as a driver
checkRole(['driver']);

// Get the driver ID
$driverId = $_SESSION['user_id'];

// Get booking ID and status from POST
$bookingId = intval($_POST['booking_id'] ?? 0);
$status = sanitizeInput($_POST['status'] ?? '');

// Validate inputs
if ($bookingId <= 0 || !in_array($status, ['approved', 'rejected'])) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid booking ID or status"
    ]);
    exit;
}

// Get booking and ride details
$stmt = $conn->prepare("
    SELECT b.ride_id, b.seats_booked, b.booking_status, r.driver_id, r.available_seats
    FROM Bookings b
    JOIN Rides r ON b.ride_id = r.ride_id
    WHERE b.booking_id = ?
");
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "error" => "Booking not found"
    ]);
    exit;
}

$booking = $result->fetch_assoc();

// Check if booking belongs to the driver's ride
if ($booking['driver_id'] != $driverId) {
    echo json_encode([
        "success" => false,
        "error" => "Not authorized to update this booking"
    ]);
    exit;
}

// Check if booking is already processed
if ($booking['booking_status'] !== 'pending') {
    echo json_encode([
        "success" => false,
        "error" => "Booking is already " . $booking['booking_status']
    ]);
    exit;
}

// Check if there are enough available seats
if ($status === 'approved' && $booking['seats_booked'] > $booking['available_seats']) {
    echo json_encode([
        "success" => false,
        "error" => "Not enough available seats"
    ]);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Update booking status
    $stmt = $conn->prepare("UPDATE Bookings SET booking_status = ? WHERE booking_id = ?");
    $stmt->bind_param("si", $status, $bookingId);
    $stmt->execute();
    
    // If approved, update available seats
    if ($status === 'approved') {
        $newAvailableSeats = $booking['available_seats'] - $booking['seats_booked'];
        $stmt = $conn->prepare("UPDATE Rides SET available_seats = ? WHERE ride_id = ?");
        $stmt->bind_param("ii", $newAvailableSeats, $booking['ride_id']);
        $stmt->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        "success" => true,
        "message" => "Booking " . $status
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    handleDatabaseError($conn, "Failed to update booking status: " . $e->getMessage());
}
?> 