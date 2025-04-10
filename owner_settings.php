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

// Get current user data (move this section before form handling)
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $has_changes = false;
    $has_errors = false;

    // Handle profile update
    if (isset($_POST['update_profile'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        
        // Check if any values have changed
        if ($name === $user_data['name'] && $email === $user_data['email'] && $phone === $user_data['phone']) {
            // No changes to profile
        }
        // Basic email validation
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'][] = "Please enter a valid email address.";
            $has_errors = true;
        } 
        // Validate phone number
        elseif (!preg_match('/^[6-9]\d{9}$/', $phone)) {
            $_SESSION['error'][] = "Invalid phone number! Must be 10 digits long, start with 6, 7, 8, or 9, and contain no spaces or special characters.";
            $has_errors = true;
        } else {
            $user_id = $_SESSION['user_id'];
            $sql = "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['message'][] = "Profile updated successfully!";
                $_SESSION['user_name'] = $name;
                $has_changes = true;
            } else {
                $_SESSION['error'][] = "Error updating profile!";
                $has_errors = true;
            }
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate new password
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/', $new_password)) {
            $_SESSION['error'][] = "Password must be at least 8 characters and include uppercase, lowercase, and numbers";
            $has_errors = true;
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['error'][] = "New passwords do not match!";
            $has_errors = true;
        } else {
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
                    $_SESSION['message'][] = "Password changed successfully!";
                    $has_changes = true;
                } else {
                    $_SESSION['error'][] = "Error changing password!";
                    $has_errors = true;
                }
            } else {
                $_SESSION['error'][] = "Current password is incorrect!";
                $has_errors = true;
            }
        }
    }

    // If no changes were made to either form
    if (!$has_changes && !$has_errors) {
        $_SESSION['message'][] = "No changes were made.";
    }
}
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
        /* Base styles */
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }

        /* Dashboard layout */
        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Main Content adjustment */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            background: #f5f7fa;
            box-sizing: border-box;
            width: calc(100% - 280px);
        }

        /* New Sidebar Styles */
        .admin-sidebar {
            width: 230px;
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

        /* Responsive Design for Sidebar */
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
                width: calc(100% - 80px);
            }
        }

        /* Settings Cards */
        .settings-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding-right: 40px;
        }

        .settings-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .settings-card h2 {
            color: #1a1a1a;
            font-size: 20px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group input:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.1);
        }

        .submit-btn {
            background: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            width: 100%;
        }

        .submit-btn:hover {
            background: #388E3C;
        }

        /* Alert Styles */
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
            font-size: 14px;
        }

        .alert-success {
            background: #E8F5E9;
            color: #2E7D32;
            border: 1px solid #C8E6C9;
        }

        .alert-error {
            background: #FFEBEE;
            color: #C62828;
            border: 1px solid #FFCDD2;
        }

        /* Updated Header and Dropdown Styles */
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        }

        /* Add these animation styles to your existing CSS */
        .settings-card form {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease-out, opacity 0.3s ease-out;
            opacity: 0;
        }

        .settings-card form.show {
            max-height: 800px; /* Adjust this value based on your form's maximum height */
            opacity: 1;
        }

        .toggle-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .toggle-btn:after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        .toggle-btn:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }

        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            20% {
                transform: scale(25, 25);
                opacity: 0.3;
            }
            100% {
                opacity: 0;
                transform: scale(40, 40);
            }
        }

        .toggle-btn i {
            transition: transform 0.3s ease;
        }

        .toggle-btn.active i {
            transform: rotate(180deg);
        }

        .settings-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .settings-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="dashboard">
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
                    <a href="owner_reviews.php">
                        <i class="fas fa-star"></i>
                        <span>Reviews</span>
                    </a>
                </li>
                <li>
                    <a href="owner_settings.php" class="active">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Profile Settings</h1>
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

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success">
                    <?php 
                        foreach ($_SESSION['message'] as $message) {
                            echo $message . "<br>";
                        }
                        unset($_SESSION['message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                        foreach ($_SESSION['error'] as $error) {
                            echo $error . "<br>";
                        }
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="settings-container">
                <div class="settings-card">
                    <h2>Profile Settings</h2>
                    <button type="button" class="toggle-btn" onclick="toggleForm('profileForm')">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                    <form method="POST" action="" name="update_profile" id="profileForm" style="display: none;">
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
                        <button type="button" class="cancel-btn" onclick="toggleForm('profileForm')">Cancel</button>
                    </form>
                </div>

                <div class="settings-card">
                    <h2>Change Password</h2>
                    <button type="button" class="toggle-btn" onclick="toggleForm('passwordForm')">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                    <form method="POST" action="" id="passwordForm" style="display: none;">
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
                        <button type="button" class="cancel-btn" onclick="toggleForm('passwordForm')">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function toggleForm(formId) {
        const form = document.getElementById(formId);
        const button = form.previousElementSibling; // Get the toggle button
        const allForms = document.querySelectorAll('.settings-card form');
        const allButtons = document.querySelectorAll('.toggle-btn');
        
        // Close all other forms first
        allForms.forEach(otherForm => {
            if (otherForm !== form && otherForm.classList.contains('show')) {
                otherForm.classList.remove('show');
                otherForm.style.display = 'none';
                const otherButton = otherForm.previousElementSibling;
                otherButton.classList.remove('active');
            }
        });

        // Toggle the clicked form
        if (form.style.display === 'none') {
            form.style.display = 'block';
            setTimeout(() => {
                form.classList.add('show');
                button.classList.add('active');
            }, 10);

            // Add ripple effect
            const ripple = document.createElement('div');
            ripple.classList.add('ripple');
            button.appendChild(ripple);
            setTimeout(() => ripple.remove(), 1000);

            // Scroll into view smoothly
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            form.classList.remove('show');
            button.classList.remove('active');
            setTimeout(() => {
                form.style.display = 'none';
            }, 500); // Match this with the CSS transition duration
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Form elements
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const profileForm = document.getElementById('profileForm');
        const passwordForm = document.getElementById('passwordForm');

        // Validation functions
        function validateEmail(email) {
            // Basic email validation regex
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function validatePhone(phone) {
            const phoneRegex = /^[6-9]\d{9}$/;
            return phoneRegex.test(phone);
        }

        function validatePassword(password) {
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
            return passwordRegex.test(password);
        }

        // Create validation message elements
        const emailValidation = document.createElement('div');
        const phoneValidation = document.createElement('div');
        const passwordValidation = document.createElement('div');
        const confirmPasswordValidation = document.createElement('div');

        // Add validation elements after their respective inputs
        emailInput.parentNode.appendChild(emailValidation);
        phoneInput.parentNode.appendChild(phoneValidation);
        newPasswordInput.parentNode.appendChild(passwordValidation);
        confirmPasswordInput.parentNode.appendChild(confirmPasswordValidation);

        // Live validation event listeners
        emailInput.addEventListener('input', function() {
            const isValid = validateEmail(this.value);
            emailValidation.innerHTML = isValid ? 
                '<span style="color: #28a745;">Valid email address</span>' : 
                '<span style="color: #dc3545;">Must be a valid email address</span>';
            updateSubmitButton();
        });

        phoneInput.addEventListener('input', function() {
            const isValid = validatePhone(this.value);
            phoneValidation.innerHTML = isValid ? 
                '<span style="color: #28a745;">Valid phone number</span>' : 
                '<span style="color: #dc3545;">Must be 10 digits starting with 6-9</span>';
            updateSubmitButton();
        });

        newPasswordInput.addEventListener('input', function() {
            const isValid = validatePassword(this.value);
            passwordValidation.innerHTML = isValid ? 
                '<span style="color: #28a745;">Password meets requirements</span>' : 
                '<span style="color: #dc3545;">Password must be 8+ characters with uppercase, lowercase, and numbers</span>';
            validateConfirmPassword();
            updateSubmitButton();
        });

        confirmPasswordInput.addEventListener('input', validateConfirmPassword);

        function validateConfirmPassword() {
            const isMatch = newPasswordInput.value === confirmPasswordInput.value;
            confirmPasswordValidation.innerHTML = isMatch && confirmPasswordInput.value ? 
                '<span style="color: #28a745;">Passwords match</span>' : 
                '<span style="color: #dc3545;">Passwords do not match</span>';
            updateSubmitButton();
        }

        function updateSubmitButton() {
            // Profile form validation
            const profileSubmitBtn = profileForm.querySelector('button[name="update_profile"]');
            const isProfileValid = (!emailInput.value || validateEmail(emailInput.value)) && 
                                 (!phoneInput.value || validatePhone(phoneInput.value));
            profileSubmitBtn.disabled = !isProfileValid;
            profileSubmitBtn.style.opacity = isProfileValid ? '1' : '0.5';

            // Password form validation
            const passwordSubmitBtn = passwordForm.querySelector('button[name="change_password"]');
            const isPasswordValid = validatePassword(newPasswordInput.value) && 
                                  newPasswordInput.value === confirmPasswordInput.value;
            passwordSubmitBtn.disabled = !isPasswordValid;
            passwordSubmitBtn.style.opacity = isPasswordValid ? '1' : '0.5';
        }

        // Initial validation check
        updateSubmitButton();
    });
    </script>
</body>
</html>