<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set security headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Database configuration
$host = 'localhost';
$db = 'ridesharing_db';
$user = 'root'; // Default XAMPP MySQL user
$pass = '';     // Default XAMPP MySQL password (empty)

// Create connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Connection failed: " . $conn->connect_error]));
}

// Common validation functions
function validateUsername($username) {
    return strlen($username) >= 3 && strlen($username) <= 50 && preg_match('/^[a-zA-Z0-9_]+$/', $username);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    return strlen($password) >= 6;
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Session handling
session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check user role
function checkRole($allowedRoles) {
    if (!isLoggedIn()) {
        echo json_encode(["success" => false, "error" => "Not logged in"]);
        exit;
    }
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        echo json_encode(["success" => false, "error" => "Unauthorized access"]);
        exit;
    }
    return true;
}

// Function to handle database errors
function handleDatabaseError($conn, $error = "Database error") {
    echo json_encode([
        "success" => false,
        "error" => $error,
        "details" => $conn->error
    ]);
    exit;
}
?>