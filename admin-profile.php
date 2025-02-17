<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "registration";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT name, email, phone FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - SOCCER-11</title>

    <!-- Bootstrap & FontAwesome -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Lato', sans-serif;
            background-color: #f8f9fa;
        }

        .admin-content {
            max-width: 500px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
        }

        .form-control {
            border: 1px solid #ced4da;
            padding: 8px;
            font-size: 14px;
            height: auto;
        }

        .btn {
            padding: 8px 15px;
            font-size: 14px;
            font-weight: 600;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px 20px;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-md bg-light navbar-light">
        <a href="admin.php" class="navbar-brand">SOCCER-11 ADMIN</a>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-toggle="dropdown">
                        <i class="fas fa-user-circle mr-2"></i><?php echo htmlspecialchars($user['name']); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item active" href="admin-profile.php"><i class="fas fa-user mr-2"></i>Profile</a>
                        <a class="dropdown-item" href="settings.php"><i class="fas fa-cog mr-2"></i>Settings</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <div class="admin-content">
            <h2 class="text-center">Admin Profile</h2>
            <hr>

            <form>
                <div class="form-group">
                    <label><i class="fas fa-user mr-2"></i>Name</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope mr-2"></i>Email</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-phone mr-2"></i>Phone</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>
                </div>

                <a href="settings.php" class="btn btn-primary btn-block">
                    <i class="fas fa-edit mr-2"></i>Edit Profile
                </a>
                <a href="admin.php" class="btn btn-secondary btn-block mt-2">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
