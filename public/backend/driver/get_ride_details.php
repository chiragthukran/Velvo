<?php
session_start();
include '../../backend/config.php';

if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'driver') {
    $driver_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT ride_id, start_location, end_location, price, available_seats, start_time, end_time FROM Rides WHERE driver_id = ?");
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rides = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($rides);
    $stmt->close();
}
$conn->close();
?>