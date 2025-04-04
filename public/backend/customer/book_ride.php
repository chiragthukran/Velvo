<?php
session_start();
include '../../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id']) && $_SESSION['role'] == 'customer') {
    $ride_id = $_POST['ride_id'];
    $customer_id = $_SESSION['user_id'];
    $current_time = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("SELECT available_seats, start_time FROM Rides WHERE ride_id = ?");
    $stmt->bind_param("i", $ride_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ride = $result->fetch_assoc();

    if ($ride && $ride['available_seats'] > 0 && $ride['start_time'] > $current_time) {
        $stmt = $conn->prepare("INSERT INTO Bookings (customer_id, ride_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $customer_id, $ride_id);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("UPDATE Rides SET available_seats = available_seats - 1 WHERE ride_id = ?");
            $stmt->bind_param("i", $ride_id);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Ride booked successfully. Pay the driver offline when you meet.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to book ride']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No seats available or ride has started']);
    }
    $stmt->close();
}
$conn->close();
?>