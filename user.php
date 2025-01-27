<?php
session_start();

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "registration";

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Initialize errors array
$signinErrors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_type']) && $_POST['form_type'] == 'signin') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Basic validation
    if (empty($email)) {
        $signinErrors['email'] = "Email is required";
    }
    if (empty($password)) {
        $signinErrors['password'] = "Password is required";
    }
    
    // If no validation errors, proceed with authentication
    if (empty($signinErrors)) {
        try {
            // Prepare SQL statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Password is correct, create session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    
                    // Redirect to dashboard or home page
                    header("Location: userdashboard.php");
                    exit();
                } else {
                    $signinErrors['password'] = "Invalid password";
                }
            } else {
                $signinErrors['email'] = "Email not found";
            }
        } catch(PDOException $e) {
            $signinErrors['general'] = "An error occurred. Please try again later.";
        }
    }
    
    // If there are errors, redirect back to login page with errors
    if (!empty($signinErrors)) {
        $_SESSION['signinErrors'] = $signinErrors;
        header("Location: login.php");
        exit();
    }
} else {
    // If someone tries to access this file directly, redirect to login page
    header("Location: login.php");
    exit();
}
?>
