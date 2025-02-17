<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['turf_id']) || !isset($_POST['date']) || !isset($_POST['time_slot'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "registration");
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit();
}

// Store booking details
$user_id = $_SESSION['user_id'];
$turf_id = $_POST['turf_id'];
$time_slot_id = $_POST['time_slot'];
$is_owner_booking = isset($_POST['is_owner_booking']) ? 1 : 0;
$status = 'confirmed';  // Default status

// First, verify that the time slot exists
$verify_slot_sql = "SELECT id FROM fixed_time_slots WHERE id = ?";
$verify_stmt = $conn->prepare($verify_slot_sql);
$verify_stmt->bind_param("i", $time_slot_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid time slot selected'
    ]);
    exit();
}
$verify_stmt->close();

// Check if this slot is already booked
$check_sql = "SELECT booking_id FROM bookings WHERE turf_id = ? AND turf_time_slot_id = ? AND status != 'cancelled'";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $turf_id, $time_slot_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'This time slot is already booked'
    ]);
    exit();
}
$check_stmt->close();

// Insert the booking
$sql = "INSERT INTO bookings (user_id, turf_id, turf_time_slot_id, status, is_owner_booking) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiisi", $user_id, $turf_id, $time_slot_id, $status, $is_owner_booking);

header('Content-Type: application/json');

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create booking: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
