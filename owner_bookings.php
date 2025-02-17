<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
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

// Fetch booking details
$sql = "SELECT 
            tts.id, 
            tts.turf_id, 
            fts.start_time, 
            fts.end_time, 
            tts.date, 
            tts.is_available, 
            tts.is_owner_reserved 
        FROM turf_time_slots tts
        JOIN fixed_time_slots fts ON tts.slot_id = fts.id  -- Ensure 'slot_id' exists in 'turf_time_slots'
        ORDER BY tts.date DESC, fts.start_time";


$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - SOCCER-11</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #4CAF50; color: white; }
        .status-available { color: green; font-weight: bold; }
        .status-reserved { color: red; font-weight: bold; }
    </style>
</head>
<body>

    <h2>Booking Details</h2>

    <table>
        <thead>
            <tr>
                <th>Turf ID</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['turf_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['start_time']); ?></td>
                    <td><?php echo htmlspecialchars($row['end_time']); ?></td>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td class="<?php echo $row['is_available'] ? 'status-available' : 'status-reserved'; ?>">
                        <?php echo $row['is_available'] ? 'Available' : 'Reserved'; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>

<?php
$conn->close();
?>
