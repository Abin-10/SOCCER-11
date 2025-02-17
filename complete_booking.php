<?php
session_start();

// Check if user is logged in and booking exists
if (!isset($_SESSION['user_id']) || !isset($_SESSION['booking'])) {
    header("Location: book_turf.php");
    exit();
}

$booking = $_SESSION['booking'];
$userId = $_SESSION['user_id'];

// Connect to database
$conn = new mysqli("localhost", "root", "", "registration");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if slot is still available
    $sql = "SELECT COUNT(*) as count FROM bookings 
            WHERE turf_id = ? AND date = ? AND time_slot_id = ? 
            AND status != 'cancelled'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $booking['turf_id'], $booking['date'], $booking['time_slot_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        throw new Exception("Sorry, this slot has been booked by someone else. Please choose another slot.");
    }

    // Get turf and time slot details for amount calculation
    $sql = "SELECT t.price_per_hour, ts.start_time, ts.end_time 
            FROM turfs t 
            JOIN time_slots ts ON ts.id = ? 
            WHERE t.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $booking['time_slot_id'], $booking['turf_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $details = $result->fetch_assoc();

    // Calculate amount
    $startTime = new DateTime($details['start_time']);
    $endTime = new DateTime($details['end_time']);
    $duration = $startTime->diff($endTime)->h;
    $amount = $duration * $details['price_per_hour'];

    // Generate booking ID
    $bookingId = 'BK' . date('Ymd') . rand(1000, 9999);

    // Insert booking
    $sql = "INSERT INTO bookings (booking_id, user_id, turf_id, date, time_slot_id, amount, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'confirmed', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siisid", $bookingId, $userId, $booking['turf_id'], $booking['date'], 
                      $booking['time_slot_id'], $amount);
    
    if (!$stmt->execute()) {
        throw new Exception("Error creating booking: " . $stmt->error);
    }

    // Insert payment record
    $sql = "INSERT INTO payments (booking_id, amount, payment_method, status, created_at) 
            VALUES (?, ?, ?, 'completed', NOW())";
    $stmt = $conn->prepare($sql);
    $paymentMethod = $_POST['payment_method'] ?? 'online';
    $stmt->bind_param("sds", $bookingId, $amount, $paymentMethod);
    
    if (!$stmt->execute()) {
        throw new Exception("Error recording payment: " . $stmt->error);
    }

    // Commit transaction
    $conn->commit();

    // Clear booking from session
    unset($_SESSION['booking']);

    // Set success message
    $_SESSION['success_message'] = "Booking confirmed! Your booking ID is " . $bookingId;
    
    // Redirect to success page
    header("Location: booking_success.php?id=" . $bookingId);
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Set error message
    $_SESSION['error_message'] = $e->getMessage();
    
    // Redirect back to booking page
    header("Location: book_turf.php");
    exit();
} finally {
    $conn->close();
}
