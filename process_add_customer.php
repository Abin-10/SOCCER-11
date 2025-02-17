<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "registration");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = $_POST['password'];
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: owner_customer.php");
        exit();
    }
    
    // Check if email already exists
    $check_email = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check_email->num_rows > 0) {
        $_SESSION['error'] = "Email already exists";
        header("Location: owner_customer.php");
        exit();
    }
    
    // Validate phone number (10 digits starting with 6-9)
    if (!preg_match("/^[6-9][0-9]{9}$/", $phone)) {
        $_SESSION['error'] = "Invalid phone number format";
        header("Location: owner_customer.php");
        exit();
    }
    
    // Validate password strength
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
        $_SESSION['error'] = "Password does not meet requirements";
        header("Location: owner_customer.php");
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new customer
    $sql = "INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'user')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Customer added successfully";
    } else {
        $_SESSION['error'] = "Error adding customer: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
    
    header("Location: owner_customer.php");
    exit();
} else {
    header("Location: owner_customer.php");
    exit();
}
?> 