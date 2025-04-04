<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Get and sanitize inputs
$username = sanitizeInput($_POST['username']);
$password = $_POST['password'];

// Validate inputs
if (!validateUsername($username)) {
    echo json_encode(['success' => false, 'error' => 'Invalid username format']);
    exit;
}

if (empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Password is required']);
    exit;
}

try {
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT user_id, password, role, name FROM Users WHERE username = ?");
    if (!$stmt) {
        handleDatabaseError($conn, "Failed to prepare statement");
    }
    
    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        handleDatabaseError($conn, "Failed to execute query");
    }
    
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['name'] = $row['name'];
            
            echo json_encode([
                'success' => true,
                'role' => $row['role'],
                'name' => $row['name']
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Incorrect password']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Username not found']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred',
        'details' => $e->getMessage()
    ]);
}

$conn->close();
?>