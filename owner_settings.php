<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

// Database connection
$db_host = "localhost";
$db_user = "root";  // Replace with your actual database username
$db_pass = "";      // Replace with your actual database password
$db_name = "registration";

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_profile'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        
        $user_id = $_SESSION['user_id'];
        $sql = "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Profile updated successfully!";
            $_SESSION['user_name'] = $name;
        } else {
            $_SESSION['error'] = "Error updating profile!";
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password === $confirm_password) {
            $user_id = $_SESSION['user_id'];
            $sql = "SELECT password FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if (password_verify($current_password, $user['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Password changed successfully!";
                } else {
                    $_SESSION['error'] = "Error changing password!";
                }
            } else {
                $_SESSION['error'] = "Current password is incorrect!";
            }
        } else {
            $_SESSION['error'] = "New passwords do not match!";
        }
    }
}

// Get current user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - SOCCER-11</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Updated modern styling */
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f5f7fa;
        }

        .main-content {
            padding: 30px;
        }

        .header {
            background: linear-gradient(135deg, #388E3C 0%, #2E7D32 100%);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            color: white;
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        .header-buttons {
            display: flex;
            gap: 15px;
        }

        .back-btn, .logout-btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 14px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .back-btn {
            background: white;
            color: #388E3C;
        }

        .back-btn:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }

        .logout-btn {
            background: #ff4444;
            color: white;
        }

        .logout-btn:hover {
            background: #ff3333;
        }

        .settings-container {
            display: flex;
            gap: 40px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .settings-card {
            background: white;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            flex: 1;
            min-width: 300px;
            max-width: 500px;
        }

        .settings-card:hover {
            transform: translateY(-5px);
        }

        .settings-card h2 {
            color: #388E3C;
            font-size: 24px;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #E8F5E9;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #E8F5E9;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .form-group input:focus {
            border-color: #4CAF50;
            background-color: white;
            box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.1);
        }

        .submit-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
            color: white;
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.2);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert::before {
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
        }

        .alert-success {
            background: #E8F5E9;
            color: #2E7D32;
            border: none;
        }

        .alert-success::before {
            content: '\f00c';
        }

        .alert-error {
            background: #FFEBEE;
            color: #C62828;
            border: none;
        }

        .alert-error::before {
            content: '\f071';
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Include the same sidebar from owner.php -->
        <div class="sidebar">
            <!-- ... existing sidebar code ... -->
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Settings</h1>
                <div class="header-buttons">
                    <a href="owner.php" class="back-btn">Back</a>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="settings-container">
                <div class="settings-card">
                    <h2>Profile Settings</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                        </div>
                        <button type="submit" name="update_profile" class="submit-btn">Update Profile</button>
                    </form>
                </div>

                <div class="settings-card">
                    <h2>Change Password</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password" class="submit-btn">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>