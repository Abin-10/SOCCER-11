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

    // Query to get all time slots and check if they're booked
    $sql = "SELECT 
                    ts.id,
                    ts.start_time,
                    ts.end_time,
                    b.booking_id IS NOT NULL as booked
                FROM fixed_time_slots ts
                LEFT JOIN bookings b ON b.turf_time_slot_id = ts.id 
                    AND b.turf_id = ? 
                    AND DATE(b.created_at) = ?
                    AND b.status != 'cancelled'
                WHERE ts.id > 0
                ORDER BY ts.start_time";

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
    $slots = [];
    
    while ($row = $result->fetch_assoc()) {
        $slots[] = [
            'id' => $row['id'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'booked' => (bool)$row['booked']
        ];
    }

    // Log the response for debugging
    error_log("Response data: " . json_encode(['slots' => $slots]));
    
    // Clear any output buffered content
    ob_clean();
    
    // Send the JSON response
    echo json_encode(['slots' => $slots]);

} catch (Exception $e) {
    // Log the error
    error_log("Error occurred: " . $e->getMessage());
    
    // Clear any output buffered content
    ob_clean();
    
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}

// End output buffering and flush
ob_end_flush();
?>
