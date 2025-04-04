<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

try {
    // Get and sanitize inputs
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $role = sanitizeInput($_POST['role']);
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);

    // Validate inputs
    if (!validateUsername($username)) {
        echo json_encode(['success' => false, 'error' => 'Invalid username format']);
        exit;
    }

    if (!validatePassword($password)) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters long']);
        exit;
    }

    if (!validateEmail($email)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email format']);
        exit;
    }

    if (!in_array($role, ['customer', 'driver'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid role']);
        exit;
    }

    if (empty($name)) {
        echo json_encode(['success' => false, 'error' => 'Name is required']);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    // Check if username exists
    $stmt = $conn->prepare("SELECT user_id FROM Users WHERE username = ?");
    if (!$stmt) {
        handleDatabaseError($conn, "Failed to prepare username check statement");
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Username already taken']);
        exit;
    }
    $stmt->close();

    // Check if email exists
    $stmt = $conn->prepare("SELECT user_id FROM Users WHERE email = ?");
    if (!$stmt) {
        handleDatabaseError($conn, "Failed to prepare email check statement");
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Email already registered']);
        exit;
    }
    $stmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO Users (username, password, role, name, email) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        handleDatabaseError($conn, "Failed to prepare user insert statement");
    }
    $stmt->bind_param("sssss", $username, $hashed_password, $role, $name, $email);
    
    if (!$stmt->execute()) {
        handleDatabaseError($conn, "Failed to create user");
    }
    
    $user_id = $stmt->insert_id;
    $stmt->close();

    // If role is driver, handle driver details
    if ($role == 'driver') {
        // Validate driver fields
        $driving_license_number = sanitizeInput($_POST['driving_license_number']);
        $aadhar_card = sanitizeInput($_POST['aadhar_card']);
        $car_number = sanitizeInput($_POST['car_number']);
        $driver_address = sanitizeInput($_POST['driver_address']);

        if (empty($driving_license_number) || empty($aadhar_card) || 
            empty($car_number) || empty($driver_address)) {
            $conn->rollback();
            echo json_encode(['success' => false, 'error' => 'All driver fields are required']);
            exit;
        }

        // Insert driver details
        $stmt = $conn->prepare("INSERT INTO DriverDetails (driver_id, driving_license_number, aadhar_card, car_number, driver_address) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            handleDatabaseError($conn, "Failed to prepare driver details statement");
        }
        $stmt->bind_param("issss", $user_id, $driving_license_number, $aadhar_card, $car_number, $driver_address);
        
        if (!$stmt->execute()) {
            $conn->rollback();
            handleDatabaseError($conn, "Failed to add driver details");
        }
        $stmt->close();
    }

    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => ($role == 'driver' ? 'Driver' : 'Customer') . ' signup successful! Please log in.'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred',
        'details' => $e->getMessage()
    ]);
}

$conn->close();
?>