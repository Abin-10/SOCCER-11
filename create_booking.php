<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Database connection
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "registration";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Validate input
if (empty($_POST['customerName']) || empty($_POST['turf']) || empty($_POST['date']) || empty($_POST['time']) || empty($_POST['amount'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Get turf ID
    $stmt = $conn->prepare("SELECT id FROM turfs WHERE turf_name = ?");
    $stmt->bind_param('s', $_POST['turf']);
    $stmt->execute();
    $turf_result = $stmt->get_result();
    $turf = $turf_result->fetch_assoc();
    
    if (!$turf) {
        throw new Exception('Invalid turf selected');
    }

    // Get time slot ID
    $time_parts = explode(" - ", $_POST['time']);
    $start_time = $time_parts[0];
    $end_time = $time_parts[1];
    
    $stmt = $conn->prepare("SELECT id FROM fixed_time_slots WHERE start_time = ? AND end_time = ?");
    $stmt->bind_param('ss', $start_time, $end_time);
    $stmt->execute();
    $slot_result = $stmt->get_result();
    $slot = $slot_result->fetch_assoc();
    
    if (!$slot) {
        throw new Exception('Invalid time slot selected');
    }

    // Check if slot is available
    $stmt = $conn->prepare("SELECT id FROM turf_time_slots WHERE turf_id = ? AND date = ? AND slot_id = ? AND is_available = 1");
    $stmt->bind_param('isi', $turf['id'], $_POST['date'], $slot['id']);
    $stmt->execute();
    $availability_result = $stmt->get_result();
    $turf_slot = $availability_result->fetch_assoc();
    
    if (!$turf_slot) {
        throw new Exception('Selected slot is not available');
    }

    // Create booking
    $booking_id = 'BK' . time();
    $stmt = $conn->prepare("INSERT INTO bookings (booking_id, user_id, slot_id, amount, status) VALUES (?, ?, ?, ?, 'Confirmed')");
    $stmt->bind_param('siid', $booking_id, $_SESSION['user_id'], $turf_slot['id'], $_POST['amount']);
    $stmt->execute();

    // Update slot availability
    $stmt = $conn->prepare("UPDATE turf_time_slots SET is_available = 0 WHERE id = ?");
    $stmt->bind_param('i', $turf_slot['id']);
    $stmt->execute();

    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Booking created successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
