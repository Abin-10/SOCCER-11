<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "registration");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$turf_id = isset($_GET['turf_id']) ? intval($_GET['turf_id']) : 0;

if ($turf_id > 0) {
    $stmt = $conn->prepare("SELECT turf_id, name, location, hourly_rate FROM turf WHERE turf_id = ?");
    $stmt->bind_param("i", $turf_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $turf = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode($turf);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid turf ID']);
}

$conn->close();
?> 