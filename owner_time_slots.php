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

// Clean up past dates from turf_time_slots
$cleanup_sql = "DELETE FROM turf_time_slots WHERE date < CURRENT_DATE";
$conn->query($cleanup_sql);

// Handle adding slot to turf_time_slots
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['slot_id'])) {
        $slot_id = $_POST['slot_id'];
        $turf_id = 1; // Assuming single turf
        $date = isset($_POST['selected_date']) ? $_POST['selected_date'] : date('Y-m-d');
        $owner_id = $_SESSION['user_id'];
        
        // Check if slot already exists for this date
        $check_sql = "SELECT id FROM turf_time_slots 
                      WHERE turf_id = ? AND slot_id = ? AND date = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("iis", $turf_id, $slot_id, $date);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            $sql = "INSERT INTO turf_time_slots (turf_id, slot_id, date, is_available, is_owner_reserved, booked_by, booking_status) 
                    VALUES (?, ?, ?, 0, 1, ?, 'Reserved by Owner')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisi", $turf_id, $slot_id, $date, $owner_id);
            $stmt->execute();
        }
    }
}

// Get selected date (default to current date if not set)
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch available fixed time slots
$sql = "SELECT fts.* 
        FROM fixed_time_slots fts
        LEFT JOIN turf_time_slots tts ON fts.id = tts.slot_id 
            AND tts.date = ?
        WHERE tts.id IS NULL
        ORDER BY fts.start_time";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_date);
$stmt->execute();
$result = $stmt->get_result();

// Fetch reserved time slots for selected date
$reserved_sql = "SELECT 
    fts.start_time, 
    fts.end_time, 
    tts.date, 
    tts.is_available,
    COALESCE(tts.booking_status, 'Available') as booking_status,
    COALESCE(u.name, 'Not Booked') as booked_by_name
FROM turf_time_slots tts
JOIN fixed_time_slots fts ON tts.slot_id = fts.id
LEFT JOIN users u ON tts.booked_by = u.id
WHERE tts.date = ?
ORDER BY fts.start_time";
$reserved_stmt = $conn->prepare($reserved_sql);
$reserved_stmt->bind_param("s", $selected_date);
$reserved_stmt->execute();
$reserved_result = $reserved_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Slots Management - SOCCER-11</title>
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
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Remove old sidebar styles and add new ones */
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
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }

        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #2E7D32;
            font-size: 24px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-name {
            color: #2E7D32;
            font-weight: 600;
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff4b4b 0%, #ff416c 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,75,75,0.3);
        }

        .time-slots-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .time-slots-container h2 {
            color: #2E7D32;
            margin-bottom: 20px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .slots-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .slots-table th {
            background: #4CAF50;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-radius: 8px;
        }

        .slots-table td {
            padding: 15px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }

        .slots-table tr td:first-child {
            border-left: 1px solid #eee;
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .slots-table tr td:last-child {
            border-right: 1px solid #eee;
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .add-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76,175,80,0.3);
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .status.available {
            background: #e8f5e9;
            color: #2E7D32;
        }

        .status.reserved {
            background: #ffebee;
            color: #c62828;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Replace old sidebar with new one -->
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
                    <a href="owner_bookings.php">
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
                    <a href="owner_time_slots.php" class="active">
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
            <div class="header">
                <h1>Time Slots Management</h1>
                <div class="user-info">
                    <span class="user-name">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>

            <!-- Add this after the header div and before the time-slots-container -->
            <div class="date-selector" style="margin-bottom: 20px;">
                <form method="GET" id="dateForm">
                    <label for="date">Select Date:</label>
                    <input type="date" 
                           id="date" 
                           name="date" 
                           value="<?php echo $selected_date; ?>" 
                           min="<?php echo date('Y-m-d'); ?>" 
                           onchange="this.form.submit()">
                </form>
            </div>

            <?php if (isset($_GET['success'])): ?>
            <div class="success-message" style="display: block;">
                Time slots have been successfully generated!
            </div>
            <?php endif; ?>

            <!-- Available Time Slots -->
            <div class="time-slots-container">
                <h2><i class="fas fa-clock"></i> Available Fixed Time Slots</h2>
                <table class="slots-table">
                    <thead>
                        <tr>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['start_time']); ?></td>
                            <td><?php echo htmlspecialchars($row['end_time']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="slot_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="selected_date" value="<?php echo $selected_date; ?>">
                                    <button type="submit" class="add-btn">
                                        <i class="fas fa-plus"></i>
                                        Add to Schedule
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Reserved Time Slots -->
            <div class="time-slots-container">
                <h2><i class="fas fa-calendar-check"></i> Reserved Time Slots</h2>
                <table class="slots-table">
                    <thead>
                        <tr>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($reserved = $reserved_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reserved['start_time']); ?></td>
                            <td><?php echo htmlspecialchars($reserved['end_time']); ?></td>
                            <td>
                                <?php if ($reserved['is_available']): ?>
                                    <span class="status available">Available</span>
                                <?php else: ?>
                                    <span class="status reserved">
                                        Reserved by <?php echo htmlspecialchars($reserved['booked_by_name']); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Add date validation
        document.addEventListener('DOMContentLoaded', function() {
            const startDate = document.querySelector('input[name="start_date"]');
            const endDate = document.querySelector('input[name="end_date"]');

            startDate.addEventListener('change', function() {
                endDate.min = this.value;
            });

            endDate.addEventListener('change', function() {
                if (this.value < startDate.value) {
                    this.value = startDate.value;
                }
            });
        });
    </script>
</body>
</html> 