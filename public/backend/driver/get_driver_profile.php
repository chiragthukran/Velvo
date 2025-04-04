<?php
require_once '../config.php';

// Check if user is logged in as a driver
checkRole(['driver']);

// Get the driver ID (either from session or request)
$driverId = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];

// Ensure the user can only view their own profile
if ($driverId !== $_SESSION['user_id']) {
    echo json_encode([
        "success" => false,
        "error" => "Not authorized to view this profile"
    ]);
    exit;
}

// Get driver profile data
$stmt = $conn->prepare("
    SELECT u.name, u.email, u.username, 
           d.driving_license_number, d.aadhar_card, d.car_number, d.driver_address, d.is_available
    FROM Users u
    JOIN DriverDetails d ON u.user_id = d.driver_id
    WHERE u.user_id = ?
");
$stmt->bind_param("i", $driverId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "error" => "Driver profile not found"
    ]);
    exit;
}

$profile = $result->fetch_assoc();

echo json_encode([
    "success" => true,
    "profile" => $profile
]);
?> 