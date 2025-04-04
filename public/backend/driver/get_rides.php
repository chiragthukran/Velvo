<?php
session_start();
include '../../backend/config.php';

if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'driver') {
    try {
        $driver_id = $_SESSION['user_id'];
        
        $stmt = $conn->prepare("
            SELECT 
                ride_id,
                pickup_location,
                dropoff_location,
                fare,
                ride_date,
                status
            FROM Rides
            WHERE driver_id = ?
            ORDER BY ride_date DESC
        ");
        
        if (!$stmt) {
            throw new Exception('Failed to prepare statement');
        }
        
        $stmt->bind_param("i", $driver_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute query');
        }
        
        $result = $stmt->get_result();
        $rides = [];
        
        while ($row = $result->fetch_assoc()) {
            $rides[] = [
                'ride_id' => $row['ride_id'],
                'pickup_location' => $row['pickup_location'],
                'dropoff_location' => $row['dropoff_location'],
                'fare' => $row['fare'],
                'ride_date' => date('Y-m-d H:i', strtotime($row['ride_date'])),
                'status' => $row['status']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'rides' => $rides
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
        'error' => 'Unauthorized access'
    ]);
}

$conn->close();
?> 