<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
        <title>SOCCER-11 User Dashboard</title>
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

            .turf-card {
                background: #fff;
                border-radius: 8px;
                overflow: hidden;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .turf-card img {
                width: 100%;
                height: 200px;
                object-fit: cover;
            }

            .turf-card-body {
                padding: 20px;
            }

            .booking-form {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                margin-bottom: 20px;
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
                    <a href="userdashboard.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'userdashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home mr-2"></i>Overview
                    </a>
                    <a href="book_turf.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'book_turf.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt mr-2"></i>Book Turf
                    </a>
                    <a href="my_bookings.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my_bookings.php' ? 'active' : ''; ?>">
                        <i class="fas fa-list mr-2"></i>My Bookings
                    </a>
                    <a href="contact.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope mr-2"></i>Contact
                    </a>
                </div>
                <!-- Main Content -->
                <div class="col-md-10 admin-content">
