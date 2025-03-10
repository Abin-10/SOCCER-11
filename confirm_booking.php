<?php
session_start();
require_once 'send_email.php';

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

    // Get the user ID and booking details first
    $get_booking_sql = "SELECT tts.booked_by, tts.date, fts.start_time, fts.end_time, u.email as user_email, u.name as user_name, t.name as turf_name, t.location 
                        FROM turf_time_slots tts
                        JOIN fixed_time_slots fts ON tts.slot_id = fts.id
                        JOIN users u ON tts.booked_by = u.id
                        JOIN turf t ON tts.turf_id = t.turf_id
                        WHERE tts.id = ?";
    $booking_stmt = $conn->prepare($get_booking_sql);
    $booking_stmt->bind_param("i", $data['booking_id']);
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result();
    $booking_data = $booking_result->fetch_assoc();

    if (!$booking_data) {
        throw new Exception('Booking not found');
    }

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

    // Create notification message
    $formatted_date = date('d M Y', strtotime($booking_data['date']));
    $formatted_time = date('h:i A', strtotime($booking_data['start_time'])) . ' - ' . 
                     date('h:i A', strtotime($booking_data['end_time']));
    
    $action_text = $data['action'] === 'confirm' ? 'confirmed' : 'rejected';
    $notification_message = "Your booking for {$formatted_date} at {$formatted_time} has been {$action_text}.";

    // Insert notification
    $notify_sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
    $notify_stmt = $conn->prepare($notify_sql);
    $notify_stmt->bind_param("is", $booking_data['booked_by'], $notification_message);
    
    if (!$notify_stmt->execute()) {
        throw new Exception('Failed to create notification');
    }

    // Send confirmation email
    $email_sent = sendBookingConfirmationEmail(
        $booking_data['user_email'],
        $booking_data['user_name'],
        $booking_data
    );

    // Commit the transaction
    $conn->commit();

    // Clear any output buffers
    ob_clean();
    
    // Send success response
    header('Content-Type: application/json');
    if ($email_sent) {
        echo json_encode([
            'success' => true,
            'message' => 'Booking has been ' . $action_text . ' successfully and confirmation email sent to customer'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Booking has been ' . $action_text . ' successfully but failed to send email notification'
        ]);
    }
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Clear any output buffers
    ob_clean();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
} finally {
    // Close all statements
    if (isset($booking_stmt)) $booking_stmt->close();
    if (isset($stmt)) $stmt->close();
    if (isset($notify_stmt)) $notify_stmt->close();
    $conn->close();
}

// Add this where you want to show the notification count
$notification_count_sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE";
$count_stmt = $conn->prepare($notification_count_sql);
$count_stmt->bind_param("i", $_SESSION['user_id']);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$unread_count = $count_result->fetch_assoc()['count'];
?>

<!-- Add this in your navigation bar -->
<a href="notifications.php" class="nav-link">
    <i class="fas fa-bell"></i>
    <?php if ($unread_count > 0): ?>
        <span class="badge badge-danger"><?php echo $unread_count; ?></span>
    <?php endif; ?>
</a>
?> 