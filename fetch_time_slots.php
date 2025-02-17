<?php
include 'db.php';  // Updated

$sql = "SELECT id, start_time, end_time FROM fixed_time_slots ORDER BY id ASC";
$result = $conn->query($sql);

$timeSlots = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $timeSlots[] = [
            'id' => $row['id'],
            'start_time' => date("h:i A", strtotime($row['start_time'])),
            'end_time' => date("h:i A", strtotime($row['end_time']))
        ];
    }
}
$conn->close();

header('Content-Type: application/json');
echo json_encode($timeSlots);
?>
