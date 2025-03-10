<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "registration");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the user's name from the session
$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Review - SOCCER-11</title>
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
                <a href="notifications.php" class="admin-nav-link"><i class="fas fa-bell mr-2"></i>Notifications</a>
                <a href="contact.php" class="admin-nav-link active"><i class="fas fa-envelope mr-2"></i>Reviews</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 admin-content">
                <h2 class="mb-4">Write a Review</h2>
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                                ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>
                        <form action="process_review.php" method="POST" class="contact-form">
                            <div class="form-group">
                                <label for="name"><i class="fas fa-user mr-2"></i>Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user_name); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="turf"><i class="fas fa-futbol mr-2"></i>Select Turf</label>
                                <select class="form-control" id="turf" name="turf_id" required>
                                    <option value="">Choose a turf</option>
                                    <?php
                                    // Fetch all turfs with correct table and column names
                                    $turf_sql = "SELECT turf_id, name, location FROM turf";
                                    $turf_result = $conn->query($turf_sql);
                                    while($turf = $turf_result->fetch_assoc()) {
                                        echo '<option value="' . $turf['turf_id'] . '">' . htmlspecialchars($turf['name']) . ' - ' . htmlspecialchars($turf['location']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="rating"><i class="fas fa-star mr-2"></i>Rating</label>
                                <select class="form-control" id="rating" name="rating" required>
                                    <option value="">Select Rating</option>
                                    <option value="5">5 Stars - Excellent</option>
                                    <option value="4">4 Stars - Very Good</option>
                                    <option value="3">3 Stars - Good</option>
                                    <option value="2">2 Stars - Fair</option>
                                    <option value="1">1 Star - Poor</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="review"><i class="fas fa-comment mr-2"></i>Review</label>
                                <textarea class="form-control" id="review" name="review" rows="5" placeholder="Share your experience with us..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane mr-2"></i>Submit Review
                            </button>
                        </form>
                    </div>
                </div>

                <!-- New Reviews Section -->
                <div class="row mt-5">
                    <div class="col-12">
                        <h3 class="mb-4">Recent Reviews</h3>
                        <?php
                        // Corrected SQL query to match your table structure
                        $review_sql = "SELECT r.*, u.name as user_name, t.name as turf_name, t.location 
                                     FROM reviews r 
                                     JOIN users u ON r.user_id = u.id 
                                     JOIN turf t ON r.turf_id = t.turf_id 
                                     ORDER BY r.review_date DESC";
                        $review_result = $conn->query($review_sql);

                        if ($review_result && $review_result->num_rows > 0) {
                            while($review = $review_result->fetch_assoc()) {
                                ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-user-circle mr-2"></i>
                                                <?php echo htmlspecialchars($review['user_name']); ?>
                                            </h5>
                                            <div class="text-warning">
                                                <?php
                                                for($i = 0; $i < $review['rating']; $i++) {
                                                    echo '<i class="fas fa-star"></i>';
                                                }
                                                for($i = $review['rating']; $i < 5; $i++) {
                                                    echo '<i class="far fa-star"></i>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <h6 class="card-subtitle mb-2 text-muted">
                                            <i class="fas fa-futbol mr-2"></i>
                                            <?php echo htmlspecialchars($review['turf_name']) . ' - ' . htmlspecialchars($review['location']); ?>
                                        </h6>
                                        <p class="card-text"><?php echo htmlspecialchars($review['comments']); ?></p>
                                        <small class="text-muted">
                                            <i class="far fa-clock mr-1"></i>
                                            <?php echo date('F j, Y', strtotime($review['review_date'])); ?>
                                        </small>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<div class="alert alert-info">No reviews available yet.</div>';
                        }
                        ?>
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

<?php
$conn->close();
?> 