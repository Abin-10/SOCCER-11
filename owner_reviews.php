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

// Fetch turf details for the owner
$turf_query = "SELECT turf_id, name FROM turf WHERE owner_id = ?";
$stmt = $conn->prepare($turf_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$turf_result = $stmt->get_result();

// Get selected turf_id from URL parameter or default to first turf
$selected_turf_id = isset($_GET['turf_id']) ? intval($_GET['turf_id']) : null;
if (!$selected_turf_id && $turf_result->num_rows > 0) {
    $first_turf = $turf_result->fetch_assoc();
    $selected_turf_id = $first_turf['turf_id'];
    $turf_result->data_seek(0); // Reset result pointer
}

// Fetch reviews with user information for selected turf
$reviews_query = "SELECT r.*, u.name as username 
                 FROM reviews r 
                 JOIN users u ON r.user_id = u.id 
                 WHERE r.turf_id = ?
                 ORDER BY r.review_date DESC";
$stmt = $conn->prepare($reviews_query);
$stmt->bind_param("i", $selected_turf_id);
$stmt->execute();
$reviews_result = $stmt->get_result();

// Calculate average rating for selected turf
$avg_rating_query = "SELECT AVG(rating) as avg_rating FROM reviews WHERE turf_id = ?";
$stmt = $conn->prepare($avg_rating_query);
$stmt->bind_param("i", $selected_turf_id);
$stmt->execute();
$avg_rating = number_format($stmt->get_result()->fetch_assoc()['avg_rating'], 1);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews Management - SOCCER-11</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:400,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f1f8e9 0%, #e8f5e9 100%);
        }

        /* Sidebar Base */
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(180deg, #4CAF50 0%, #388E3C 100%);
            color: white;
            padding: 25px;
            position: fixed;
            height: 100vh;
            box-shadow: 4px 0 25px rgba(76,175,80,0.2);
            z-index: 100;
        }

        /* Logo Section */
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

        /* Menu Items */
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
            }
        }

        .reviews-container {
            background: rgba(255,255,255,0.95);
            padding: 30px;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.8);
            margin: 30px;
            max-width: 1200px;
            margin-left: 320px;
        }

        .rating-summary {
            text-align: center;
            margin-bottom: 30px;
            padding: 30px;
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            border-radius: 25px;
            color: white;
            box-shadow: 0 15px 35px rgba(76,175,80,0.25);
            position: relative;
            overflow: hidden;
        }

        .rating-summary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
            pointer-events: none;
        }

        .average-rating {
            font-size: 48px;
            font-weight: 800;
            margin: 15px 0;
            text-shadow: 0 3px 15px rgba(0,0,0,0.2);
            background: linear-gradient(to bottom, #ffffff, #e0e0e0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .review-card {
            background: white;
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(76,175,80,0.15);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            animation: slideIn 0.5s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .review-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #4CAF50, #2E7D32);
            border-radius: 4px;
        }

        .review-card:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 15px 35px rgba(76,175,80,0.2);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }

        .user-name {
            font-weight: 800;
            color: #1B5E20;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-name::before {
            content: '\f007';
            font-family: 'Font Awesome 5 Free';
            font-size: 0.9em;
            color: #4CAF50;
        }

        .review-date {
            color: #666;
            font-size: 0.9em;
            font-weight: 600;
            background: rgba(76,175,80,0.1);
            padding: 8px 15px;
            border-radius: 20px;
        }

        .star-rating {
            color: #FFD700;
            margin: 15px 0;
            font-size: 1.3em;
            display: flex;
            gap: 5px;
        }

        .review-comment {
            color: #444;
            line-height: 1.8;
            font-size: 1em;
            padding: 8px 0;
            font-weight: 500;
        }

        .no-reviews {
            text-align: center;
            padding: 60px;
            color: #666;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .no-reviews i {
            color: #4CAF50;
            margin-bottom: 25px;
            font-size: 4em;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* Add a media query for smaller screens */
        @media (max-width: 1024px) {
            .reviews-container {
                max-width: 500px;
            }
        }

        @media (max-width: 768px) {
            .reviews-container {
                max-width: calc(100% - 100px);
                margin-left: 90px;
            }
        }

        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-left: 280px;
        }

        .header h1 {
            font-size: 2em;
            color: #1B5E20;
            margin: 0;
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

        /* Dropdown Content */
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
            
            .header {
                margin-left: 80px;
                padding: 20px;
            }
        }

        .reviews-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 30px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            font-family: 'Roboto', sans-serif;
        }

        .reviews-table th {
            background: #4CAF50;
            color: white;
            padding: 20px 25px;
            text-align: left;
            font-weight: 500;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid rgba(0,0,0,0.05);
        }

        .reviews-table td {
            padding: 25px;
            border-bottom: 1px solid rgba(0,0,0,0.08);
            font-size: 0.95rem;
            line-height: 1.6;
            vertical-align: middle;
        }

        .reviews-table tr:last-child td {
            border-bottom: none;
        }

        .reviews-table tr:hover {
            background: rgba(76,175,80,0.03);
        }

        .reviews-table .user-name {
            font-weight: 500;
            color: #2E7D32;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
        }

        .star-rating-cell {
            color: #FFD700;
            font-size: 1.1rem;
            letter-spacing: 2px;
        }

        .reviews-table td:nth-child(3) {
            max-width: 400px;
            line-height: 1.8;
            color: #555;
        }

        .reviews-table td:last-child {
            color: #666;
            font-size: 0.9rem;
        }

        /* Add alternating row colors */
        .reviews-table tbody tr:nth-child(even) {
            background-color: rgba(76,175,80,0.02);
        }

        /* Update responsive breakpoints for the new width */
        @media (max-width: 1400px) {
            .reviews-container {
                max-width: 1000px;
            }
        }

        @media (max-width: 1200px) {
            .reviews-container {
                max-width: 800px;
            }
        }

        @media (max-width: 768px) {
            .reviews-container {
                max-width: calc(100% - 100px);
                margin-left: 90px;
            }
        }

        .turf-selector {
            margin-bottom: 30px;
        }

        .turf-selector select {
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            border: 1px solid rgba(76,175,80,0.2);
            background: white;
            font-size: 1rem;
            color: #2E7D32;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .turf-selector select:hover {
            border-color: #4CAF50;
            box-shadow: 0 6px 20px rgba(76,175,80,0.15);
        }

        .turf-selector select:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 6px 20px rgba(76,175,80,0.15);
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
                    <a href="owner_time_slots.php">
                        <i class="fas fa-clock"></i>
                        <span>Time Slots</span>
                    </a>
                </li>
                <li>
                    <a href="owner_reviews.php" class="active">
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
                <h1>Reviews Management</h1>
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

            <div class="reviews-container">
                <div class="turf-selector">
                    <form method="GET" action="">
                        <select name="turf_id" onchange="this.form.submit()">
                            <?php while ($turf = $turf_result->fetch_assoc()): ?>
                                <option value="<?php echo $turf['turf_id']; ?>" 
                                        <?php echo ($selected_turf_id == $turf['turf_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($turf['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </form>
                </div>

                <div class="rating-summary">
                    <h2>Overall Rating</h2>
                    <div class="average-rating"><?php echo $avg_rating; ?> / 5.0</div>
                    <div class="star-rating">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $avg_rating) {
                                echo '<i class="fas fa-star"></i>';
                            } elseif ($i - 0.5 <= $avg_rating) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                </div>

                <?php if ($reviews_result->num_rows > 0): ?>
                    <table class="reviews-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($review = $reviews_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span class="user-name"><?php echo htmlspecialchars($review['username']); ?></span>
                                    </td>
                                    <td class="star-rating-cell">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $review['rating']) {
                                                echo '<i class="fas fa-star"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($review['comments']); ?></td>
                                    <td><?php echo date('F j, Y', strtotime($review['review_date'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-reviews">
                        <i class="fas fa-comment-slash fa-3x"></i>
                        <p>No reviews yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add active class to current menu item
            const menuItems = document.querySelectorAll('.menu-items a');
            menuItems.forEach(item => {
                if (item.getAttribute('href') === 'owner_reviews.php') {
                    item.classList.add('active');
                }
            });

            // Add stagger effect to review cards
            const reviewCards = document.querySelectorAll('.review-card');
            reviewCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });

            // Add hover effect to star ratings
            const starRatings = document.querySelectorAll('.star-rating');
            starRatings.forEach(rating => {
                rating.addEventListener('mouseenter', () => {
                    const stars = rating.querySelectorAll('i');
                    stars.forEach((star, index) => {
                        setTimeout(() => {
                            star.style.transform = 'scale(1.2)';
                        }, index * 50);
                    });
                });

                rating.addEventListener('mouseleave', () => {
                    const stars = rating.querySelectorAll('i');
                    stars.forEach(star => {
                        star.style.transform = 'scale(1)';
                    });
                });
            });
        });
    </script>
</body>
</html> 