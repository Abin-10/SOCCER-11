<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
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

// Function to generate formatted booking ID
function generateFormattedBookingId($original_id, $created_at) {
    $date = new DateTime($created_at);
    $year = $date->format('Y');
    $month = $date->format('m');
    $padded_id = str_pad($original_id, 4, '0', STR_PAD_LEFT);
    return "BK-{$year}{$month}-{$padded_id}";
}

// Add customer filter to WHERE clause if provided
$customer_filter = isset($_GET['customer_filter']) ? $_GET['customer_filter'] : '';
$where_clause = "WHERE tts.booking_status IS NOT NULL AND t.owner_id = ?";
if ($customer_filter) {
    $where_clause .= " AND u.name LIKE ?";
}

// Fetch bookings data
$sql = "SELECT 
    tts.id as booking_id,
    t.turf_id,
    t.name as turf_name,
    t.location,
    t.morning_rate,
    t.afternoon_rate,
    t.evening_rate,
    u.name as customer_name,
    u.email as customer_email,
    u.phone as customer_phone,
    fts.start_time,
    fts.end_time,
    tts.date,
    tts.booking_status,
    tts.created_at,
    tts.is_owner_reserved
FROM turf_time_slots tts
JOIN turf t ON tts.turf_id = t.turf_id
JOIN fixed_time_slots fts ON tts.slot_id = fts.id
LEFT JOIN users u ON tts.booked_by = u.id
$where_clause
ORDER BY t.turf_id ASC, tts.date DESC, fts.start_time ASC";

$stmt = $conn->prepare($sql);
if ($customer_filter) {
    $filter_param = "%$customer_filter%";
    $stmt->bind_param("is", $_SESSION['user_id'], $filter_param);
} else {
    $stmt->bind_param("i", $_SESSION['user_id']);
}
$stmt->execute();
$result = $stmt->get_result();

// Output HTML header with styles
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Turf Bookings Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background-color: #f0f4f8;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #20a83f;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2em;
        }
        .report-date {
            text-align: center;
            color: #4a5568;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #e2e8f0;
        }
        th {
            background-color: #20a83f;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        tr:hover {
            background-color: #ebf4ff;
        }
        .turf-header {
            background-color: #e2e8f0;
            padding: 12px;
            margin: 20px 0 10px 0;
            font-weight: bold;
            border-radius: 5px;
            color: #2d3748;
        }
        .summary {
            text-align: right;
            font-weight: bold;
            padding: 12px;
            background-color: #20a83f;
            color: white;
            margin-top: 20px;
            border-radius: 5px;
        }
        .button {
            padding: 10px 20px;
            background-color: #20a83f;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 5px;
            text-decoration: none;
            display: inline-block;
        }
        .button:hover {
            background-color: #198632;
        }
        .buttons-container {
            margin-bottom: 20px;
            text-align: left;
        }
        @media print {
            body {
                background-color: white;
            }
            .container {
                box-shadow: none;
            }
            .no-print {
                display: none;
            }
        }
        .filter-container {
            margin-bottom: 20px;
        }
        .filter-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .filter-input {
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            font-size: 14px;
            min-width: 250px;
        }
        .filter-input:focus {
            outline: none;
            border-color: #20a83f;
            box-shadow: 0 0 5px rgba(32, 168, 63, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="buttons-container no-print">
            <a href="owner_bookings.php" class="button">← Back to Dashboard</a>
            <button class="button" onclick="window.print();">Print Report</button>
        </div>
        
        <!-- Add customer filter form -->
        <div class="filter-container no-print">
            <form method="GET" class="filter-form">
                <input type="text" 
                       name="customer_filter" 
                       placeholder="Filter by customer name"
                       value="<?php echo isset($_GET['customer_filter']) ? htmlspecialchars($_GET['customer_filter']) : ''; ?>"
                       class="filter-input">
                <button type="submit" class="button">Filter</button>
                <?php if(isset($_GET['customer_filter'])): ?>
                    <a href="export_bookings.php" class="button">Clear Filter</a>
                <?php endif; ?>
            </form>
        </div>
        
        <h1>Turf Bookings Report</h1>
        <div class="report-date">Generated on: <?php echo date('d/m/Y'); ?></div>
        
        <?php
        $current_turf = null;
        $turf_total = 0;
        $turf_bookings = 0;
        $grand_total = 0;
        $total_bookings = 0;

        while ($row = $result->fetch_assoc()) {
            if ($current_turf !== $row['turf_id']) {
                if ($current_turf !== null) {
                    // Print turf summary
                    echo "<div class='summary'>Turf Summary - Bookings: $turf_bookings | Revenue: ₹" . number_format($turf_total, 2) . "</div>";
                    echo "</table>";
                }
                
                echo "<div class='turf-header'>{$row['turf_name']} - {$row['location']}</div>";
                echo "<table>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Duration</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>";
                
                $turf_total = 0;
                $turf_bookings = 0;
            }

            // Calculate duration and amount
            $start = strtotime($row['start_time']);
            $end = strtotime($row['end_time']);
            $duration = ($end - $start) / 3600;
            
            // Determine rate based on time
            $start_hour = date('H', $start);
            if ($start_hour >= 6 && $start_hour < 10) {
                $rate = $row['morning_rate'];
            } elseif ($start_hour >= 10 && $start_hour < 16) {
                $rate = $row['afternoon_rate'];
            } else {
                $rate = $row['evening_rate'];
            }
            
            $total_amount = $row['is_owner_reserved'] ? 'N/A' : ($duration * $rate);

            // Format data
            $booking_id = generateFormattedBookingId($row['booking_id'], $row['created_at']);
            $customer = $row['customer_name'] ?: 'N/A';
            $contact = $row['customer_phone'] ?: 'N/A';
            $date = date('d/m/Y', strtotime($row['date']));
            $time = date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time']));

            echo "<tr>
                <td>$booking_id</td>
                <td>$customer</td>
                <td>$contact</td>
                <td>$date</td>
                <td>$time</td>
                <td>" . number_format($duration, 1) . "h</td>
                <td>" . (is_numeric($total_amount) ? '₹' . number_format($total_amount, 2) : 'N/A') . "</td>
                <td>" . ucfirst($row['booking_status']) . "</td>
            </tr>";

            if (!$row['is_owner_reserved'] && is_numeric($total_amount)) {
                $turf_total += $total_amount;
                $grand_total += $total_amount;
                $turf_bookings++;
                $total_bookings++;
            }
            
            $current_turf = $row['turf_id'];
        }

        // Print final turf summary
        if ($current_turf !== null) {
            echo "<div class='summary'>Turf Summary - Bookings: $turf_bookings | Revenue: ₹" . number_format($turf_total, 2) . "</div>";
            echo "</table>";
        }
        ?>

        <div class="summary" style="margin-top: 30px;">
            GRAND TOTAL - Total Bookings: <?php echo $total_bookings; ?> | Total Revenue: ₹<?php echo number_format($grand_total, 2); ?>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>