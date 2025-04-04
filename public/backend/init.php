<?php
/**
 * Database initialization script
 * This file sets up the database tables and creates sample data
 */

// First run setup.php to create the database schema
require_once 'setup.php';

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'ridesharing_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

// Create sample drivers (passwords are 'password')
$password = password_hash('password', PASSWORD_DEFAULT);

$drivers = [
    [
        'username' => 'driver1',
        'password' => $password,
        'name' => 'Amit Kumar',
        'email' => 'amit@example.com',
        'license' => 'DL123456789',
        'aadhar' => 'AA12345678',
        'car' => 'DL-01-AB-1234',
        'address' => 'Delhi, India'
    ],
    [
        'username' => 'driver2',
        'password' => $password,
        'name' => 'Priya Sharma',
        'email' => 'priya@example.com',
        'license' => 'DL987654321',
        'aadhar' => 'AA87654321',
        'car' => 'DL-02-CD-5678',
        'address' => 'Mumbai, India'
    ],
    [
        'username' => 'driver3',
        'password' => $password,
        'name' => 'Rahul Singh',
        'email' => 'rahul@example.com',
        'license' => 'DL567891234',
        'aadhar' => 'AA56789123',
        'car' => 'DL-03-EF-9012',
        'address' => 'Bangalore, India'
    ]
];

foreach ($drivers as $driver) {
    // Insert driver user
    $stmt = $conn->prepare("INSERT INTO Users (username, password, role, name, email) VALUES (?, ?, 'driver', ?, ?)");
    $stmt->bind_param("ssss", $driver['username'], $driver['password'], $driver['name'], $driver['email']);
    $stmt->execute();
    
    $driverId = $conn->insert_id;
    
    // Insert driver details
    $stmt = $conn->prepare("INSERT INTO DriverDetails (driver_id, driving_license_number, aadhar_card, car_number, driver_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $driverId, $driver['license'], $driver['aadhar'], $driver['car'], $driver['address']);
    $stmt->execute();
}

// Create sample customers
$customers = [
    [
        'username' => 'customer1',
        'password' => $password,
        'name' => 'Neha Patel',
        'email' => 'neha@example.com'
    ],
    [
        'username' => 'customer2',
        'password' => $password,
        'name' => 'Vikram Mehra',
        'email' => 'vikram@example.com'
    ],
    [
        'username' => 'customer3',
        'password' => $password,
        'name' => 'Ananya Gupta',
        'email' => 'ananya@example.com'
    ]
];

foreach ($customers as $customer) {
    $stmt = $conn->prepare("INSERT INTO Users (username, password, role, name, email) VALUES (?, ?, 'customer', ?, ?)");
    $stmt->bind_param("ssss", $customer['username'], $customer['password'], $customer['name'], $customer['email']);
    $stmt->execute();
}

// Create sample rides
$cities = [
    'Delhi', 'Mumbai', 'Bangalore', 'Chennai', 'Kolkata', 
    'Hyderabad', 'Pune', 'Ahmedabad', 'Jaipur', 'Lucknow'
];

// Get all driver IDs
$stmt = $conn->query("SELECT user_id FROM Users WHERE role = 'driver'");
$driverIds = [];
while ($row = $stmt->fetch_assoc()) {
    $driverIds[] = $row['user_id'];
}

// Create 15 rides for each driver (5 past, 10 future)
foreach ($driverIds as $driverId) {
    // Past rides
    for ($i = 0; $i < 5; $i++) {
        $fromCity = $cities[array_rand($cities)];
        $toCity = $cities[array_rand($cities)];
        
        // Ensure from and to cities are different
        while ($fromCity === $toCity) {
            $toCity = $cities[array_rand($cities)];
        }
        
        $pastDate = date('Y-m-d', strtotime("-" . rand(1, 30) . " days"));
        $departureTime = sprintf("%02d:%02d:00", rand(6, 22), rand(0, 59));
        $totalSeats = rand(1, 4);
        $availableSeats = rand(0, $totalSeats);
        $fare = rand(500, 2000);
        $status = rand(0, 1) ? 'completed' : 'cancelled';
        
        $stmt = $conn->prepare("INSERT INTO Rides (driver_id, pickup_location, dropoff_location, ride_date, departure_time, total_seats, available_seats, fare, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssiiss", $driverId, $fromCity, $toCity, $pastDate, $departureTime, $totalSeats, $availableSeats, $fare, $status);
        $stmt->execute();
    }
    
    // Future rides
    for ($i = 0; $i < 10; $i++) {
        $fromCity = $cities[array_rand($cities)];
        $toCity = $cities[array_rand($cities)];
        
        // Ensure from and to cities are different
        while ($fromCity === $toCity) {
            $toCity = $cities[array_rand($cities)];
        }
        
        $futureDate = date('Y-m-d', strtotime("+" . rand(1, 30) . " days"));
        $departureTime = sprintf("%02d:%02d:00", rand(6, 22), rand(0, 59));
        $totalSeats = rand(1, 4);
        $availableSeats = $totalSeats; // All seats available for future rides
        $fare = rand(500, 2000);
        
        $stmt = $conn->prepare("INSERT INTO Rides (driver_id, pickup_location, dropoff_location, ride_date, departure_time, total_seats, available_seats, fare, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("issssiii", $driverId, $fromCity, $toCity, $futureDate, $departureTime, $totalSeats, $availableSeats, $fare);
        $stmt->execute();
    }
}

// Get all customer IDs
$stmt = $conn->query("SELECT user_id FROM Users WHERE role = 'customer'");
$customerIds = [];
while ($row = $stmt->fetch_assoc()) {
    $customerIds[] = $row['user_id'];
}

// Get some active rides
$stmt = $conn->query("SELECT ride_id, available_seats FROM Rides WHERE status = 'active' LIMIT 20");
$activeRides = [];
while ($row = $stmt->fetch_assoc()) {
    $activeRides[] = $row;
}

// Create sample bookings
foreach ($customerIds as $customerId) {
    // Create 3-5 bookings for each customer
    $numBookings = rand(3, 5);
    
    for ($i = 0; $i < $numBookings && !empty($activeRides); $i++) {
        $rideIndex = array_rand($activeRides);
        $ride = $activeRides[$rideIndex];
        
        if ($ride['available_seats'] > 0) {
            $seatsBooked = rand(1, min(2, $ride['available_seats']));
            $status = ['pending', 'approved', 'completed', 'cancelled', 'rejected'][rand(0, 4)];
            
            $stmt = $conn->prepare("INSERT INTO Bookings (ride_id, customer_id, seats_booked, booking_status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $ride['ride_id'], $customerId, $seatsBooked, $status);
            $stmt->execute();
            
            // If booking is approved, update available seats
            if ($status === 'approved') {
                $newAvailableSeats = $ride['available_seats'] - $seatsBooked;
                $stmt = $conn->prepare("UPDATE Rides SET available_seats = ? WHERE ride_id = ?");
                $stmt->bind_param("ii", $newAvailableSeats, $ride['ride_id']);
                $stmt->execute();
                
                // Update our local copy
                $activeRides[$rideIndex]['available_seats'] = $newAvailableSeats;
            }
        }
        
        // Remove rides with no available seats
        foreach ($activeRides as $idx => $r) {
            if ($r['available_seats'] <= 0) {
                unset($activeRides[$idx]);
            }
        }
    }
}

$conn->close();
echo "Initialization completed successfully! Sample data has been added.\n";
?> 