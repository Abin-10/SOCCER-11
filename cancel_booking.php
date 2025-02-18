<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit();
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['booking_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Booking ID not provided'
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

try {
    // Start transaction
    $conn->begin_transaction();

    // Verify the booking belongs to the user and is not already cancelled
    $check_sql = "SELECT id FROM turf_time_slots 
                  WHERE id = ? AND booked_by = ? AND booking_status != 'cancelled'";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $data['booking_id'], $_SESSION['user_id']);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invalid booking or already cancelled');
    }

    // Update the booking status
    $update_sql = "UPDATE turf_time_slots 
                   SET booking_status = 'cancelled', 
                       is_available = 1 
                   WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $data['booking_id']);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to cancel booking');
    }

    // Commit the transaction
    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Booking cancelled successfully'
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
    $conn->close();
}
?> 