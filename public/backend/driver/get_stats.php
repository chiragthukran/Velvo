<?php
include '../../backend/config.php';

// Check if user is logged in and is a driver
checkRole(['driver']);

try {
    $user_id = $_SESSION['user_id'];
    
    // Get total rides and earnings
    $stmt = $conn->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'completed' THEN fare ELSE 0 END) as earnings
        FROM Rides 
        WHERE driver_id = ?");
    
    if (!$stmt) {
        handleDatabaseError($conn, "Failed to prepare statement");
    }
    
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        handleDatabaseError($conn, "Failed to execute query");
    }
    
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();
    
    // Convert null values to 0
    $stats['total'] = $stats['total'] ?? 0;
    $stats['earnings'] = $stats['earnings'] ?? 0;
    
    // Calculate rating (placeholder - you might want to add a ratings table)
    $stats['rating'] = 4.5; // Placeholder rating
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
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