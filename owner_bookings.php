<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
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

// Fetch booking details
$sql = "SELECT 
            tts.id, 
            tts.turf_id,
            tts.booking_status,
            tts.booked_by,
            u.name as user_name,
            u.phone as user_phone,
            fts.start_time, 
            fts.end_time, 
            tts.date, 
            tts.is_available, 
            tts.is_owner_reserved 
        FROM turf_time_slots tts
        JOIN fixed_time_slots fts ON tts.slot_id = fts.id
        LEFT JOIN users u ON tts.booked_by = u.id
        WHERE tts.booking_status = 'pending'
        ORDER BY tts.date ASC, fts.start_time ASC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - SOCCER-11</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f0f2f5; 
            padding: 20px; 
            max-width: 1200px; 
            margin: 0 auto;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            background: white;
            margin-bottom: 20px;
        }
        th, td { 
            padding: 15px; 
            border: 1px solid #e0e0e0; 
            text-align: left; 
        }
        th { 
            background: #4CAF50; 
            color: white; 
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .status-available { 
            color: #2ecc71; 
            font-weight: bold; 
        }
        .status-reserved { 
            color: #e74c3c; 
            font-weight: bold; 
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }
        .back-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="owner.php" class="back-btn">← Back to Dashboard</a>
        <h2>Booking Details</h2>

        <div class="container mt-4">
            <h2>Pending Bookings</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($booking['date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($booking['start_time'])) . ' - ' . 
                                           date('h:i A', strtotime($booking['end_time'])); ?></td>
                                <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['user_phone']); ?></td>
                                <td>
                                    <button class="btn btn-success btn-sm confirm-booking" 
                                            data-booking-id="<?php echo $booking['id']; ?>" 
                                            data-action="confirm">
                                        Confirm
                                    </button>
                                    <button class="btn btn-danger btn-sm reject-booking" 
                                            data-booking-id="<?php echo $booking['id']; ?>" 
                                            data-action="reject">
                                        Reject
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const handleBookingAction = async (bookingId, action) => {
            try {
                const response = await fetch('confirm_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        booking_id: bookingId,
                        action: action
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Failed to process booking');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to process booking');
            }
        };

        document.querySelectorAll('.confirm-booking, .reject-booking').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to ' + this.dataset.action + ' this booking?')) {
                    handleBookingAction(this.dataset.bookingId, this.dataset.action);
                }
            });
        });
    });
    </script>
</body>
</html>

<?php
$conn->close();
?>
