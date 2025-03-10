<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

function sendBookingConfirmationEmail($userEmail, $userName, $bookingDetails) {
    $mail = new PHPMailer(true);

    try {
        // Server settings - using the same credentials as verify_otp.php
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'soccer097711@gmail.com';
        $mail->Password   = 'ccax pvgw mmdn wttr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('soccer097711@gmail.com', 'SOCCER-11');
        $mail->addAddress($userEmail, $userName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Booking Confirmation - SOCCER-11";
        
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #4CAF50;'>SOCCER-11 - Booking Confirmed!</h2>
            <p>Dear $userName,</p>
            <p>Your turf booking has been confirmed. Here are the details:</p>
            <div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <p><strong>Date:</strong> " . date('d M Y', strtotime($bookingDetails['date'])) . "</p>
                <p><strong>Time:</strong> " . date('h:i A', strtotime($bookingDetails['start_time'])) . " - " . 
                                          date('h:i A', strtotime($bookingDetails['end_time'])) . "</p>
                <p><strong>Turf:</strong> " . $bookingDetails['turf_name'] . "</p>
                <p><strong>Location:</strong> " . $bookingDetails['location'] . "</p>
            </div>
            <p>Thank you for choosing SOCCER-11. We look forward to seeing you!</p>
            <p style='color: #666; font-size: 14px;'>If you have any questions, please don't hesitate to contact us.</p>
        </div>";

        $mail->Body = $message;

        return $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}
?> 