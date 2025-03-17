<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Database connection
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "registration";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add this function after the database connection
function generateFormattedBookingId($original_id, $created_at) {
    $date = new DateTime($created_at);
    $year = $date->format('Y');
    $month = $date->format('m');
    
    // Pad the ID with zeros to ensure at least 4 digits
    $padded_id = str_pad($original_id, 4, '0', STR_PAD_LEFT);
    
    // Format: BK-YYYYMM-####
    return "BK-{$year}{$month}-{$padded_id}";
}

// Fetch all bookings with detailed information
$sql = "SELECT 
    tts.id as booking_id,
    t.name as turf_name,
    t.location,
    t.hourly_rate,
    u.name as customer_name,
    u.email as customer_email,
    u.phone as customer_phone,
    fts.start_time,
    fts.end_time,
    tts.date,
    tts.booking_status,
    tts.created_at,
    tts.is_owner_reserved
FROM turf_time_slots tts
JOIN turf t ON tts.turf_id = t.turf_id
JOIN fixed_time_slots fts ON tts.slot_id = fts.id
LEFT JOIN users u ON tts.booked_by = u.id
WHERE tts.booking_status IS NOT NULL
ORDER BY tts.id ASC, tts.date DESC, fts.start_time ASC";

$result = $conn->query($sql);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="turf_bookings_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV headers
fputcsv($output, [
    'Booking ID',
    'Turf Name',
    'Location',
    'Customer Name',
    'Email',
    'Phone',
    'Date',
    'Start Time',
    'End Time',
    'Duration (Hours)',
    'Rate (₹)',
    'Total Amount (₹)',
    'Status',
    'Owner Reserved',
    'Booking Date'
]);

// Add data rows
while ($row = $result->fetch_assoc()) {
    // Calculate duration in hours
    $start = strtotime($row['start_time']);
    $end = strtotime($row['end_time']);
    $duration = ($end - $start) / 3600; // Convert seconds to hours
    
    // Calculate total amount (N/A for owner reservations)
    $rate = $row['is_owner_reserved'] ? 'N/A' : $row['hourly_rate'];
    $total_amount = $row['is_owner_reserved'] ? 'N/A' : ($duration * $row['hourly_rate']);
    
    // Generate formatted booking ID
    $formatted_booking_id = generateFormattedBookingId($row['booking_id'], $row['created_at']);
    
    fputcsv($output, [
        $formatted_booking_id,
        $row['turf_name'],
        $row['location'],
        $row['customer_name'] ?: 'N/A',
        $row['customer_email'] ?: 'N/A',
        $row['customer_phone'] ?: 'N/A',
        date('d/m/Y', strtotime($row['date'])),
        date('h:i A', strtotime($row['start_time'])),
        date('h:i A', strtotime($row['end_time'])),
        $duration,
        $rate,
        $total_amount,
        ucfirst($row['booking_status']),
        $row['is_owner_reserved'] ? 'Yes' : 'No',
        date('d/m/Y h:i A', strtotime($row['created_at']))
    ]);
}

fclose($output);
$conn->close();
?>