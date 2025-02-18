<?php
// Start output buffering to catch any unwanted output
ob_start();

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Clear any previous output
ob_clean();

header('Content-Type: application/json');

try {
    // Log request parameters
    error_log("Request parameters: " . json_encode($_GET));
    
    // Database connection
    $conn = new mysqli("localhost", "root", "", "registration");
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get parameters
    $date = $_GET['date'] ?? '';
    $turf_id = $_GET['turf_id'] ?? '';

    if (empty($date) || empty($turf_id)) {
        throw new Exception('Missing required parameters');
    }

    // Query to get all time slots and their booking status
    $sql = "SELECT 
                fts.id,
                fts.start_time,
                fts.end_time,
                tts.id as slot_booking_id,
                COALESCE(tts.booking_status, 'available') as booking_status,
                COALESCE(tts.is_available, 1) as is_available,
                COALESCE(tts.is_owner_reserved, 0) as is_owner_reserved,
                COALESCE(tts.booked_by, 0) as booked_by
            FROM fixed_time_slots fts
            LEFT JOIN turf_time_slots tts ON fts.id = tts.slot_id 
                AND tts.turf_id = ? 
                AND tts.date = ?
            ORDER BY fts.start_time";

    // Log the SQL query for debugging
    error_log("SQL Query: " . $sql);
    error_log("Parameters: turf_id=" . $turf_id . ", date=" . $date);

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("is", $turf_id, $date);
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Result fetch failed: " . $stmt->error);
    }

    $slots = [];

    while ($row = $result->fetch_assoc()) {
        // A slot is available if:
        // 1. It has no booking entry (booking_status is 'available'), or
        // 2. The existing booking is cancelled
        // 3. The slot is not owner reserved
        $isAvailable = (
            $row['booking_status'] === 'available' || 
            $row['booking_status'] === 'cancelled'
        ) && !$row['is_owner_reserved'];

        $slots[] = [
            'id' => $row['id'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'booking_status' => $row['booking_status'],
            'is_available' => $isAvailable,
            'slot_booking_id' => $row['slot_booking_id']
        ];
    }

    // Log the response data
    error_log("Response data: " . json_encode(['slots' => $slots]));

    // Make sure we have a clean output buffer
    ob_clean();
    
    // Send the JSON response
    echo json_encode(['slots' => $slots]);

} catch (Exception $e) {
    // Log the error
    error_log("Error occurred: " . $e->getMessage());
    
    // Clean the output buffer
    ob_clean();
    
    // Send error response
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    // Close database resources
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
    
    // End output buffering and flush
    ob_end_flush();
}
?>
