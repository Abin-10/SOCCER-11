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

// Fetch pending bookings
$pending_sql = "SELECT 
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

$pending_result = $conn->query($pending_sql);

// Fetch all recent bookings
$recent_sql = "SELECT 
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
WHERE tts.booking_status IN ('confirmed', 'rejected', 'pending')
ORDER BY tts.date DESC, fts.start_time DESC
LIMIT 10";

$recent_result = $conn->query($recent_sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - SOCCER-11</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4CAF50;
            --primary-dark: #388E3C;
            --primary-light: #C8E6C9;
            --accent: #2E7D32;
            --danger: #dc3545;
            --success: #28a745;
            --warning: #ffc107;
            --text-dark: #2c3e50;
            --text-light: #ffffff;
            --gray-light: #f8f9fa;
            --shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body { 
            background: #f4f6f9;
            color: var(--text-dark);
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Updated Sidebar Styles */
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

        /* Main Content Area */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }

        .page-header {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary);
        }

        /* Enhanced Table */
        .table-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .custom-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .custom-table th {
            background: var(--gray-light);
            color: var(--text-dark);
            font-weight: 600;
            padding: 15px 20px;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #dee2e6;
        }

        .custom-table td {
            padding: 20px;
            vertical-align: middle;
            border-bottom: 1px solid #eee;
            color: #444;
        }

        .custom-table tr:last-child td {
            border-bottom: none;
        }

        .custom-table tr:hover {
            background-color: var(--gray-light);
        }

        /* Customer Info */
        .customer-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .customer-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .customer-details {
            line-height: 1.4;
        }

        .customer-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .customer-phone {
            font-size: 0.85rem;
            color: #666;
        }

        /* Action Buttons */
        .btn {
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            border: none;
        }

        .btn-confirm {
            background: var(--success);
            color: white;
        }

        .btn-reject {
            background: var(--danger);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* Status Badge */
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .recent-bookings {
            margin-top: 40px;
        }

        .section-header h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        /* Add hover effect for recent bookings */
        .recent-bookings .custom-table tr:hover {
            background-color: rgba(76,175,80,0.05);
            cursor: default;
        }

        /* Add these new animation styles after your existing styles */
        
        /* Confirmation Animation Overlay */
        .confirmation-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .confirmation-animation {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            animation: scaleIn 0.3s ease-out;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .success-checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: var(--success);
            display: flex;
            align-items: center;
            justify-content: center;
            transform: scale(0);
            animation: checkmarkIn 0.5s ease-out 0.3s forwards;
        }

        .success-checkmark i {
            color: white;
            font-size: 40px;
            transform: scale(0);
            animation: iconPop 0.3s ease-out 0.8s forwards;
        }

        .confirmation-message {
            font-size: 1.2rem;
            color: var(--text-dark);
            margin: 20px 0;
            opacity: 0;
            transform: translateY(20px);
            animation: slideUp 0.3s ease-out 1s forwards;
        }

        /* Row fade out animation */
        .row-fade-out {
            animation: rowFadeOut 0.5s ease-out forwards;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes checkmarkIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        @keyframes iconPop {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes rowFadeOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(-20px);
                height: 0;
                margin: 0;
                padding: 0;
            }
        }

        /* Add confetti animation */
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--primary);
            opacity: 0;
        }

        @keyframes confettiRain {
            0% {
                opacity: 1;
                transform: translateY(-100%) rotate(0deg);
            }
            100% {
                opacity: 0;
                transform: translateY(100vh) rotate(360deg);
            }
        }

        /* Updated Header and Dropdown Styles */
        .page-header {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Dropdown Container */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        /* Dropdown Button */
        .dropdown-btn {
            background: linear-gradient(to right, #4CAF50 0%, #388E3C 100%);
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 5px 15px rgba(76,175,80,0.2);
        }

        .dropdown-btn:hover {
            background-position: right bottom;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(76,175,80,0.3);
        }

        .dropdown-icon {
            font-size: 12px;
            margin-left: 5px;
            transition: transform 0.3s ease;
        }

        /* Dropdown Content (Hidden by Default) */
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 220px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            z-index: 1000;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 10px;
            animation: fadeIn 0.3s ease-out;
        }

        /* Show the dropdown menu on hover */
        .dropdown:hover .dropdown-content {
            display: block;
        }

        /* Change rotation of dropdown icon when open */
        .dropdown:hover .dropdown-icon {
            transform: rotate(180deg);
        }

        /* Links inside the dropdown */
        .dropdown-content a {
            color: #333;
            padding: 14px 20px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .dropdown-content a:last-child {
            border-bottom: none;
        }

        .dropdown-content a i {
            width: 20px;
            color: #4CAF50;
        }

        .dropdown-content a:hover {
            background-color: rgba(76,175,80,0.1);
            transform: translateX(5px);
        }

        /* Special styling for logout item */
        .dropdown-content a.logout-item {
            color: #E53E3E;
        }

        .dropdown-content a.logout-item i {
            color: #E53E3E;
        }

        .dropdown-content a.logout-item:hover {
            background-color: rgba(229,62,62,0.1);
        }

        /* Animation for dropdown */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Update header styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            background: rgba(255,255,255,0.9);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.7);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dropdown-btn span {
                display: none;
            }
            
            .dropdown-btn {
                padding: 12px;
            }
            
            .dropdown-content {
                right: -50px;
            }
        }

        .btn-success {
            background-color: var(--success);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- New Sidebar -->
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

        <div class="main-content">
            <div class="header">
                <h1>Owner Dashboard</h1>
                <div class="dropdown">
                    <button class="dropdown-btn">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </button>
                    <div class="dropdown-content">
                        <a href="owner_settings.php">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                        <a href="logout.php" class="logout-item">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="stats-row fade-in">
                <div class="stat-card">
                    <h3><i class="fas fa-clock mr-2"></i>Pending Bookings</h3>
                    <div class="stat-value"><?php echo $pending_result->num_rows; ?></div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-calendar-check mr-2"></i>Total Bookings</h3>
                    <div class="stat-value"><?php echo $recent_result->num_rows; ?></div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-calendar-check mr-2"></i>Booking Management</h3>
                <button id="downloadBookings" class="btn btn-success">
                    <i class="fas fa-download mr-2"></i>Download All Bookings
                </button>
            </div>

            <!-- Table Container -->
            <div class="table-container fade-in">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Booking Details</th>
                            <th>Customer Information</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $pending_result->data_seek(0);
                        while ($booking = $pending_result->fetch_assoc()): 
                            $initial = strtoupper(substr($booking['user_name'], 0, 1));
                        ?>
                            <tr>
                                <td>
                                    <div class="booking-details">
                                        <div class="date"><i class="far fa-calendar-alt mr-2"></i><?php echo date('d M Y', strtotime($booking['date'])); ?></div>
                                        <div class="time text-muted">
                                            <i class="far fa-clock mr-2"></i>
                                            <?php echo date('h:i A', strtotime($booking['start_time'])) . ' - ' . 
                                                   date('h:i A', strtotime($booking['end_time'])); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar"><?php echo $initial; ?></div>
                                        <div class="customer-details">
                                            <div class="customer-name"><?php echo htmlspecialchars($booking['user_name']); ?></div>
                                            <div class="customer-phone">
                                                <i class="fas fa-phone-alt mr-1"></i>
                                                <?php echo htmlspecialchars($booking['user_phone']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-clock mr-1"></i>Pending
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-confirm confirm-booking mr-2" 
                                            data-booking-id="<?php echo $booking['id']; ?>" 
                                            data-action="confirm">
                                        <i class="fas fa-check"></i>Confirm
                                    </button>
                                    <button class="btn btn-reject reject-booking" 
                                            data-booking-id="<?php echo $booking['id']; ?>" 
                                            data-action="reject">
                                        <i class="fas fa-times"></i>Reject
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Bookings Section -->
            <div class="recent-bookings fade-in mt-4">
                <div class="section-header d-flex justify-content-between align-items-center mb-4">
                    <h3><i class="fas fa-history mr-2"></i>Recent Bookings</h3>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Booking Details</th>
                                <th>Customer Information</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            while ($booking = $recent_result->fetch_assoc()): 
                                $initial = strtoupper(substr($booking['user_name'], 0, 1));
                                $status_class = '';
                                $status_text = '';
                                
                                switch($booking['booking_status']) {
                                    case 'confirmed':
                                        $status_class = 'status-confirmed';
                                        $status_text = 'Confirmed';
                                        $status_icon = 'check-circle';
                                        break;
                                    case 'rejected':
                                        $status_class = 'status-rejected';
                                        $status_text = 'Rejected';
                                        $status_icon = 'times-circle';
                                        break;
                                    default:
                                        $status_class = 'status-pending';
                                        $status_text = 'Pending';
                                        $status_icon = 'clock';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <div class="booking-details">
                                            <div class="date">
                                                <i class="far fa-calendar-alt mr-2"></i>
                                                <?php echo date('d M Y', strtotime($booking['date'])); ?>
                                            </div>
                                            <div class="time text-muted">
                                                <i class="far fa-clock mr-2"></i>
                                                <?php echo date('h:i A', strtotime($booking['start_time'])) . ' - ' . 
                                                       date('h:i A', strtotime($booking['end_time'])); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="customer-info">
                                            <div class="customer-avatar"><?php echo $initial; ?></div>
                                            <div class="customer-details">
                                                <div class="customer-name">
                                                    <?php echo htmlspecialchars($booking['user_name']); ?>
                                                </div>
                                                <div class="customer-phone">
                                                    <i class="fas fa-phone-alt mr-1"></i>
                                                    <?php echo htmlspecialchars($booking['user_phone']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <i class="fas fa-<?php echo $status_icon; ?> mr-1"></i>
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="confirmation-overlay">
        <div class="confirmation-animation">
            <div class="success-checkmark">
                <i class="fas fa-check"></i>
            </div>
            <div class="confirmation-message">
                Booking Confirmed Successfully!
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.querySelector('.confirmation-overlay');
        
        // Create confetti elements
        function createConfetti() {
            const colors = ['#4CAF50', '#A5D6A7', '#81C784', '#66BB6A'];
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animation = `confettiRain ${1 + Math.random() * 2}s linear forwards`;
                document.body.appendChild(confetti);
                
                // Remove confetti after animation
                setTimeout(() => confetti.remove(), 3000);
            }
        }

        const handleBookingAction = async (bookingId, action, row) => {
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
                    if (action === 'confirm') {
                        // Show confirmation animation
                        overlay.style.display = 'flex';
                        
                        // Trigger confetti
                        createConfetti();
                        
                        // Fade out the row
                        row.classList.add('row-fade-out');
                        
                        // Wait for animations to complete
                        setTimeout(() => {
                            overlay.style.display = 'none';
                            location.reload();
                        }, 2500);
                    } else {
                        // For reject action, just reload
                        alert(data.message);
                        location.reload();
                    }
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
                const row = this.closest('tr');
                if (confirm('Are you sure you want to ' + this.dataset.action + ' this booking?')) {
                    handleBookingAction(this.dataset.bookingId, this.dataset.action, row);
                }
            });
        });

        // Add data-title attributes to menu items for mobile tooltips
        const menuItems = document.querySelectorAll('.menu-items a');
        menuItems.forEach(item => {
            const span = item.querySelector('span');
            if (span) {
                item.setAttribute('data-title', span.textContent);
            }
        });

        document.getElementById('downloadBookings').addEventListener('click', function() {
            window.location.href = 'export_bookings.php';
        });
    });
    </script>
</body>
</html>

<?php
$conn->close();
?>
