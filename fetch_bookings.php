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
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Build query based on filters
$where_conditions = [];
$params = [];
$types = '';

if (!empty($_POST['dateRange'])) {
    $dates = explode(" to ", $_POST['dateRange']);
    if (count($dates) == 2) {
        $where_conditions[] = "tts.date BETWEEN ? AND ?";
        $params[] = $dates[0];
        $params[] = $dates[1];
        $types .= 'ss';
    }
}

if (!empty($_POST['turf'])) {
    $where_conditions[] = "t.turf_name = ?";
    $params[] = $_POST['turf'];
    $types .= 's';
}

if (!empty($_POST['status'])) {
    $where_conditions[] = "b.status = ?";
    $params[] = $_POST['status'];
    $types .= 's';
}

if (!empty($_POST['search'])) {
    $search = '%' . $_POST['search'] . '%';
    $where_conditions[] = "(u.name LIKE ? OR b.booking_id LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $types .= 'ss';
}

$sql = "SELECT 
            b.booking_id,
            u.name as customer_name,
            t.turf_name,
            tts.date,
            fts.start_time,
            fts.end_time,
            b.amount,
            b.status
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN turf_time_slots tts ON b.slot_id = tts.id
        JOIN turfs t ON tts.turf_id = t.id
        JOIN fixed_time_slots fts ON tts.slot_id = fts.id";

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " ORDER BY tts.date DESC, fts.start_time LIMIT 10";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $status_class = '';
    switch(strtolower($row['status'])) {
        case 'confirmed':
            $status_class = 'success';
            break;
        case 'pending':
            $status_class = 'warning';
            break;
        case 'cancelled':
            $status_class = 'danger';
            break;
    }
    
    echo "<tr>
            <td>{$row['booking_id']}</td>
            <td>{$row['customer_name']}</td>
            <td>{$row['turf_name']}</td>
            <td>{$row['date']}</td>
            <td>{$row['start_time']} - {$row['end_time']}</td>
            <td>₹{$row['amount']}</td>
            <td><span class='badge badge-{$status_class}'>{$row['status']}</span></td>
            <td>
                <button class='btn btn-sm btn-info' onclick='viewBooking(\"{$row['booking_id']}\")'>
                    <i class='fas fa-eye'></i>
                </button>
                <button class='btn btn-sm btn-warning' onclick='editBooking(\"{$row['booking_id']}\")'>
                    <i class='fas fa-edit'></i>
                </button>
                <button class='btn btn-sm btn-danger' onclick='deleteBooking(\"{$row['booking_id']}\")'>
                    <i class='fas fa-trash'></i>
                </button>
            </td>
          </tr>";
}

$stmt->close();
$conn->close();
?>
