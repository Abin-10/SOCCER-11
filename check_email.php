<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die(json_encode(['error' => 'Unauthorized']));
}

$conn = new mysqli("localhost", "root", "", "registration");

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

if (isset($_POST['email'])) {
    // Sanitize the email input
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    // Return true if email is unique (count = 0), false if it exists
    echo json_encode(['unique' => ($row['count'] == 0)]);
    
    $stmt->close();
} else {
    echo json_encode(['error' => 'No email provided']);
}

$conn->close(); 