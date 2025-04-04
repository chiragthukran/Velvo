<?php
include '../../backend/config.php';

// Check if user is logged in and is a driver
checkRole(['driver']);

try {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT is_available FROM DriverDetails WHERE driver_id = ?");
    if (!$stmt) {
        handleDatabaseError($conn, "Failed to prepare statement");
    }
    
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        handleDatabaseError($conn, "Failed to execute query");
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'is_available' => (bool)$row['is_available']
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