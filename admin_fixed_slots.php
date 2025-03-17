<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new condition for edit operation
    if (isset($_POST['edit_id'])) {
        $edit_id = $_POST['edit_id'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        // Check if time is between 11 PM to 12 AM
        if ($start_time >= '23:00:00' || $end_time >= '23:00:00') {
            $error_message = "Cannot edit time slots between 11:00 PM to 12:00 AM. Please choose a different time.";
        } else {
            // Check for overlapping time slots (excluding the current slot being edited)
            $check_overlap_query = "SELECT * FROM fixed_time_slots 
                                  WHERE id != ? AND
                                  ((? BETWEEN start_time AND end_time) 
                                  OR (? BETWEEN start_time AND end_time)
                                  OR (start_time BETWEEN ? AND ?)
                                  OR (end_time BETWEEN ? AND ?))";
            $stmt = $conn->prepare($check_overlap_query);
            $stmt->bind_param("issssss", $edit_id, $start_time, $end_time, $start_time, $end_time, $start_time, $end_time);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error_message = "This time slot overlaps with an existing slot. Please choose a different time.";
            } else {
                $update_query = "UPDATE fixed_time_slots SET start_time = ?, end_time = ? WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("ssi", $start_time, $end_time, $edit_id);
                if ($stmt->execute()) {
                    $success_message = "Time slot updated successfully!";
                } else {
                    $error_message = "Error updating time slot.";
                }
            }
        }
    } else {
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        // Check if time is between 11 PM to 12 AM
        if ($start_time >= '23:00:00' || $end_time >= '23:00:00') {
            $error_message = "Cannot add time slots between 11:00 PM to 12:00 AM. Please choose a different time.";
        } else {
            // Check for overlapping time slots
            $check_overlap_query = "SELECT * FROM fixed_time_slots 
                                   WHERE (? BETWEEN start_time AND end_time) 
                                   OR (? BETWEEN start_time AND end_time)
                                   OR (start_time BETWEEN ? AND ?)
                                   OR (end_time BETWEEN ? AND ?)";
            $stmt = $conn->prepare($check_overlap_query);
            $stmt->bind_param("ssssss", $start_time, $end_time, $start_time, $end_time, $start_time, $end_time);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error_message = "This time slot overlaps with an existing slot. Please choose a different time.";
            } else {
                $insert_query = "INSERT INTO fixed_time_slots (start_time, end_time) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("ss", $start_time, $end_time);
                if ($stmt->execute()) {
                    $success_message = "Time slot added successfully!";
                } else {
                    $error_message = "Error adding time slot.";
                }
            }
        }
    }
}

$slots = $conn->query("SELECT * FROM fixed_time_slots ORDER BY start_time ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SOCCER-11 Admin Dashboard</title>
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
            --primary-color: #4a90e2;
            --secondary-color: #f8f9fa;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --text-color: #333;
            --border-radius: 8px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .back-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background-color: #357abd;
            transform: translateX(-3px);
        }

        h2 {
            color: var(--text-color);
            margin: 0;
            font-size: 24px;
        }

        form {
            background: var(--secondary-color);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
        }

        input[type="time"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        input[type="time"]:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        button {
            background-color: var(--success-color);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: all 0.3s ease;
        }

        button:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        th {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: left;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .delete-btn {
            background-color: var(--danger-color);
            color: white;
            padding: 8px 16px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .delete-btn:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 10px;
            }
            
            th, td {
                padding: 10px;
            }
        }

        /* Add new sidebar styles */
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
                <a href="turfs.php" class="admin-nav-link"><i class="fas fa-football-ball mr-2"></i>Turfs</a>
                <a href="admin_fixed_slots.php" class="admin-nav-link active"><i class="fas fa-clock mr-2"></i>Manage Slots</a>
                <a href="settings.php" class="admin-nav-link"><i class="fas fa-cog mr-2"></i>Settings</a>
            </div>

            <!-- Main content -->
            <div class="col-md-10">
                <div class="container">
                    <div class="header">
                        <h2>Manage Fixed Time Slots</h2>
                    </div>

                    <?php if (isset($success_message)) { echo "<div class='success'>$success_message</div>"; } ?>
                    <?php if (isset($error_message)) { echo "<div class='error'>$error_message</div>"; } ?>

                    <form action="" method="post">
                        <div class="form-group">
                            <label for="start_time">Start Time:</label>
                            <input type="time" name="start_time" id="start_time" required>
                        </div>

                        <div class="form-group">
                            <label for="end_time">End Time:</label>
                            <input type="time" name="end_time" id="end_time" required>
                        </div>

                        <button type="submit">Add Time Slot</button>
                    </form>

                    <h3>Existing Time Slots</h3>
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Action</th>
                        </tr>
                        <?php while ($row = $slots->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo date("h:i A", strtotime($row['start_time'])); ?></td>
                                <td><?php echo date("h:i A", strtotime($row['end_time'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-primary btn-sm edit-btn mr-2" 
                                                data-id="<?php echo $row['id']; ?>"
                                                data-start="<?php echo $row['start_time']; ?>"
                                                data-end="<?php echo $row['end_time']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="delete_slot.php?id=<?php echo $row['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this time slot?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.edit-btn').click(function() {
                var id = $(this).data('id');
                var start = $(this).data('start');
                var end = $(this).data('end');
                
                // Update form for editing
                $('form').append('<input type="hidden" name="edit_id" value="' + id + '">');
                $('#start_time').val(start);
                $('#end_time').val(end);
                $('button[type="submit"]').text('Update Time Slot');
                
                // Scroll to form
                $('html, body').animate({
                    scrollTop: $("form").offset().top
                }, 500);
            });
        });
    </script>
</body>
</html>