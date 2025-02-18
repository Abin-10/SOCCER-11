<?php
session_start();

// Check if user is owner
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['booking_id']) || !isset($data['action'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
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

    // Update booking status based on action
    $new_status = $data['action'] === 'confirm' ? 'confirmed' : 'cancelled';
    $is_available = $data['action'] === 'confirm' ? 0 : 1;

    $update_sql = "UPDATE turf_time_slots 
                   SET booking_status = ?, 
                       is_available = ? 
                   WHERE id = ? AND booking_status = 'pending'";
    
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sii", $new_status, $is_available, $data['booking_id']);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update booking status');
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('Booking not found or already processed');
    }

    // Commit the transaction
    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Booking ' . ($new_status === 'confirmed' ? 'confirmed' : 'rejected') . ' successfully'
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