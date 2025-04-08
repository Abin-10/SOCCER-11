<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "registration";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $turf_name = $_POST['turf_name'];
    $location = $_POST['location'];
    $morning_rate = $_POST['morning_rate'];
    $afternoon_rate = $_POST['afternoon_rate'];
    $evening_rate = $_POST['evening_rate'];
    $owner_id = $_POST['owner_id'];
    $error = false;
    $errors = array();

    // Check if turf name already exists
    $check_name_sql = "SELECT turf_id FROM turf WHERE name = ?";
    $stmt = $conn->prepare($check_name_sql);
    $stmt->bind_param("s", $turf_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error = true;
        $errors['turf_name'] = 'Turf name already exists!';
    }
    $stmt->close();

    // Add validation for positive rates
    if ($morning_rate <= 0 || $afternoon_rate <= 0 || $evening_rate <= 0) {
        $error = true;
        $errors['rate'] = 'All rates must be greater than 0!';
    }

    if (!$error) {
        $sql_insert = "INSERT INTO turf (name, location, morning_rate, afternoon_rate, evening_rate, owner_id) 
                       VALUES ('$turf_name', '$location', '$morning_rate', '$afternoon_rate', '$evening_rate', '$owner_id')";
        
        if ($conn->query($sql_insert) === TRUE) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const successAlert = document.createElement('div');
                        successAlert.className = 'success-alert';
                        successAlert.innerHTML = '<i class=\"fas fa-check-circle\"></i> Turf added successfully!';
                        document.body.appendChild(successAlert);
                        
                        setTimeout(() => {
                            successAlert.classList.add('show');
                        }, 100);
                        
                        setTimeout(() => {
                            successAlert.classList.remove('show');
                            setTimeout(() => {
                                window.location.href='turfs.php';
                            }, 500);
                        }, 2000);
                    });
                </script>";
        } else {
            $errors['general'] = "Error: " . $conn->error;
        }
    }
}

