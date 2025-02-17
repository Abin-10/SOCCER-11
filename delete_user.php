<?php
session_start();
$conn = new mysqli("localhost", "root", "", "registration");

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare DELETE query
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully!'); window.location.href='users.php';</script>";
    } else {
        echo "<script>alert('Error deleting user!'); window.location.href='users.php';</script>";
    }
}
?>
