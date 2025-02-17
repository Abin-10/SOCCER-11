<?php
// Initialize these variables to avoid undefined array key warnings
$name = isset($_POST['name']) ? $_POST['name'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Database connection parameters
$host = "localhost";
$username = "root";
$password = ""; 
$database = "registration"; // Changed to match existing database name

// Create connection
try {
    $conn = mysqli_connect($host, $username, $password, $database);
    
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $host = "localhost";
    $user = "root";
    $pass = ""; 
    $database = "registration";

  
    $conn = mysqli_connect($host, $user, $pass);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    
    $db_sql = "CREATE DATABASE IF NOT EXISTS $database";
    if (mysqli_query($conn, $db_sql)) {
        mysqli_select_db($conn, $database);
    } else {
        die("Error creating database: " . mysqli_error($conn));
    }

    $table_sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(15) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        role enum('admin','owner','user')
    )";

    if (!mysqli_query($conn, $table_sql)) {
        die("Error creating table: " . mysqli_error($conn));
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

   
    $name = mysqli_real_escape_string($conn, $name);
    $email = mysqli_real_escape_string($conn, $email);
    $phone = mysqli_real_escape_string($conn, $phone);
    $hashedPassword = mysqli_real_escape_string($conn, $hashedPassword);

   
    $sql = "INSERT INTO users (name, email, phone, password) 
            VALUES ('$name', '$email', '$phone', '$hashedPassword')";

    if (mysqli_query($conn, $sql)) {
        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    
    mysqli_close($conn);
}
?>