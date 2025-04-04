<?php
require_once '../config.php';

// Check if user is logged in as a customer
checkRole(['customer']);

// Get the customer ID
$customerId = $_SESSION['user_id'];

// Get booking ID from POST
$bookingId = intval($_POST['booking_id'] ?? 0);

// Validate input
if ($bookingId <= 0) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid booking ID"
    ]);
    exit;
}

// Get booking details
$stmt = $conn->prepare("
    SELECT b.ride_id, b.seats_booked, b.booking_status, r.available_seats
    FROM Bookings b
    JOIN Rides r ON b.ride_id = r.ride_id
    WHERE b.booking_id = ? AND b.customer_id = ?
");
$stmt->bind_param("ii", $bookingId, $customerId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "error" => "Booking not found or not authorized"
    ]);
    exit;
}

$booking = $result->fetch_assoc();

// Check if booking can be cancelled
if (!in_array($booking['booking_status'], ['pending', 'approved'])) {
    echo json_encode([
        "success" => false,
        "error" => "Cannot cancel a booking that is already " . $booking['booking_status']
    ]);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Update booking status
    $stmt = $conn->prepare("UPDATE Bookings SET booking_status = 'cancelled' WHERE booking_id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    
    // If booking was approved, restore available seats
    if ($booking['booking_status'] === 'approved') {
        $newAvailableSeats = $booking['available_seats'] + $booking['seats_booked'];
        $stmt = $conn->prepare("UPDATE Rides SET available_seats = ? WHERE ride_id = ?");
        $stmt->bind_param("ii", $newAvailableSeats, $booking['ride_id']);
        $stmt->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        "success" => true,
        "message" => "Booking cancelled successfully"
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    handleDatabaseError($conn, "Failed to cancel booking: " . $e->getMessage());
}
?> 