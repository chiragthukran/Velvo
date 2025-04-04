<?php
require_once '../config.php';

// Check if user is logged in as a customer
checkRole(['customer']);

// Get the customer ID
$customerId = $_SESSION['user_id'];

// Query for recent booking activity
$stmt = $conn->prepare("
    SELECT b.booking_id, b.booking_status, b.created_at,
           r.pickup_location, r.dropoff_location, r.ride_date
    FROM Bookings b
    JOIN Rides r ON b.ride_id = r.ride_id
    WHERE b.customer_id = ?
    ORDER BY b.created_at DESC
    LIMIT 10
");
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();

$activities = [];
while ($row = $result->fetch_assoc()) {
    $message = "Booking for " . $row['pickup_location'] . " to " . $row['dropoff_location'];
    
    $activities[] = [
        'message' => $message,
        'status' => $row['booking_status'],
        'date' => $row['created_at'],
        'type' => 'booking',
        'id' => $row['booking_id']
    ];
}

echo json_encode([
    "success" => true,
    "activities" => $activities
]);
?> 