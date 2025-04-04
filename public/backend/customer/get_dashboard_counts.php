<?php
require_once '../config.php';

// Check if user is logged in as a customer
checkRole(['customer']);

// Get the customer ID
$customerId = $_SESSION['user_id'];

// Initialize counts
$counts = [
    'active_bookings' => 0,
    'completed_rides' => 0,
    'pending_requests' => 0
];

// Count active bookings (approved)
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM Bookings WHERE customer_id = ? AND booking_status = 'approved'");
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();
$counts['active_bookings'] = $result->fetch_assoc()['count'];

// Count completed rides
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM Bookings WHERE customer_id = ? AND booking_status = 'completed'");
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();
$counts['completed_rides'] = $result->fetch_assoc()['count'];

// Count pending requests
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM Bookings WHERE customer_id = ? AND booking_status = 'pending'");
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();
$counts['pending_requests'] = $result->fetch_assoc()['count'];

echo json_encode([
    "success" => true,
    "counts" => $counts
]);
?> 