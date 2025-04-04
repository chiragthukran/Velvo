<?php
include '../../backend/config.php';

// Check if user is logged in and is a driver
checkRole(['driver']);

try {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    $available = isset($data['available']) ? (bool)$data['available'] : false;
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("UPDATE DriverDetails SET is_available = ? WHERE driver_id = ?");
    if (!$stmt) {
        handleDatabaseError($conn, "Failed to prepare statement");
    }
    
    $available_int = $available ? 1 : 0;
    $stmt->bind_param("ii", $available_int, $user_id);
    if (!$stmt->execute()) {
        handleDatabaseError($conn, "Failed to update availability");
    }
    
    echo json_encode([
        'success' => true,
        'is_available' => $available
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