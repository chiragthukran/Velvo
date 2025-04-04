<?php
include '../../backend/config.php';

$start_location = $_GET['start_location'];
$end_location = isset($_GET['end_location']) ? $_GET['end_location'] : '';
$current_time = date('Y-m-d H:i:s');

$sql = "
    SELECT r.ride_id, r.start_location, r.end_location, r.price, r.available_seats, r.start_time, r.end_time, u.name AS driver_name
    FROM Rides r
    JOIN Users u ON r.driver_id = u.user_id
    WHERE r.start_location LIKE ? AND r.start_time > ? AND r.available_seats > 0
";
$params = ["%$start_location%", $current_time];
$types = "ss";

if (!empty($end_location)) {
    $sql .= " AND r.end_location LIKE ?";
    $params[] = "%$end_location%";
    $types .= "s";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$rides = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($rides);
$stmt->close();
$conn->close();
?>