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

// Update user details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    if (!empty($name) && !empty($email) && !empty($phone)) {
        $update_sql = "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssi", $name, $email, $phone, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Settings updated successfully!";
            $_SESSION['user_name'] = $name; // Update session name
            header("Location: settings.php");
            exit();
        } else {
            $error_msg = "Error updating settings.";
        }
        $stmt->close();
    } else {
        $error_msg = "All fields are required!";
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
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- jQuery, Popper.js, and Bootstrap JS (Fix Dropdown Issue) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>

</body>
</html>
