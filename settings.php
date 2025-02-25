<?php
session_start();
if (!isset($_SESSION['user_id'])) {
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

// Fetch user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT name, email, phone FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$admin_name = $user['name']; // Fetch admin name

// Add this function at the top of the file after session_start()
function validatePassword($password) {
    $errors = [];
    
    // Check minimum length
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    // Check for at least one uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    // Check for at least one lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    // Check for at least one number
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    return $errors;
}

// Add this function to validate phone number
function validatePhoneNumber($phone) {
    $errors = [];
    
    // Check length
    if (strlen($phone) !== 10) {
        $errors[] = "Phone number must be exactly 10 digits long";
    }
    
    // Check starting digit
    if (!preg_match('/^[6789]/', $phone)) {
        $errors[] = "Phone number must start with 6, 7, 8, or 9";
    }
    
    // Check for non-digit characters
    if (!preg_match('/^\d{10}$/', $phone)) {
        $errors[] = "Phone number must contain only digits and no spaces or special characters";
    }
    
    return $errors;
}

// Add this function to validate email
function validateEmail($email) {
    $errors = [];
    
    // Check if it's a Gmail address
    if (strpos($email, '@gmail.com') !== false) {
        // Verify it's exactly @gmail.com at the end
        if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
            $errors[] = "Invalid Gmail format. Only @gmail.com is allowed";
        }
    }
    
    return $errors;
}

