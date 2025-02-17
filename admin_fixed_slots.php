<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $check_query = "SELECT * FROM fixed_time_slots WHERE start_time = ? AND end_time = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ss", $start_time, $end_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $insert_query = "INSERT INTO fixed_time_slots (start_time, end_time) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ss", $start_time, $end_time);
        if ($stmt->execute()) {
            $success_message = "Time slot added successfully!";
        } else {
            $error_message = "Error adding time slot.";
        }
    } else {
        $error_message = "This time slot already exists!";
    }
}

$slots = $conn->query("SELECT * FROM fixed_time_slots ORDER BY start_time ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Fixed Time Slots</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Manage Fixed Time Slots</h2>
            <a href="admin.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Admin
            </a>
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
                    <td><?php echo $row['start_time']; ?></td>
                    <td><?php echo $row['end_time']; ?></td>
                    <td>
                        <a href="delete_slot.php?id=<?php echo $row['id']; ?>" class="delete-btn">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>