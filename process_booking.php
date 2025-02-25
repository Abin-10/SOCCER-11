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
$booking_date = $_POST['date'];
$is_owner_booking = isset($_POST['is_owner_booking']) ? 1 : 0;
$status = 'pending';  // Default status is pending until owner confirms

try {
    // Start transaction
    $conn->begin_transaction();

    // First, verify that the time slot exists
    $verify_slot_sql = "SELECT id FROM fixed_time_slots WHERE id = ?";
    $verify_stmt = $conn->prepare($verify_slot_sql);
    $verify_stmt->bind_param("i", $time_slot_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows === 0) {
        throw new Exception('Invalid time slot selected');
    }
    $verify_stmt->close();

    // Check if this slot is already booked
    $check_sql = "SELECT id FROM turf_time_slots 
                  WHERE turf_id = ? 
                  AND slot_id = ? 
                  AND date = ? 
                  AND (booking_status = 'confirmed' OR booking_status = 'pending')";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("iis", $turf_id, $time_slot_id, $booking_date);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        throw new Exception('This time slot is already booked');
    }
    $check_stmt->close();

    // Insert or update the turf_time_slots entry
    $booking_sql = "INSERT INTO turf_time_slots 
                    (turf_id, slot_id, date, is_available, is_owner_reserved, booked_by, booking_status, created_at) 
                    VALUES (?, ?, ?, 0, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                    is_available = 0,
                    is_owner_reserved = ?,
                    booked_by = ?,
                    booking_status = ?";
    
    $stmt = $conn->prepare($booking_sql);
    $stmt->bind_param("iisiisiss", 
        $turf_id, 
        $time_slot_id, 
        $booking_date, 
        $is_owner_booking,
        $user_id,
        $status,
        $is_owner_booking,
        $user_id,
        $status
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to create booking');
    }

    // Commit the transaction
    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully'
    ]);

} catch (Exception $e) {
    // Rollback the transaction
    $conn->rollback();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    $stmt->close();
    $conn->close();
}
?>
