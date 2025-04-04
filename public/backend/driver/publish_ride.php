<?php
session_start();
include '../../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id']) && $_SESSION['role'] == 'driver') {
    try {
        // Get and sanitize inputs
        $driver_id = $_SESSION['user_id'];
        $pickup_location = sanitizeInput($_POST['start_location']);
        $dropoff_location = sanitizeInput($_POST['end_location']);
        $ride_date = sanitizeInput($_POST['start_time']);
        $fare = floatval($_POST['price']);
        $status = 'pending';

        // Validate inputs
        if (empty($pickup_location) || empty($dropoff_location)) {
            throw new Exception('Start and end locations are required');
        }

        if ($fare <= 0) {
            throw new Exception('Fare must be greater than 0');
        }

        if (empty($ride_date)) {
            throw new Exception('Ride date is required');
        }

        // Insert the ride
        $stmt = $conn->prepare("INSERT INTO Rides (driver_id, pickup_location, dropoff_location, ride_date, status, fare) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Failed to prepare ride insert statement: ' . $conn->error);
        }
        
        $stmt->bind_param("issssd", $driver_id, $pickup_location, $dropoff_location, $ride_date, $status, $fare);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to publish ride: ' . $stmt->error);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Ride published successfully'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request or unauthorized access'
    ]);
}

$conn->close();
?>