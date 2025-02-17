<?php
session_start();
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $conn = new mysqli('localhost', 'root', '', 'registration');
        
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        $error_message = "An error occurred during login. Please try again later.";
    } else {
        
        $email = $conn->real_escape_string(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)); 
        $sql = "SELECT * FROM users WHERE email='$email'";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            echo'<form id="form" method="POST" action="verify_otp.php">';
            echo '<input type="hidden" name="email" value="' . $email . '">';
            echo'</form>';
            echo'<script>document.getElementById("form").submit();</script>';

        } else {
            $error_message = "Invalid email";
        }
        
               $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOCCER-11 - Forgot Password</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        p, input, .back-to-login {
            font-family: 'Open Sans', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            background-color: #ffffff;
        }

        .login-container {
            background: #ffffff;
            padding: 3.5rem;
            border-radius: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1),
                        0 0 0 1px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 480px;
            animation: containerSlideUp 0.8s ease-out;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #2ecc71, #27ae60);
        }

        .logo {
            text-align: center;
            margin-bottom: 2.5rem;
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -1px;
            animation: logoScale 1s ease-out;
        }

        .task { 
            color: #27ae60;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .mate { 
            color: #2ecc71;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #16a085;
            margin-bottom: 1.5rem;
            font-weight: 600;
            font-size: 1.8rem;
            animation: fadeInDown 0.8s ease-out;
        }

        p.description {
            text-align: center;
            color: #666;
            margin-bottom: 2rem;
            font-size: 1.1rem;
            animation: fadeInDown 0.8s ease-out;
        }

        .form-group {
            margin-bottom: 2.2rem;
            position: relative;
            animation: slideInRight 0.8s ease-out backwards;
        }

        .form-group:nth-child(1) {
            animation-delay: 0.2s;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.8rem;
            color: #2c3e50;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 1.2rem 1.4rem;
            border: 2px solid #e8eef3;
            border-radius: 18px;
            font-size: 1.05rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .form-group input:focus {
            outline: none;
            border-color: #27ae60;
            box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.15);
            transform: translateY(-2px);
        }

        .form-group input::placeholder {
            color: #a0aec0;
        }

        .login-btn {
            width: 100%;
            padding: 1.3rem;
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            border: none;
            border-radius: 18px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.25);
            animation: slideInUp 0.8s ease-out backwards;
            animation-delay: 0.4s;
        }

        .login-btn:hover {
            background: linear-gradient(135deg, #219a52 0%, #25a25a 100%);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 6px 25px rgba(46, 204, 113, 0.3);
        }

        .login-btn:active {
            transform: translateY(-1px);
        }

        .back-to-login {
            text-align: center;
            margin-top: 2rem;
            color: #666;
            font-size: 1.05rem;
            animation: fadeIn 0.8s ease-out backwards;
            animation-delay: 0.6s;
        }

        .back-to-login a {
            color: #27ae60;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-to-login a:hover {
            color: #219a52;
            text-decoration: underline;
            transform: translateY(-1px);
        }

        .error-message {
            background-color: rgba(220, 38, 38, 0.08);
            color: #dc2626;
            padding: 1.2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            text-align: center;
            border: 1px solid rgba(220, 38, 38, 0.15);
            font-weight: 500;
            font-size: 0.95rem;
        }

        @keyframes containerSlideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes logoScale {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Hover animations */
        .form-group input {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .login-btn {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .login-btn:hover {
            transform: translateY(-3px) scale(1.02);
        }

        .back-to-login a {
            transition: all 0.3s ease;
        }

        .back-to-login a:hover {
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <span class="task">SOCCER-</span><span class="mate">11</span>
        </div>
        <h2>Forgot Password</h2>
        <p class="description">Enter your email to receive an otp.</p>
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="login-btn">Send An OTP</button>
            <p class="back-to-login">Remember your password? <a href="login.php">Login here</a></p>
        </form>
    </div>
</body>
</html>