// Update user details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!empty($name) && !empty($email) && !empty($phone)) {
        // Validate email if it's a Gmail address
        $email_errors = validateEmail($email);
        if (!empty($email_errors)) {
            $error_msg = "Email validation failed:<br>" . implode("<br>", $email_errors);
        } else {
            // Validate phone number
            $phone_errors = validatePhoneNumber($phone);
            if (!empty($phone_errors)) {
                $error_msg = "Phone number requirements not met:<br>" . implode("<br>", $phone_errors);
            } else {
                // If password fields are filled, validate and update password
                if (!empty($new_password) || !empty($confirm_password)) {
                    // First, verify current password
                    $password_sql = "SELECT password FROM users WHERE id = ?";
                    $stmt = $conn->prepare($password_sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user_data = $result->fetch_assoc();
                    $stmt->close();

                    if (empty($current_password)) {
                        $error_msg = "Current password is required to change password!";
                    } elseif (!password_verify($current_password, $user_data['password'])) {
                        $error_msg = "Current password is incorrect!";
                    } elseif ($new_password !== $confirm_password) {
                        $error_msg = "New passwords do not match!";
                    } else {
                        // Validate new password
                        $password_errors = validatePassword($new_password);
                        if (!empty($password_errors)) {
                            $error_msg = "Password requirements not met:<br>" . implode("<br>", $password_errors);
                        } else {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $update_sql = "UPDATE users SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?";
                            $stmt = $conn->prepare($update_sql);
                            $stmt->bind_param("ssssi", $name, $email, $phone, $hashed_password, $user_id);
                        }
                    }
                } else {
                    // Update without password change
                    $update_sql = "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_sql);
                    $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
                }

                if (!isset($error_msg) && isset($stmt) && $stmt->execute()) {
                    $_SESSION['success_msg'] = "Settings updated successfully!";
                    $_SESSION['user_name'] = $name; // Update session name
                    header("Location: settings.php");
                    exit();
                } elseif (!isset($error_msg)) {
                    $error_msg = "Error updating settings.";
                }
                if (isset($stmt)) {
                    $stmt->close();
                }
            }
        }
    } else {
        $error_msg = "Name, email, and phone fields are required!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - SOCCER-11 Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Lato', sans-serif;
            background-color: #f8f9fa;
        }
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
            transition: all 0.3s;
        }
        .admin-nav-link:hover {
            background: #e9ecef;
            text-decoration: none;
            color: rgb(29, 113, 19);
        }
        .admin-nav-link.active {
            background: rgb(22, 110, 27);
            color: #fff;
        }
        .settings-card {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .btn-primary {
            background-color: rgb(18, 101, 16);
            border-color: rgb(22, 91, 8);
        }
        .btn-primary:hover {
            background-color: rgb(34, 120, 24);
            border-color: rgb(22, 98, 13);
        }
    </style>
</head>
<body>

<!-- Navbar Start -->
<nav class="navbar navbar-expand-md bg-light navbar-light">
    <a href="admin.php" class="navbar-brand">SOCCER-11 ADMIN</a>
    <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-toggle="dropdown">
                    <i class="fas fa-user-circle mr-2"></i><?php echo htmlspecialchars($admin_name); ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="admin-profile.php"><i class="fas fa-user mr-2"></i>Profile</a>
                    <a class="dropdown-item" href="settings.php"><i class="fas fa-cog mr-2"></i>Settings</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                </div>
            </li>
        </ul>
    </div>
</nav>
<!-- Navbar End -->

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 admin-sidebar">
            <h4 class="mb-4">Dashboard</h4>
            <a href="admin.php" class="admin-nav-link"><i class="fas fa-home mr-2"></i>Overview</a>
            <a href="booking.php" class="admin-nav-link"><i class="fas fa-calendar-check mr-2"></i>Bookings</a>
            <a href="users.php" class="admin-nav-link"><i class="fas fa-users mr-2"></i>Users</a>
            <a href="turfs.php" class="admin-nav-link"><i class="fas fa-futbol mr-2"></i>Turfs</a>
            <a href="admin_fixed_slots.php" class="admin-nav-link"><i class="fas fa-clock mr-2"></i>Manage Slots</a> <!-- New link for managing slots -->
            <a href="settings.php" class="admin-nav-link active"><i class="fas fa-cog mr-2"></i>Settings</a>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 admin-content">
            <h2>Account Settings</h2>

            <?php if (isset($_SESSION['success_msg'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
                </div>
            <?php endif; ?>

            <div class="settings-card">
                <form method="POST">
                    <div class="row">
                        <!-- Left Column - Personal Info -->
                        <div class="col-md-6">
                            <h4 class="mb-4">Personal Information</h4>
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                        </div>

                        <!-- Right Column - Password Change -->
                        <div class="col-md-6">
                            <h4 class="mb-4">Change Password</h4>
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" class="form-control">
                                <small class="form-text text-muted">
                                    Password must contain at least 8 characters, including uppercase, lowercase, 
                                    and numbers.
                                </small>
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control">
                            </div>
                        </div>
                    </div>

                    <?php if (isset($error_msg)): ?>
                        <div class="alert alert-danger mt-3">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <?php echo $error_msg; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- jQuery, Popper.js, and Bootstrap JS (Fix Dropdown Issue) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>

<script>
function validatePasswordField(password) {
    const errors = [];
    
    if (password.length < 8) {
        errors.push("Password must be at least 8 characters long");
    }
    if (!/[A-Z]/.test(password)) {
        errors.push("Password must contain at least one uppercase letter");
    }
    if (!/[a-z]/.test(password)) {
        errors.push("Password must contain at least one lowercase letter");
    }
    if (!/[0-9]/.test(password)) {
        errors.push("Password must contain at least one number");
    }
    
    return errors;
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const newPasswordInput = form.querySelector('input[name="new_password"]');
    const confirmPasswordInput = form.querySelector('input[name="confirm_password"]');
    const feedbackDiv = document.createElement('div');
    feedbackDiv.className = 'password-feedback mt-2';
    newPasswordInput.parentNode.appendChild(feedbackDiv);

    newPasswordInput.addEventListener('input', function() {
        const password = this.value;
        const errors = validatePasswordField(password);
        
        if (password.length > 0) {
            if (errors.length > 0) {
                feedbackDiv.innerHTML = `
                    <div class="alert alert-warning">
                        <strong>Password requirements:</strong><br>
                        ${errors.map(error => `<small>• ${error}</small>`).join('<br>')}
                    </div>`;
            } else {
                feedbackDiv.innerHTML = `
                    <div class="alert alert-success">
                        <small>Password meets all requirements!</small>
                    </div>`;
            }
        } else {
            feedbackDiv.innerHTML = '';
        }
    });

    form.addEventListener('submit', function(e) {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (newPassword || confirmPassword) {
            const errors = validatePasswordField(newPassword);
            
            if (errors.length > 0) {
                e.preventDefault();
                alert('Please fix the password requirements:\n\n' + errors.join('\n'));
                return;
            }

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
        }
    });
});
</script>

</body>
</html>
