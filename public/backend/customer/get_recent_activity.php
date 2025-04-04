<?php
include '../../backend/config.php';

// Check if user is logged in and is a customer
checkRole(['customer']);

try {
    $user_id = $_SESSION['user_id'];
    
    // Get recent rides with status changes
    $stmt = $conn->prepare("
        SELECT 
            r.ride_id,
            r.pickup_location,
            r.dropoff_location,
            r.status,
            r.created_at,
            u.name as driver_name
        FROM Rides r
        LEFT JOIN Users u ON r.driver_id = u.user_id
        WHERE r.customer_id = ?
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    
    if (!$stmt) {
        handleDatabaseError($conn, "Failed to prepare statement");
    }
    
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        handleDatabaseError($conn, "Failed to execute query");
    }
    
    $result = $stmt->get_result();
    $activities = [];
    
    while ($row = $result->fetch_assoc()) {
        $activity = [
            'description' => '',
            'time' => date('M j, Y g:i A', strtotime($row['created_at'])),
            'icon' => ''
        ];
        
        switch ($row['status']) {
            case 'pending':
                $activity['description'] = "Requested a ride from {$row['pickup_location']} to {$row['dropoff_location']}";
                $activity['icon'] = 'fa-clock';
                break;
            case 'accepted':
                $activity['description'] = "Ride accepted by driver {$row['driver_name']}";
                $activity['icon'] = 'fa-check';
                break;
            case 'completed':
                $activity['description'] = "Completed ride with {$row['driver_name']}";
                $activity['icon'] = 'fa-flag-checkered';
                break;
            case 'cancelled':
                $activity['description'] = "Cancelled ride request";
                $activity['icon'] = 'fa-times';
                break;
        }
        
        $activities[] = $activity;
    }
    
    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred',
        'details' => $e->getMessage()
    ]);
}

$conn->close();
?> 