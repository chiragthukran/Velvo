<?php
require_once '../config.php';

// Check if user is logged in as a driver
checkRole(['driver']);

// Get the driver ID
$driverId = $_SESSION['user_id'];

// Get ride ID and status from POST
$rideId = intval($_POST['ride_id'] ?? 0);
$status = sanitizeInput($_POST['status'] ?? '');

// Validate inputs
if ($rideId <= 0 || !in_array($status, ['completed', 'cancelled'])) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid ride ID or status"
    ]);
    exit;
}

// Check if the ride belongs to the driver
$stmt = $conn->prepare("SELECT status FROM Rides WHERE ride_id = ? AND driver_id = ?");
$stmt->bind_param("ii", $rideId, $driverId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "error" => "Ride not found or not authorized"
    ]);
    exit;
}

$currentStatus = $result->fetch_assoc()['status'];
if ($currentStatus !== 'active') {
    echo json_encode([
        "success" => false,
        "error" => "Only active rides can be updated"
    ]);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Update ride status
    $stmt = $conn->prepare("UPDATE Rides SET status = ? WHERE ride_id = ?");
    $stmt->bind_param("si", $status, $rideId);
    $stmt->execute();
    
    // Update related bookings
    $bookingStatus = ($status === 'completed') ? 'completed' : 'cancelled';
    $stmt = $conn->prepare("UPDATE Bookings SET booking_status = ? WHERE ride_id = ? AND booking_status = 'approved'");
    $stmt->bind_param("si", $bookingStatus, $rideId);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        "success" => true,
        "message" => "Ride status updated to " . $status
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    handleDatabaseError($conn, "Failed to update ride status: " . $e->getMessage());
}
?> 