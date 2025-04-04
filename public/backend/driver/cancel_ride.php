<?php
session_start();
include '../../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id']) && $_SESSION['role'] == 'driver') {
    try {
        // Get JSON data
        $data = json_decode(file_get_contents('php://input'), true);
        $ride_id = intval($data['ride_id']);
        $driver_id = $_SESSION['user_id'];

        // Verify the ride belongs to the driver
        $stmt = $conn->prepare("SELECT status FROM Rides WHERE ride_id = ? AND driver_id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare statement');
        }
        
        $stmt->bind_param("ii", $ride_id, $driver_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute query');
        }
        
        $result = $stmt->get_result();
        $ride = $result->fetch_assoc();
        
        if (!$ride) {
            throw new Exception('Ride not found or unauthorized');
        }
        
        if ($ride['status'] !== 'pending') {
            throw new Exception('Only pending rides can be cancelled');
        }
        
        $stmt->close();

        // Update ride status to cancelled
        $stmt = $conn->prepare("UPDATE Rides SET status = 'cancelled' WHERE ride_id = ? AND driver_id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare update statement');
        }
        
        $stmt->bind_param("ii", $ride_id, $driver_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to cancel ride');
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Ride cancelled successfully'
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