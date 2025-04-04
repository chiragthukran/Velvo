<?php
session_start();
include '../../backend/config.php';

if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'customer') {
    $customer_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT b.booking_id, r.ride_id, r.start_location, r.end_location, r.price, r.start_time, r.end_time, u.name AS driver_name, 
               d.driving_license_number, d.aadhar_card, d.car_number, d.driver_address
        FROM Bookings b
        JOIN Rides r ON b.ride_id = r.ride_id
        JOIN Users u ON r.driver_id = u.user_id
        JOIN DriverDetails d ON r.driver_id = d.driver_id
        WHERE b.customer_id = ? AND b.status = 'booked'
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($bookings);
    $stmt->close();
}
$conn->close();
?>