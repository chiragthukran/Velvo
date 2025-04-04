<?php
require_once '../config.php';

// Check if user is logged in as a customer
checkRole(['customer']);

// Get the customer ID (either from session or request)
$customerId = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];

// Ensure the user can only view their own profile
if ($customerId !== $_SESSION['user_id']) {
    echo json_encode([
        "success" => false,
        "error" => "Not authorized to view this profile"
    ]);
    exit;
}

// Get customer profile data
$stmt = $conn->prepare("
    SELECT user_id, name, email, username, created_at
    FROM Users
    WHERE user_id = ? AND role = 'customer'
");
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "error" => "Customer profile not found"
    ]);
    exit;
}

$profile = $result->fetch_assoc();

// Remove sensitive information
unset($profile['password']);

echo json_encode([
    "success" => true,
    "profile" => $profile
]);
?> 