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
    <title>Contact Us - SOCCER-11</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Oswald:wght@400;700&display=swap" rel="stylesheet"> 
    
    <!-- Bootstrap and FontAwesome CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    
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
        
        .contact-form {
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .form-group label {
            font-weight: 600;
            color: #333;
        }
        
        .btn-primary {
            background: rgb(24, 137, 32);
            border-color: rgb(24, 137, 32);
        }
        
        .btn-primary:hover {
            background: rgb(20, 115, 27);
            border-color: rgb(20, 115, 27);
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
                <a href="contact.php" class="admin-nav-link active"><i class="fas fa-envelope mr-2"></i>Contact</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 admin-content">
                <h2 class="mb-4">Contact Us</h2>
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <form action="process_contact.php" method="POST" class="contact-form">
                            <div class="form-group">
                                <label for="name"><i class="fas fa-user mr-2"></i>Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user_name); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope mr-2"></i>Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="message"><i class="fas fa-comment mr-2"></i>Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane mr-2"></i>Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.dropdown-toggle').dropdown();
        });
    </script>
</body>
</html> 