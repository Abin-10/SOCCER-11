<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "registration");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$turf_id = isset($_GET['turf_id']) ? intval($_GET['turf_id']) : 0;

if ($turf_id > 0) {
    $sql = "SELECT turf_id, name, location, morning_rate, afternoon_rate, evening_rate, owner_id 
            FROM turf 
            WHERE turf_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $turf_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $turf = $result->fetch_assoc();
    
    // Ensure hourly_rate is returned as a number
    $turf['morning_rate'] = floatval($turf['morning_rate']);
    $turf['afternoon_rate'] = floatval($turf['afternoon_rate']);
    $turf['evening_rate'] = floatval($turf['evening_rate']);

    header('Content-Type: application/json');
    echo json_encode($turf);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid turf ID']);
}

$conn->close();
?> 