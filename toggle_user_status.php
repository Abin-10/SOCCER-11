<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$conn = new mysqli("localhost", "root", "", "registration");

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed']));
}

if (isset($_POST['user_id']) && isset($_POST['status'])) {
    $user_id = $conn->real_escape_string($_POST['user_id']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $query = "UPDATE users SET status = '$status' WHERE id = '$user_id'";
    
    if ($conn->query($query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
}

$conn->close(); 