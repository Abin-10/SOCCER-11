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
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body { 
            background: linear-gradient(135deg, #f1f8e9 0%, #e8f5e9 100%);
            margin: 0;
        }
        
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }
        
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(180deg, #4CAF50 0%, #388E3C 100%);
            color: white;
            padding: 25px;
            position: fixed;
            height: 100vh;
            box-shadow: 4px 0 25px rgba(76,175,80,0.2);
        }
        .logo {
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 35px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logo i {
            font-size: 28px;
            color: #A5D6A7;
            text-shadow: 0 0 15px rgba(165,214,167,0.4);
        }
        .logo h2 {
            font-size: 20px;
            color: white;
            margin: 0;
        }
        .menu-items {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .menu-items a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px 20px;
            border-radius: 12px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 8px;
            background: linear-gradient(to right, transparent 0%, #ffffff 100%);
            background-size: 200% 100%;
            background-position: left bottom;
            border: 1px solid rgba(255,255,255,0.1);
            position: relative;
        }
        .menu-items a:hover {
            background-position: right bottom;
            transform: translateX(12px);
            box-shadow: 0 6px 20px rgba(76,175,80,0.25);
            color: #2E7D32;
        }
        .menu-items a.active {
            background: white;
            color: #2E7D32;
            box-shadow: 0 6px 20px rgba(76,175,80,0.25);
        }
        .menu-items i {
            width: 20px;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .menu-items a:hover i {
            transform: scale(1.2) rotate(5deg);
            color: #2E7D32;
        }
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 80px;
                padding: 15px;
            }
            .logo h2,
            .menu-items span {
                display: none;
            }
            .menu-items a {
                padding: 15px;
                justify-content: center;
            }
            .menu-items i {
                margin: 0;
            }
            .main-content {
                margin-left: 80px;
                padding: 20px;
            }
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
    <div class="dashboard">
        <!-- New Sidebar HTML -->
        <div class="admin-sidebar">
            <div class="logo">
                <i class="fas fa-futbol"></i>
                <h2>SOCCER-11</h2>
            </div>
            <ul class="menu-items">
                <li>
                    <a href="owner.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="owner_bookings.php" class="active">
                        <i class="fas fa-calendar"></i>
                        <span>Bookings</span>
                    </a>
                </li>
                <li>
                    <a href="owner_customer.php">
                        <i class="fas fa-users"></i>
                        <span>Customers</span>
                    </a>
                </li>
                <li>
                    <a href="owner_time_slots.php">
                        <i class="fas fa-clock"></i>
                        <span>Time Slots</span>
                    </a>
                </li>
                <li>
                    <a href="owner_reviews.php">
                        <i class="fas fa-star"></i>
                        <span>Reviews</span>
                    </a>
                </li>
                <li>
                    <a href="owner_settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container">
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