$sql = "SELECT turf.turf_id, turf.name AS turf_name, turf.location, 
               turf.morning_rate, turf.afternoon_rate, turf.evening_rate,
               users.name AS owner_name, users.email AS owner_email, users.phone AS owner_phone
        FROM turf
        INNER JOIN users ON turf.owner_id = users.id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turf Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color:rgb(54, 122, 25);
            --secondary-color:rgb(51, 105, 24);
            --background-color: #f5f6fa;
            --text-color: #2c3e50;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: white;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            border-radius: 10px;
        }

        h2 {
            text-align: center;
            color: var(--text-color);
            margin: 0;
        }

        .form-container {
            max-width: 500px;
            margin: 0 auto 30px;
            padding: 25px;
            background: white;
            box-shadow: var(--shadow);
            border-radius: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        button {
            width: 100%;
            padding: 12px;
            background: var(--primary-color);
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        button:hover {
            background: var(--secondary-color);
        }

        .table-container {
            overflow-x: auto;
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .empty-message {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .form-container {
                padding: 15px;
            }

            th, td {
                padding: 10px;
                font-size: 14px;
            }
        }

        /* Add sidebar styles */
        .admin-sidebar {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 20px;
            border-right: 1px solid #dee2e6;
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
            background: var(--primary-color);
            color: #fff;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .success-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateX(150%);
            transition: transform 0.3s ease-in-out;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .success-alert.show {
            transform: translateX(0);
        }

        .success-alert i {
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <!-- Add Nav Start -->
    <nav class="navbar navbar-expand-md bg-light navbar-light">
        <a href="#" class="navbar-brand">SOCCER-11 ADMIN</a>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-user-circle mr-2"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="adminDropdown">
                        <a class="dropdown-item" href="admin-profile.php"><i class="fas fa-user mr-2"></i> Profile</a>
                        <a class="dropdown-item" href="settings.php"><i class="fas fa-cog mr-2"></i> Settings</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
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
                <a href="admin.php" class="admin-nav-link"><i class="fas fa-home mr-2"></i>Overview</a>
                <a href="booking.php" class="admin-nav-link"><i class="fas fa-calendar-alt mr-2"></i>Bookings</a>
                <a href="users.php" class="admin-nav-link"><i class="fas fa-users mr-2"></i>Users</a>
                <a href="turfs.php" class="admin-nav-link active"><i class="fas fa-football-ball mr-2"></i>Turfs</a>
                <a href="admin_fixed_slots.php" class="admin-nav-link"><i class="fas fa-clock mr-2"></i>Manage Slots</a>
                <a href="settings.php" class="admin-nav-link"><i class="fas fa-cog mr-2"></i>Settings</a>
            </div>

            <!-- Main content -->
            <div class="col-md-10">
                <div class="container">
                    <div class="header">
                        <h2>Turf Management</h2>
                    </div>

                    <div class="form-container">
                        <h3>Add New Turf</h3>
                        <form method="POST">
                            <div class="form-group">
                                <label>Turf Name:</label>
                                <input type="text" name="turf_name" required placeholder="Enter turf name" 
                                       value="<?php echo isset($_POST['turf_name']) ? htmlspecialchars($_POST['turf_name']) : ''; ?>">
                                <?php if (isset($errors['turf_name'])) echo "<div class='error-message'>{$errors['turf_name']}</div>"; ?>
                            </div>

                            <div class="form-group">
                                <label>Location:</label>
                                <input type="text" name="location" required placeholder="Enter location"
                                       value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
                                <?php if (isset($errors['location'])) echo "<div class='error-message'>{$errors['location']}</div>"; ?>
                            </div>

                            <div class="form-group">
                                <label>Morning Rate (6:01 AM - 10:00 AM):</label>
                                <input type="number" name="morning_rate" step="0.01" required placeholder="Enter morning rate"
                                       value="<?php echo isset($_POST['morning_rate']) ? htmlspecialchars($_POST['morning_rate']) : ''; ?>">
                                <?php if (isset($errors['morning_rate'])) echo "<div class='error-message'>{$errors['morning_rate']}</div>"; ?>
                            </div>

                            <div class="form-group">
                                <label>Afternoon Rate (10:01 AM - 4:00 PM):</label>
                                <input type="number" name="afternoon_rate" step="0.01" required placeholder="Enter afternoon rate"
                                       value="<?php echo isset($_POST['afternoon_rate']) ? htmlspecialchars($_POST['afternoon_rate']) : ''; ?>">
                                <?php if (isset($errors['afternoon_rate'])) echo "<div class='error-message'>{$errors['afternoon_rate']}</div>"; ?>
                            </div>

                            <div class="form-group">
                                <label>Evening Rate (4:01 PM - 11:00 PM):</label>
                                <input type="number" name="evening_rate" step="0.01" required placeholder="Enter evening rate"
                                       value="<?php echo isset($_POST['evening_rate']) ? htmlspecialchars($_POST['evening_rate']) : ''; ?>">
                                <?php if (isset($errors['evening_rate'])) echo "<div class='error-message'>{$errors['evening_rate']}</div>"; ?>
                            </div>

                            <div class="form-group">
                                <label>Owner:</label>
                                <select name="owner_id" required>
                                    <option value="">Select Owner</option>
                                    <?php
                                    $owner_query = "SELECT id, name FROM users WHERE role='owner'";
                                    $owners = $conn->query($owner_query);
                                    while ($owner = $owners->fetch_assoc()) {
                                        $selected = (isset($_POST['owner_id']) && $_POST['owner_id'] == $owner['id']) ? 'selected' : '';
                                        echo "<option value='" . $owner['id'] . "' $selected>" . $owner['name'] . "</option>";
                                    }
                                    ?>
                                </select>
                                <?php if (isset($errors['owner_id'])) echo "<div class='error-message'>{$errors['owner_id']}</div>"; ?>
                            </div>

                            <?php if (isset($errors['general'])) echo "<div class='error-message mb-3'>{$errors['general']}</div>"; ?>

                            <button type="submit">Add Turf</button>
                        </form>
                    </div>

                    <div class="table-container">
                        <table>
                            <tr>
                                <th>Turf ID</th>
                                <th>Turf Name</th>
                                <th>Location</th>
                                <th>Morning Rate (6:01-10:00)</th>
                                <th>Afternoon Rate (10:01-16:00)</th>
                                <th>Evening Rate (16:01-23:00)</th>
                                <th>Owner Name</th>
                                <th>Owner Email</th>
                                <th>Owner Phone</th>
                            </tr>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                            <td>" . $row["turf_id"] . "</td>
                                            <td>" . $row["turf_name"] . "</td>
                                            <td>" . $row["location"] . "</td>
                                            <td>₹" . $row["morning_rate"] . "</td>
                                            <td>₹" . $row["afternoon_rate"] . "</td>
                                            <td>₹" . $row["evening_rate"] . "</td>
                                            <td>" . $row["owner_name"] . "</td>
                                            <td>" . $row["owner_email"] . "</td>
                                            <td>" . $row["owner_phone"] . "</td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='empty-message'>No turfs found!</td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>