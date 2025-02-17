<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "registration");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    // Set status based on action
    $status = ($action === 'activate') ? 1 : 0;
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $status, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "User has been " . ($status ? 'activated' : 'deactivated') . " successfully.";
    } else {
        $_SESSION['error'] = "Error updating user status.";
    }
    
    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request.";
}

$conn->close();
header("Location: owner_customer.php");
exit(); 