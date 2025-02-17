<?php
session_start();

try {
    $conn = new mysqli('localhost:3306', 'root', '', 'registration'); // Using existing 'registration' database
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Add this line to set the user_name in session
$_SESSION['user_name'] = $user['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($phone)) {
        $error_message = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format!";
    } else {
        $update_sql = "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssi", $name, $email, $phone, $user_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Profile updated successfully!";
            // Refresh user data after update
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $update_stmt->close(); // Close the update statement
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
    }
}

// 4. Add proper cleanup at the end of the file, before HTML
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - SOCCER-11</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #16a34a;
            --secondary-color: #15803d;
            --accent-color: #dcfce7;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --success-color: #059669;
            --danger-color: #dc2626;
            --gradient-start: #22c55e;
            --gradient-end: #15803d;
            --background: #f8fafc;
        }

        body {
            background: var(--background);
            color: var(--text-primary);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 0% 0%, rgba(34, 197, 94, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(21, 128, 61, 0.1) 0%, transparent 50%);
            z-index: -1;
        }

        .profile-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            padding: 70px 50px;
            border-radius: 30px;
            margin-bottom: 40px;
            box-shadow: 0 20px 40px rgba(21, 128, 61, 0.15);
            position: relative;
            overflow: hidden;
            color: white;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: linear-gradient(45deg, 
                rgba(255,255,255,0) 0%,
                rgba(255,255,255,0.1) 50%,
                rgba(255,255,255,0) 100%);
            transform: rotate(45deg);
            animation: shine 8s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        .profile-header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
        }

        .profile-content {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 40px;
            padding: 0;
        }

        .profile-form, .bookings-section {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .profile-form:hover, .bookings-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .form-group {
            margin-bottom: 30px;
            position: relative;
        }

        .form-group label {
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 12px;
            display: block;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 16px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px var(--accent-color);
            background-color: white;
        }

        .form-control:focus + label {
            color: var(--primary-color);
        }

        .btn {
            padding: 16px 32px;
            border-radius: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 1rem;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            border: none;
            color: white;
            box-shadow: 0 8px 15px rgba(21, 128, 61, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 20px rgba(21, 128, 61, 0.3);
        }

        .btn-primary::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, 
                rgba(255,255,255,0) 0%,
                rgba(255,255,255,0.1) 50%,
                rgba(255,255,255,0) 100%);
            transform: rotate(45deg);
            transition: all 0.3s ease;
            opacity: 0;
        }

        .btn-primary:hover::after {
            opacity: 1;
            animation: btnShine 1.5s ease-out;
        }

        @keyframes btnShine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        .booking-card {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .booking-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .booking-status {
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 0.875rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .status-confirmed {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
        }

        .status-pending {
            background: linear-gradient(135deg, #eab308, #ca8a04);
            color: white;
        }

        .alert {
            border-radius: 20px;
            padding: 20px 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .profile-header {
                padding: 40px 25px;
            }
            
            .profile-header h1 {
                font-size: 2.25rem;
            }

            .profile-form, .bookings-section {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <h1 class="display-4 mb-2">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                <p class="lead mb-0">Manage your profile and view your bookings</p>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="profile-content">
                <div class="profile-form">
                    <h3 class="mb-4">Personal Information</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name"><i class="fas fa-user mr-2"></i>Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope mr-2"></i>Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone mr-2"></i>Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>

                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary mr-3">
                                <i class="fas fa-save mr-2"></i>Update Profile
                            </button>
                            <a href="userdashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>Back
                            </a>
                        </div>
                    </form>
                </div>

                <div class="bookings-section">
                    <h3 class="mb-4">Recent Bookings</h3>
                    <?php
                    $bookings_sql = "SELECT * FROM bookings WHERE user_id = ? ORDER BY booking_date DESC LIMIT 5";
                    $bookings_stmt = $conn->prepare($bookings_sql);
                    $bookings_stmt->bind_param("i", $user_id);
                    $bookings_stmt->execute();
                    $bookings_result = $bookings_stmt->get_result();

                    while ($booking = $bookings_result->fetch_assoc()):
                    ?>
                        <div class="booking-card">
                            <div class="booking-header">
                                <span class="booking-id">#<?php echo htmlspecialchars($booking['booking_id']); ?></span>
                                <span class="booking-status <?php echo strtolower($booking['status']) === 'confirmed' ? 'status-confirmed' : 'status-pending'; ?>">
                                    <?php echo htmlspecialchars($booking['status']); ?>
                                </span>
                            </div>
                            <div class="booking-details">
                                <div class="detail-item">
                                    <i class="far fa-calendar"></i>
                                    <?php echo htmlspecialchars($booking['booking_date']); ?>
                                </div>
                                <div class="detail-item">
                                    <i class="far fa-clock"></i>
                                    <?php echo htmlspecialchars($booking['time_slot']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>