<?php
include '../config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'error' => 'Not logged in',
        'redirect' => '../login.html'
    ]);
    exit;
}

// Get the requested role from the query parameter
$requested_role = isset($_GET['role']) ? $_GET['role'] : null;

// If a specific role is requested, check if user has that role
if ($requested_role && $_SESSION['role'] !== $requested_role) {
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized access',
        'redirect' => '../' . $_SESSION['role'] . '/dashboard.html'
    ]);
    exit;
}

// Return user info if all checks pass
echo json_encode([
    'success' => true,
    'user' => [
        'id' => $_SESSION['user_id'],
        'role' => $_SESSION['role'],
        'name' => $_SESSION['name']
    ]
]);
?> 