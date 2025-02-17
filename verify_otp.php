<?php
session_start(); 
$error_message = '';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

function generateVerificationCode($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

function sendVerificationEmail($recipientEmail, $verificationCode) {
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
        $mail->Subject = 'Your Verification Code';
        $mail->Body    = "Your verification code is: $verificationCode\n\nThis code will expire in 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $verificationCode = generateVerificationCode();
        $_SESSION['verification_code'] = $verificationCode;
        $_SESSION['email'] = $email;

        if (sendVerificationEmail($email, $verificationCode)) {
            echo"";
        } else {
            $error_message= "Failed to send verification code.";
        }
    } elseif (isset($_POST['verify'])) {
        $enteredOTP = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];
        if ($enteredOTP == $_SESSION['verification_code']) {
            header('Location: reset_password.php');
            unset($_SESSION['verification_code']);  
        } else {
            $error_message="Incorrect OTP.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskMate - OTP Verification</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background: #f5f5f5;  /* Light gray background */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .login-container {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transform-style: preserve-3d;
            perspective: 1000px;
            animation: containerFloat 3s ease-in-out infinite;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            color: #2e7d32;  /* Dark green color */
        }

        .task { color: #42b72a; }
        .mate { color: #2b5419; }

        h2 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: #333;
        }

        p {
            font-size: 1rem !important;
            color: #666;
        }

        .otp-inputs {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 2rem auto;
            gap: 12px;
            max-width: 360px;
        }

        .otp-inputs input {
            width: 45px;
            height: 45px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            color: #333;
            transition: all 0.3s ease;
            padding: 0;
            margin: 0;
        }

        .otp-inputs input:focus {
            outline: none;
            border-color: #42b72a;
            box-shadow: 0 0 10px rgba(66, 183, 42, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 1rem;
            background: #42b72a;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 1.5rem 0;
        }

        .login-btn:hover {
            background: #36a420;
            box-shadow: 0 5px 15px rgba(54, 164, 32, 0.2);
        }

        .error-message {
            background-color: #fff3f3;
            color: #dc2626;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            text-align: center;
            border: 1px solid #fecdd3;
            font-size: 1rem;
        }

        .resend-text {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
            font-size: 0.9rem;
        }

        .resend-text a {
            color: #42b72a;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .resend-text a:hover {
            color: #36a420;
        }

        @keyframes containerFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <span class="task">SOCCER</span><span class="mate">-11</span>
        </div>
        <h2>OTP Verification</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <p style="text-align: center; color: #64748b; margin-bottom: 1.5rem;">Enter the 6-digit code sent to your email.</p>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group otp-inputs">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp2')" id="otp1" name="otp1">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp3')" id="otp2" name="otp2">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp4')" id="otp3" name="otp3">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp5')" id="otp4" name="otp4">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp6')" id="otp5" name="otp5">
                <input type="text" maxlength="1" required id="otp6" name="otp6">
            </div>
            <button type="submit" class="login-btn" name="verify">Verify OTP</button>
          
        </form>
    </div>

    <script>
        function moveToNext(current, nextFieldID) {
            if (current.value.length === 1) {
                document.getElementById(nextFieldID)?.focus();
            }
        }
    </script>
</body>
</html>