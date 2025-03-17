<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user's name from the session
$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>SOCCER-11 Notifications</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport">

        <!-- Favicon -->
        <link href="img/favicon.ico" rel="icon">

        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Oswald:wght@400;700&display=swap" rel="stylesheet"> 

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

        <!-- Custom Styles -->
        <link href="css/style.css" rel="stylesheet">
        
        <style>
            /* Copy the admin-sidebar styles from userdashboard.php */
            .admin-sidebar {
                background: #f8f9fa;
                min-height: 100vh;
                padding: 20px;
                border-right: 1px solid #dee2e6;
                box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            }
            
            .admin-content {
                padding: 20px;
            }
            
            .admin-nav-link {
                color: #333;
                padding: 10px 15px;
                display: block;
                border-radius: 4px;
                margin-bottom: 5px;
            }
            
            .admin-nav-link:hover {
                background: #e9ecef;
                text-decoration: none;
            }
            
            .admin-nav-link.active {
                background: rgb(24, 137, 32);
                color: #fff;
            }

            /* Enhanced styles for notifications */
            .notification-item {
                padding: 20px;
                border-bottom: 1px solid #eee;
                transition: all 0.3s ease;
                position: relative;
                cursor: pointer;
            }

            .notification-item:hover {
                background-color: #f8f9fa;
                transform: translateX(5px);
            }

            .notification-item.unread {
                background-color: #e8f4ff;
                border-left: 4px solid #007bff;
            }

            .notification-item.unread:hover {
                background-color: #d8ebff;
            }

            .notification-time {
                color: #6c757d;
                font-size: 0.85rem;
                font-weight: 500;
            }

            .card {
                border: none;
                border-radius: 15px;
                box-shadow: 0 0 20px rgba(0,0,0,0.05);
                overflow: hidden;
            }

            .card-body {
                padding: 0;
            }

            /* Animation for new notifications */
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .notification-item {
                animation: fadeIn 0.5s ease-out;
            }

            /* Empty state styling */
            .no-notifications {
                padding: 40px 20px;
                text-align: center;
                color: #6c757d;
            }

            .no-notifications i {
                font-size: 48px;
                margin-bottom: 15px;
                color: #dee2e6;
            }
        </style>
    </head>

    <body>
        <!-- Nav Start -->
        <nav class="navbar navbar-expand-md bg-light navbar-light">
            <a href="userdashboard.php" class="navbar-brand">SOCCER-11</a>
            <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle mr-2"></i><?php echo htmlspecialchars($user_name); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="profile.php"><i class="fas fa-user mr-2"></i>Profile</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
        <!-- Nav End -->
        
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-md-2 admin-sidebar">
                    <h4 class="mb-4">Dashboard</h4>
                    <a href="userdashboard.php" class="admin-nav-link"><i class="fas fa-home mr-2"></i>Overview</a>
                    <a href="book_turf.php" class="admin-nav-link"><i class="fas fa-calendar-alt mr-2"></i>Book Turf</a>
                    <a href="my_bookings.php" class="admin-nav-link"><i class="fas fa-list mr-2"></i>My Bookings</a>
                    <a href="notifications.php" class="admin-nav-link active"><i class="fas fa-bell mr-2"></i>Notifications</a>
                    <a href="contact.php" class="admin-nav-link"><i class="fas fa-envelope mr-2"></i>Reviews</a>
                </div>

                <!-- Main Content -->
                <div class="col-md-10 admin-content">
                    <h2 class="mb-4">
                        <i class="fas fa-bell mr-2" style="color: #007bff;"></i>
                        Notifications
                    </h2>
                    
                    <div class="card">
                        <div class="card-body">
                            <?php
                            // Database connection
                            $conn = new mysqli("localhost", "root", "", "registration");
                            
                            if ($conn->connect_error) {
                                echo "<div class='no-notifications'>";
                                echo "<i class='fas fa-exclamation-circle'></i>";
                                echo "<p class='text-danger'>Unable to fetch notifications</p>";
                                echo "</div>";
                            } else {
                                // Get notifications for the user
                                $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $_SESSION['user_id']);
                                
                                if ($stmt->execute()) {
                                    $result = $stmt->get_result();
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $unreadClass = $row['is_read'] ? '' : 'unread';
                                            echo "<div class='notification-item {$unreadClass}'>";
                                            echo "<div class='d-flex justify-content-between align-items-center'>";
                                            echo "<div class='notification-content'>";
                                            if (!$row['is_read']) {
                                                echo "<i class='fas fa-circle mr-2' style='color: #007bff; font-size: 8px;'></i>";
                                            }
                                            echo "<span>" . htmlspecialchars($row['message']) . "</span>";
                                            echo "</div>";
                                            echo "<span class='notification-time'>" . date('M d, Y H:i', strtotime($row['created_at'])) . "</span>";
                                            echo "</div>";
                                            echo "</div>";
                                        }
                                    } else {
                                        echo "<div class='no-notifications'>";
                                        echo "<i class='far fa-bell'></i>";
                                        echo "<p>No notifications found</p>";
                                        echo "</div>";
                                    }
                                } else {
                                    echo "<div class='no-notifications'>";
                                    echo "<i class='fas fa-exclamation-circle'></i>";
                                    echo "<p class='text-danger'>Error loading notifications</p>";
                                    echo "</div>";
                                }
                                
                                $stmt->close();
                                $conn->close();
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- jQuery and Bootstrap Bundle -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html> 