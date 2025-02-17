<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get the admin's name from the session
$admin_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>SOCCER-11 Admin Dashboard</title>
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
            .admin-sidebar {
                background: #f8f9fa;
                min-height: 100vh;
                padding: 20px;
                border-right: 1px solid #dee2e6;
            }
            
            .admin-content {
                padding: 20px;
            }
            
            .stats-card {
                background: #fff;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        </style>
    </head>

    <body>
        <!-- Nav Start -->
        <nav class="navbar navbar-expand-md bg-light navbar-light">
            <a href="#" class="navbar-brand">SOCCER-11 ADMIN</a>
            <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle mr-2"></i> <?php echo htmlspecialchars($admin_name); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="adminDropdown">
                            <a class="dropdown-item" href="admin-profile.php"><i class="fas fa-user mr-2"></i> Profile</a>
                            <a class="dropdown-item" href="settings.php"><i class="fas fa-cog mr-2"></i> Settings</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
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
                    <a href="#" class="admin-nav-link active"><i class="fas fa-home mr-2"></i>Overview</a>
                    <a href="booking.php" class="admin-nav-link"><i class="fas fa-calendar-alt mr-2"></i>Bookings</a>
                    <a href="users.php" class="admin-nav-link"><i class="fas fa-users mr-2"></i>Users</a>
                    <a href="turfs.php" class="admin-nav-link"><i class="fas fa-football-ball mr-2"></i>Turfs</a>
                    <a href="admin_fixed_slots.php" class="admin-nav-link"><i class="fas fa-clock mr-2"></i>Manage Slots</a> <!-- New Link -->
                    <a href="settings.php" class="admin-nav-link"><i class="fas fa-cog mr-2"></i>Settings</a>
                </div>

                <!-- Main Content -->
                <div class="col-md-10 admin-content">
                    <h2 class="mb-4">Welcome, Admin!</h2>
                    
                    <div class="stats-card">
                        <h4>About SOCCER-11</h4>
                        <p>SOCCER-11 is a premier soccer turf booking platform that enables users to book high-quality turfs for their matches and practice sessions. Our platform ensures easy and efficient management of bookings and facilities.</p>
                    </div>
                    
                    <div class="stats-card">
                        <h4>Admin Responsibilities</h4>
                        <p>As an admin, you are responsible for managing bookings, overseeing user accounts, updating turf details, and ensuring a smooth experience for users. Use the navigation panel to manage the platform effectively.</p>
                    </div>
                    
                    <div class="stats-card">
                        <h4>Turf Information</h4>
                        <p>We have multiple turfs available for booking, each with different time slots. Admins can update turf availability, pricing, and other essential details to keep the platform up to date.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- jQuery and Bootstrap Bundle (includes Popper.js) -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Initialize Dropdown Manually (If Needed) -->
        <script>
            $(document).ready(function() {
                $('.dropdown-toggle').dropdown();
            });
        </script>
    </body>
</html>
