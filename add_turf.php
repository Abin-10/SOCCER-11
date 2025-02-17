<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "registration";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $turf_name = $conn->real_escape_string($_POST['turf_name']);
    $location = $conn->real_escape_string($_POST['location']);
    $hourly_rate = $conn->real_escape_string($_POST['hourly_rate']);
    $owner_id = $_SESSION['user_id']; // Assuming you store user_id in session

    $sql = "INSERT INTO turf (name, location, hourly_rate, owner_id) 
            VALUES ('$turf_name', '$location', '$hourly_rate', '$owner_id')";

    if ($conn->query($sql) === TRUE) {
        header('Location: turfs.php?success=1');
    } else {
        header('Location: turfs.php?error=1');
    }
}

$conn->close();
?>
