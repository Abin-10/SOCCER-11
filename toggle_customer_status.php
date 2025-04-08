<?php
session_start();
// Remove the require_once of owner_customer.php and instead copy just the needed functions

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

// Add PHPMailer includes and function definition here instead
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

function sendStatusNotificationEmail($recipientEmail, $status) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'soccer097711@gmail.com';
        $mail->Password   = 'ccax pvgw mmdn wttr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('soccer097711@gmail.com', 'Soccer-11');
        $mail->addAddress($recipientEmail);
        
        if ($status === 'active') {
            $mail->Subject = 'Account Activated - Soccer-11';
            $mail->Body    = "Dear User,\n\nYour account has been activated. You can now log in and access all features of Soccer-11.\n\nBest regards,\nSoccer-11 Team";
        } else {
            $mail->Subject = 'Account Deactivated - Soccer-11';
            $mail->Body    = "Dear User,\n\nYour account has been deactivated. Please contact support if you believe this is an error.\n\nBest regards,\nSoccer-11 Team";
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

$conn = new mysqli("localhost", "root", "", "registration");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $user_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    // Get user's email before updating status
    $email_query = $conn->query("SELECT email FROM users WHERE id = $user_id");
    $user_data = $email_query->fetch_assoc();
    $user_email = $user_data['email'];
    
    $new_status = ($action === 'activate') ? 'active' : 'inactive';
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $user_id);
    
    if ($stmt->execute()) {
        // Send email notification
        if (sendStatusNotificationEmail($user_email, $new_status)) {
            $_SESSION['success'] = "User status updated successfully and notification email sent.";
        } else {
            $_SESSION['success'] = "User status updated successfully but failed to send notification email.";
        }
    } else {
        $_SESSION['error'] = "Failed to update user status.";
    }
    
    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request.";
}

header("Location: owner_customer.php");
exit(); 