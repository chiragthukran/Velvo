<?php
include '../../backend/config.php';

// Check if user is logged in and is a customer
checkRole(['customer']);

try {
    $user_id = $_SESSION['user_id'];
    
    // Get total rides
    $stmt = $conn->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' OR status = 'accepted' THEN 1 ELSE 0 END) as upcoming,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM Rides 
        WHERE customer_id = ?");
    
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
    $stats['upcoming'] = $stats['upcoming'] ?? 0;
    $stats['completed'] = $stats['completed'] ?? 0;
    
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