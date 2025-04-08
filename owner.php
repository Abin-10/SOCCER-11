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

// Get total users count
$users_query = "SELECT COUNT(*) as total_users FROM users";
$users_result = $conn->query($users_query);
$total_users = $users_result->fetch_assoc()['total_users'];

// Get turf details for the owner
$turf_query = "SELECT t.turf_id, t.name AS turf_name, t.location, 
               t.morning_rate, t.afternoon_rate, t.evening_rate 
               FROM turf t 
               WHERE t.owner_id = ?";
$stmt = $conn->prepare($turf_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$turf_result = $stmt->get_result();
$total_turfs = $turf_result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard - SOCCER-11</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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

        /* Sidebar Styles */
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

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }

        /* Updated Header and Dropdown Styles */
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

        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            backdrop-filter: blur(10px);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(76,175,80,0.05) 0%, rgba(56,142,60,0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .card:hover::before {
            opacity: 1;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .card-header i {
            font-size: 32px;
            color:rgb(38, 132, 41);
            background: linear-gradient(135deg, #E8F5E9 0%, #F1F8E9 100%);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(76,175,80,0.1);
        }

        .card .number {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-top: 10px;
        }

        /* Recent Activity Table */
        .recent-activity {
            background: rgba(255,255,255,0.9);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.7);
        }

        .activity-table {
            margin-top: 20px;
            width: 100%;
            overflow-x: auto;
        }

        .activity-table table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
        }

        .activity-table th {
            background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
            color: white;
            padding: 15px 20px;
            text-align: left;
        }

        .activity-table td {
            padding: 15px 20px;
            background: white;
        }

        .activity-table tr {
            margin-bottom: 10px;
            transition: transform 0.3s ease;
        }

        .activity-table tr:hover {
            transform: translateY(-2px);
        }

        .status {
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .status.active {
            background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(76,175,80,0.2);
        }

        .status.pending {
            background: linear-gradient(135deg, #81C784 0%, #66BB6A 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(129,199,132,0.2);
        }

        .logout-btn {
            background: linear-gradient(135deg, #E53E3E 0%, #C53030 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.4s ease;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(229,62,62,0.2);
        }

        .logout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255,71,87,0.3);
        }

        /* Responsive Design */
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

            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }

        /* Updated Turf Details Section */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(76,175,80,0.1);
        }

        .section-header h3 {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
            color: #2E7D32;
        }

        .section-header h3 i {
            font-size: 24px;
            color: #4CAF50;
        }

        .add-turf-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .add-turf-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(76,175,80,0.3);
        }

        .turf-cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            padding: 10px;
        }

        .turf-detail-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(76,175,80,0.1);
        }

        .turf-detail-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(76,175,80,0.15);
        }

        .turf-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .turf-header i {
            font-size: 28px;
            color: #4CAF50;
            background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%);
            padding: 15px;
            border-radius: 12px;
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-badge.active {
            background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
            color: white;
        }

        .turf-detail-card h4 {
            color: #2E7D32;
            margin: 15px 0;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .turf-info {
            margin: 15px 0;
        }

        .turf-info p {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
            color: #555;
        }

        .turf-info i {
            color: #4CAF50;
            width: 20px;
        }

        .turf-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .turf-actions button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .edit-btn {
            background: linear-gradient(135deg, #FFA726 0%, #FB8C00 100%);
            color: white;
        }

        .view-btn {
            background: linear-gradient(135deg, #26C6DA 0%, #00ACC1 100%);
            color: white;
        }

        .turf-actions button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        @media (max-width: 768px) {
            .turf-cards-container {
                grid-template-columns: 1fr;
            }
            
            .turf-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="logo">
                <i class="fas fa-futbol"></i>
                <h2>SOCCER-11</h2>
            </div>
            <ul class="menu-items">
                <li>
                    <a href="owner.php" class="active">
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
            <div class="header">
                <h1>Owner Dashboard</h1>
                <div class="user-controls">
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
                        </div>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>

            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <h3>Total Users</h3>
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="number"><?php echo $total_users; ?></div>
                    <p>Manage your users effectively and ensure a great experience!</p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>My Turfs</h3>
                        <i class="fas fa-futbol"></i>
                    </div>
                    <div class="number"><?php echo $total_turfs; ?></div>
                    <p>Your registered turf facilities</p>
                </div>
            </div>

            <!-- Turf Details Section -->
            <div class="recent-activity">
                <div class="section-header">
                    <h3><i class="fas fa-futbol"></i> My Turf Details</h3>
                </div>
                <div class="turf-cards-container">
                    <?php while ($turf = $turf_result->fetch_assoc()): ?>
                    <div class="turf-detail-card">
                        <div class="turf-header">
                            <i class="fas fa-futbol"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($turf['turf_name']); ?></h4>
                        <div class="turf-info">
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($turf['location']); ?></p>
                            <p><i class="fas fa-rupee-sign"></i> Morning Rate: <?php echo htmlspecialchars($turf['morning_rate']); ?>/hour</p>
                            <p><i class="fas fa-rupee-sign"></i> Afternoon Rate: <?php echo htmlspecialchars($turf['afternoon_rate']); ?>/hour</p>
                            <p><i class="fas fa-rupee-sign"></i> Evening Rate: <?php echo htmlspecialchars($turf['evening_rate']); ?>/hour</p>
                        </div>
                        <div class="turf-actions">
                            <button class="edit-btn" onclick="editTurf(<?php echo $turf['turf_id']; ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="view-btn" onclick="viewBookings(<?php echo $turf['turf_id']; ?>)">
                                <i class="fas fa-calendar"></i> View Bookings
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add any JavaScript functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // Example: Add active class to current menu item
            const menuItems = document.querySelectorAll('.menu-items a');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    menuItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });

        function editTurf(turfId) {
            window.location.href = `edit_turf.php?id=${turfId}`;
        }

        function viewBookings(turfId) {
            window.location.href = `owner_bookings.php?turf_id=${turfId}`;
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 