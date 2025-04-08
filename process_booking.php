<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
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

    $user_id = $_SESSION['user_id'];
    $turf_id = $_POST['turf_id'];
    $slot_id = $_POST['time_slot'];
    $booking_date = $_POST['date'];
    $amount = calculateBookingAmount($conn, $turf_id, $slot_id);

    // First create the turf_time_slot entry
    $slot_sql = "INSERT INTO turf_time_slots (turf_id, slot_id, date, is_available, booked_by, booking_status) 
                 VALUES (?, ?, ?, 0, ?, 'pending')";
    
    $slot_stmt = $conn->prepare($slot_sql);
    $slot_stmt->bind_param("iisi", $turf_id, $slot_id, $booking_date, $user_id);
    
    if (!$slot_stmt->execute()) {
        throw new Exception('Failed to create booking slot');
    }

    $booking_id = $conn->insert_id;

    // Get slot timing details
    $time_sql = "SELECT start_time, end_time FROM fixed_time_slots WHERE id = ?";
    $time_stmt = $conn->prepare($time_sql);
    $time_stmt->bind_param("i", $slot_id);
    $time_stmt->execute();
    $time_result = $time_stmt->get_result();
    $time_data = $time_result->fetch_assoc();

    // Create detailed booking record
    $detail_sql = "INSERT INTO booking_details (
        booking_id, user_id, turf_id, slot_id, date, 
        start_time, end_time, amount, booking_status, 
        payment_status, payment_method
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', 'online')";

    $detail_stmt = $conn->prepare($detail_sql);
    $detail_stmt->bind_param(
        "iiiisssd",
        $booking_id,
        $user_id,
        $turf_id,
        $slot_id,
        $booking_date,
        $time_data['start_time'],
        $time_data['end_time'],
        $amount
    );

    if (!$detail_stmt->execute()) {
        throw new Exception('Failed to store booking details');
    }

    // Create notification
    $notify_sql = "INSERT INTO notifications (user_id, message) 
                  VALUES (?, 'Your booking request has been received and is pending confirmation.')";
    $notify_stmt = $conn->prepare($notify_sql);
    $notify_stmt->bind_param("i", $user_id);
    $notify_stmt->execute();

    // Update turf time slots availability
    $update_sql = "UPDATE turf_time_slots SET is_available = 0 WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $slot_id);
    $update_stmt->execute();

    // Insert payment record
    $payment_sql = "INSERT INTO payments (
        turf_time_slot_id,
        amount,
        payment_date,
        payment_status
    ) VALUES (?, ?, NOW(), ?)";

    $payment_stmt = $conn->prepare($payment_sql);
    $payment_stmt->bind_param(
        "ids",
        $booking_id,
        $_POST['payment_amount'],
        $_POST['payment_status']
    );

    if (!$payment_stmt->execute()) {
        throw new Exception('Failed to record payment');
    }

    // Commit transaction
    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully',
        'booking_id' => $booking_id
    ]);

} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Close all prepared statements
    if (isset($slot_stmt)) $slot_stmt->close();
    if (isset($time_stmt)) $time_stmt->close();
    if (isset($detail_stmt)) $detail_stmt->close();
    if (isset($notify_stmt)) $notify_stmt->close();
    if (isset($update_stmt)) $update_stmt->close();
    if (isset($payment_stmt)) $payment_stmt->close();
    
    // Close the connection last, after all operations are complete
    if (isset($conn)) $conn->close();
}

function calculateBookingAmount($conn, $turf_id, $slot_id) {
    // Get slot timing to determine which rate to use
    $slot_sql = "SELECT start_time FROM fixed_time_slots WHERE id = ?";
    $slot_stmt = $conn->prepare($slot_sql);
    $slot_stmt->bind_param("i", $slot_id);
    $slot_stmt->execute();
    $slot_result = $slot_stmt->get_result();
    $slot_data = $slot_result->fetch_assoc();
    $slot_stmt->close();

    // Get hour from slot start time
    $slotHour = (int)date('H', strtotime($slot_data['start_time']));

    // Get turf rates
    $rate_sql = "SELECT morning_rate, afternoon_rate, evening_rate FROM turf WHERE turf_id = ?";
    $rate_stmt = $conn->prepare($rate_sql);
    $rate_stmt->bind_param("i", $turf_id);
    $rate_stmt->execute();
    $rate_result = $rate_stmt->get_result();
    $rate_data = $rate_result->fetch_assoc();
    $rate_stmt->close();
    
    // Determine rate based on slot time
    if ($slotHour >= 6 && $slotHour <= 10) {
        return floatval($rate_data['morning_rate']);
    } else if ($slotHour > 10 && $slotHour <= 16) {
        return floatval($rate_data['afternoon_rate']);
    } else if ($slotHour > 16 && $slotHour <= 23) {
        return floatval($rate_data['evening_rate']);
    } else {
        return floatval($rate_data['morning_rate']); // Default to morning rate
    }
}
?>
