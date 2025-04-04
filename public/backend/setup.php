<?php
// Connect to MySQL without selecting a database
$conn = new mysqli('localhost', 'root', '');

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS ridesharing_db";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully\n";
} else {
    echo "Error creating database: " . $conn->error . "\n";
}

// Select the database
$conn->select_db('ridesharing_db');

// Drop existing tables in the correct order
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("DROP TABLE IF EXISTS Rides");
$conn->query("DROP TABLE IF EXISTS DriverDetails");
$conn->query("DROP TABLE IF EXISTS Users");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Create Users table
$sql = "CREATE TABLE IF NOT EXISTS Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'driver') NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Users table created successfully\n";
} else {
    echo "Error creating Users table: " . $conn->error . "\n";
}

// Create DriverDetails table
$sql = "CREATE TABLE IF NOT EXISTS DriverDetails (
    driver_id INT PRIMARY KEY,
    driving_license_number VARCHAR(50) UNIQUE NOT NULL,
    aadhar_card VARCHAR(50) UNIQUE NOT NULL,
    car_number VARCHAR(20) NOT NULL,
    driver_address TEXT NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES Users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "DriverDetails table created successfully\n";
} else {
    echo "Error creating DriverDetails table: " . $conn->error . "\n";
}

// Create Rides table
$sql = "CREATE TABLE IF NOT EXISTS Rides (
    ride_id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    dropoff_location VARCHAR(255) NOT NULL,
    ride_date DATETIME NOT NULL,
    status ENUM('pending', 'accepted', 'completed', 'cancelled') DEFAULT 'pending',
    fare DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES Users(user_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Rides table created successfully\n";
} else {
    echo "Error creating Rides table: " . $conn->error . "\n";
}

$conn->close();
echo "Database setup completed!\n";
?> 