<?php
session_start();
$error_message = '';

if (!isset($_SESSION['email'])) {
    header('Location: forgot_password.php');
    exit(); 
}
$conn= new mysqli('localhost','root','','registration');
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    $error_message = "An error occurred during login. Please try again later.";
} else {
    if($_SERVER["REQUEST_METHOD"]=="POST"){
        $password= $_POST['new_password'];
        $confirm_password= $_POST['confirm_password'];
        if($password==$confirm_password){
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET password = '$hashed_password' WHERE email = '" . $_SESSION['email'] . "'";
            
            if ($conn->query($sql) === TRUE) {
                $_SESSION['success_message'] = "Your password has been successfully updated!";
                header('Location: login.php');
                unset($_SESSION['email']);  
                exit();
            } else {
                $error_message = "Error updating password: " . $conn->error;
            }
            
    }
    
} 
}  

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soccer-11 - Reset Password</title>
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
            background: #ffffff;  /* Changed to white */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            backdrop-filter: blur(10px);
            animation: fadeIn 0.6s ease-out;
        }

        .logo {
            text-align: center;
            margin-bottom: 2.5rem;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .task { color: #2ecc71; }
        .mate { color: #27ae60; }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 2.5rem;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 2rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.8rem;
            color: #34495e;
            font-size: 1rem;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-group input:focus {
            outline: none;
            border-color: #2ecc71;
            box-shadow: 0 0 10px rgba(46, 204, 113, 0.2);
        }

        .login-btn {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(to right, #2ecc71, #27ae60);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .login-btn:hover {
            background: linear-gradient(to right, #27ae60, #219a52);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 15px rgba(46, 204, 113, 0.2);
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.9rem;
            margin-top: 0.7rem;
            font-weight: 500;
        }

        .error-message1 {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            text-align: center;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <span class="task">Soccer</span><span class="mate">-11</span>
        </div>
        <h2>Reset Password</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message1">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form id="resetForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="6">
                <div id="password-error" class="error-message"></div> <!-- Error message for password -->
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                <div id="cpassword-error" class="error-message"></div> <!-- Error message for confirm password -->
            </div>
            <button type="submit" class="login-btn">Reset Password</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordError = document.getElementById('password-error');
            const cpasswordError = document.getElementById('cpassword-error');

            const passwordInput = document.getElementById('new_password');
            const cpasswordInput = document.getElementById('confirm_password');

            function checkPassword() {
                const password = passwordInput.value.trim();
                const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d@$!%*?&]{8,}$/;

                if (password === '') {
                    passwordError.innerHTML = "Password is required";
                    passwordInput.style.border = "2px solid red";
                    return false;
                } else if (!passwordPattern.test(password)) {
                    passwordError.innerHTML = "Password must be 8+ characters with uppercase, lowercase, and a number.";
                    passwordInput.style.border = "2px solid red";
                    return false;
                } else {
                    passwordError.innerHTML = "";
                    passwordInput.style.border = "2px solid green";
                    return true;
                }
            }

            function checkConfirmPassword() {
                const password = passwordInput.value.trim();
                const confirmPassword = cpasswordInput.value.trim();
                
                // Confirm password should only be checked if both fields are filled
                if (confirmPassword === '') {
                    cpasswordError.innerHTML = "Please confirm your password";
                    cpasswordInput.style.border = "2px solid red";
                    return false;
                } else if (confirmPassword !== password) {
                    cpasswordError.innerHTML = "Passwords do not match";
                    cpasswordInput.style.border = "2px solid red";
                    return false;
                } else {
                    cpasswordError.innerHTML = "";
                    cpasswordInput.style.border = "2px solid green";
                    return true;
                }
            }

            passwordInput.addEventListener('input', function() {
            checkPassword();
            if (cpasswordInput.value !== '') {
                checkConfirmPassword();
            }
        });
        cpasswordInput.addEventListener('input', checkConfirmPassword);

            document.getElementById('resetForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                let isValid = true;
                if (!checkPassword()) isValid = false;
                if (!checkConfirmPassword()) isValid = false;
                
                if (isValid) {
                    console.log('Form is valid, submitting...');
                    this.submit();  
                } else {
                    console.log('Form has errors, not submitting.');
                }
            });
        });
    </script>
</body>
</html>