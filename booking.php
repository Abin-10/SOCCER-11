<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
require_once 'db.php';

// Handle booking deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $booking_id = intval($_POST['booking_id']);
    
    // Delete the booking
    $delete_query = "DELETE FROM turf_time_slots WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $booking_id);
    
    $response = array();
    if (mysqli_stmt_execute($stmt)) {
        $response['success'] = true;
        $response['message'] = 'Booking deleted successfully';
    } else {
        $response['success'] = false;
        $response['message'] = 'Error deleting booking';
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Get current date
$current_date = date('Y-m-d');

// Handle date filter
$selected_date = isset($_GET['date']) ? $_GET['date'] : $current_date;

// Handle turf filter
$turf_query = "SELECT turf_id as id, name as turf_name FROM turf";
$turf_result = mysqli_query($conn, $turf_query);
$selected_turf = isset($_GET['turf_id']) ? $_GET['turf_id'] : 'all';

// Fetch bookings with related information
$query = "SELECT ts.*, t.name as turf_name, fts.start_time, fts.end_time, u.name as booked_user_name 
          FROM turf_time_slots ts
          LEFT JOIN turf t ON ts.turf_id = t.turf_id
          LEFT JOIN fixed_time_slots fts ON ts.slot_id = fts.id
          LEFT JOIN users u ON ts.booked_by = u.id
          WHERE 1=1";

if ($selected_turf != 'all') {
    $query .= " AND ts.turf_id = " . intval($selected_turf);
}

if ($selected_date) {
    $query .= " AND ts.date = '" . mysqli_real_escape_string($conn, $selected_date) . "'";
}

$query .= " ORDER BY fts.start_time ASC";
$result = mysqli_query($conn, $query);

// Get admin's name from session
$admin_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SOCCER-11 Admin - Bookings</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Oswald:wght@400;700&display=swap" rel="stylesheet"> 

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f1c40f;
        }

        body {
            background-color: #f5f6fa;
        }

        .admin-content {
            padding: 30px;
        }

        h2.mb-4 {
            color: var(--primary-color);
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        h2.mb-4:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--secondary-color);
        }

        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .filter-section label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            border-color: var(--secondary-color);
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-1px);
        }

        .booking-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-top: 25px;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            color: var(--primary-color);
            font-weight: 600;
            padding: 15px;
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
            display: inline-block;
            text-align: center;
            min-width: 100px;
        }

        .status-available {
            background-color: var(--success-color);
            color: white;
        }

        .status-booked {
            background-color: var(--danger-color);
            color: white;
        }

        .status-reserved {
            background-color: var(--warning-color);
            color: black;
        }

        .btn-sm {
            padding: 5px 15px;
            border-radius: 6px;
            margin: 0 3px;
            font-size: 0.85em;
        }

        .btn-danger {
            background-color: var(--danger-color);
            border: none;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .admin-content {
                padding: 15px;
            }
            
            .filter-section .col-md-4 {
                margin-bottom: 15px;
            }
            
            .table-responsive {
                border-radius: 12px;
            }
        }

        .back-link {
            color: #2c3e50;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9em;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #3498db;
            text-decoration: none;
        }

        .back-link i {
            font-size: 0.85em;
        }

        .header {
            background: linear-gradient(135deg, #388E3C 0%, #2E7D32 100%);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            color: white;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        .back-btn {
            padding: 12px 25px;
            background: white;
            color: #388E3C;
            border-radius: 8px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 14px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            text-decoration: none;
            color: #2E7D32;
        }

        .filter-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .booking-table {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            color: #2c3e50;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: #388E3C;
            border: none;
            box-shadow: 0 4px 6px rgba(56, 142, 60, 0.2);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #2E7D32;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(56, 142, 60, 0.3);
        }

        .small-back-btn {
            padding: 5px 10px; /* Reduced padding */
            font-size: 12px; /* Font size remains the same */
            margin-left: auto; /* Push to the right */
            display: flex; /* Flex to align icon and text */
            align-items: center; /* Center icon vertically */
            max-width: 150px; /* Set a maximum width for the button */
            text-align: center; /* Center text within the button */
        }

        .header .d-flex {
            justify-content: space-between; /* Space between title and button */
            align-items: center; /* Center items vertically */
        }

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
            background: rgb(24, 137, 32);
            color: #fff;
        }
    </style>
</head>

<body>
    <!-- Nav Start -->
    <nav class="navbar navbar-expand-md bg-light navbar-light">
        <a href="#" class="navbar-brand">SOCCER-11 ADMIN</a>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-user-circle mr-2"></i> <?php echo htmlspecialchars($admin_name); ?>
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
                <a href="booking.php" class="admin-nav-link active"><i class="fas fa-calendar-alt mr-2"></i>Bookings</a>
                <a href="users.php" class="admin-nav-link"><i class="fas fa-users mr-2"></i>Users</a>
                <a href="turfs.php" class="admin-nav-link"><i class="fas fa-football-ball mr-2"></i>Turfs</a>
                <a href="admin_fixed_slots.php" class="admin-nav-link"><i class="fas fa-clock mr-2"></i>Manage Slots</a>
                <a href="settings.php" class="admin-nav-link"><i class="fas fa-cog mr-2"></i>Settings</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 admin-content">
                <div class="header">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1>Booking Management</h1>
                    </div>
                </div>
                
                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="GET" class="row align-items-end">
                        <div class="col-md-4">
                            <label for="date">Select Date:</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="<?php echo $selected_date; ?>" min="<?php echo $current_date; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="turf_id">Select Turf:</label>
                            <select class="form-control" id="turf_id" name="turf_id">
                                <option value="all">All Turfs</option>
                                <?php while($turf = mysqli_fetch_assoc($turf_result)): ?>
                                    <option value="<?php echo $turf['id']; ?>" 
                                            <?php echo ($selected_turf == $turf['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($turf['turf_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </form>
                </div>

                <!-- Bookings Table -->
                <div class="booking-table">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Turf</th>
                                    <th>Time Slot</th>
                                    <th>Status</th>
                                    <th>Booked By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['turf_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['start_time']) . ' - ' . htmlspecialchars($row['end_time']); ?></td>
                                        <td>
                                            <?php
                                            if ($row['is_available'] == 1) {
                                                echo '<span class="status-badge status-available">Available</span>';
                                            } elseif ($row['is_owner_reserved'] == 1) {
                                                echo '<span class="status-badge status-reserved">Reserved</span>';
                                            } else {
                                                echo '<span class="status-badge status-booked">Booked</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $row['booked_user_name'] ? htmlspecialchars($row['booked_user_name']) : '-'; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap Bundle -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Initialize Dropdown -->
    <script>
        $(document).ready(function() {
            $('.dropdown-toggle').dropdown();
        });
        
        function editBooking(id) {
            // Implement edit functionality
            alert('Edit booking ' + id);
        }

        function deleteBooking(id) {
            if (confirm('Are you sure you want to delete this booking?')) {
                $.ajax({
                    url: 'booking.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        booking_id: id
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Remove the row from the table
                            $(`button[onclick="deleteBooking(${id})"]`).closest('tr').remove();
                            alert(response.message);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Error occurred while deleting the booking');
                    }
                });
            }
        }

        // Auto-submit form when filters change
        document.getElementById('date').addEventListener('change', function() {
            this.form.submit();
        });
        
        document.getElementById('turf_id').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html> 