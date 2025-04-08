<?php
// Add this at the top of the file
session_start();

// Database connection
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "registration";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Sign In
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_type']) && $_POST['form_type'] == 'signin') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            
            switch($user['role']) {
                case 'admin':
                    header("Location: admin.php");
                    break;
                case 'owner':
                    header("Location: owner.php");
                    break;
                case 'user':
                default:
                    header("Location: userdashboard.php");
                    break;
            }
            exit();
        } else {
            $signinErrors['password'] = "Invalid password";
        }
    } else {
        $signinErrors['email'] = "Email not found or account is inactive";
    }
    $stmt->close();
}

// Handle Sign Up
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_type']) && $_POST['form_type'] == 'signup') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if email already exists
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $signupErrors['email'] = "Email already exists";
    } else {
        $sql = "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $phone, $password);
        
        if ($stmt->execute()) {
            $successMessage = "Registration successful! Please sign in.";
            header("Location: login.php?action=signin");
            exit();
        } else {
            $signupErrors['general'] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
    $check_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In/Sign Up Form</title>
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css?family=Montserrat:400,800');

    * {
      box-sizing: border-box;
    }

    body {
      background: rgb(221, 220, 222);
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      font-family: 'Montserrat', sans-serif;
      height: 100vh;
      margin: -20px 0 50px;
    }

    h1 {
      font-weight: bold;
      margin: 0;
    }

    h2 {
      text-align: center;
    }

    p {
      font-size: 14px;
      font-weight: 100;
      line-height: 20px;
      letter-spacing: 0.5px;
      margin: 20px 0 30px;
    }

    span {
      font-size: 12px;
    }

    a {
      color: #333;
      font-size: 14px;
      text-decoration: none;
      margin: 15px 0;
    }

    button {
      border-radius: 20px;
      border: 1px solid #4CAF50;
      background-color: #4CAF50;
      color: rgb(255, 255, 255);
      font-size: 12px;
      font-weight: bold;
      padding: 12px 45px;
      letter-spacing: 1px;
      text-transform: uppercase;
      transition: transform 80ms ease-in;
      cursor: pointer;
    }

    button:active {
      transform: scale(0.95);
    }

    button:focus {
      outline: none;
    }

    button.ghost {
      background-color: transparent;
      border-color: #FFFFFF;
    }

    form {
      background-color: #FFFFFF;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      padding: 0 50px;
      height: 100%;
      text-align: center;
    }

    input {
      background-color: #eee;
      border: none;
      padding: 12px 15px;
      margin: 8px 0;
      width: 100%;
      font-family: inherit;
    }

    .container {
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25),
                  0 10px 10px rgba(0, 0, 0, 0.22);
      position: relative;
      overflow: hidden;
      width: 768px;
      max-width: 100%;
      min-height: 480px;
      margin-top: 80px;
    }

    .form-container {
      position: absolute;
      top: 0;
      height: 100%;
      transition: all 0.6s ease-in-out;
    }

    .sign-in-container {
      left: 0;
      width: 50%;
      z-index: 2;
    }

    .container.right-panel-active .sign-in-container {
      transform: translateX(100%);
    }

    .sign-up-container {
      left: 0;
      width: 50%;
      opacity: 0;
      z-index: 1;
    }

    .container.right-panel-active .sign-up-container {
      transform: translateX(100%);
      opacity: 1;
      z-index: 5;
      animation: show 0.6s;
    }

    @keyframes show {
      0%, 49.99% {
        opacity: 0;
        z-index: 1;
      }
      50%, 100% {
        opacity: 1;
        z-index: 5;
      }
    }

    .overlay-container {
      position: absolute;
      top: 0;
      left: 50%;
      width: 50%;
      height: 100%;
      overflow: hidden;
      transition: transform 0.6s ease-in-out;
      z-index: 100;
    }

    .container.right-panel-active .overlay-container {
      transform: translateX(-100%);
    }

    .overlay {
      background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
      background-repeat: no-repeat;
      background-size: cover;
      background-position: 0 0;
      color: #FFFFFF;
      position: relative;
      left: -100%;
      height: 100%;
      width: 200%;
      transform: translateX(0);
      transition: transform 0.6s ease-in-out;
    }

    .container.right-panel-active .overlay {
      transform: translateX(50%);
    }

    .overlay-panel {
      position: absolute;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      padding: 0 40px;
      text-align: center;
      top: 0;
      height: 100%;
      width: 50%;
      transform: translateX(0);
      transition: transform 0.6s ease-in-out;
    }

    .overlay-left {
      transform: translateX(-20%);
    }

    .container.right-panel-active .overlay-left {
      transform: translateX(0);
    }

    .overlay-right {
      right: 0;
      transform: translateX(0);
    }

    .container.right-panel-active .overlay-right {
      transform: translateX(20%);
    }

    input.valid {
      border: 2px solid #4CAF50 !important;
      background-color: #f0fff0 !important;
    }

    input.invalid {
      border: 2px solid #ff6b6b !important;
      background-color: #fff0f0 !important;
    }

    .field-error {
      color: #ff6b6b;
      font-size: 12px;
      margin-top: -5px;
      margin-bottom: 5px;
      text-align: left;
      width: 100%;
      display: block;
    }

    .input-group {
        position: relative;
        width: 100%;
        margin: 8px 0;
    }

    .input-group i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
    }

    .input-group input {
        padding-left: 45px !important;
    }

    .back-button {
        position: fixed;
        top: 30px;
        left: 30px;
        z-index: 1000;
    }

    .back-button a {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        background-color: #4CAF50;
        color: white;
        border-radius: 25px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .back-button a:hover {
        background-color: #2E7D32;
        transform: translateX(5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .back-button i {
        font-size: 16px;
        transition: transform 0.3s ease;
    }

    .back-button a:hover i {
        transform: translateX(-3px);
    }

    .logo {
        position: fixed;
        top: 30px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
    }

    .logo h1 {
        color: #4CAF50;
        font-size: 2rem;
        display: flex;
        align-items: center;
        gap: 10px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }

    .logo i {
        font-size: 1.8rem;
        animation: spin 4s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .animate-slide-in {
        animation: slideIn 0.5s ease-out;
    }

    .animate-fade-in {
        animation: fadeIn 0.8s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translate(-50%, -20px);
        }
        to {
            opacity: 1;
            transform: translate(-50%, 0);
        }
    }

    @media (max-width: 768px) {
        .logo h1 {
            font-size: 1.5rem;
        }

        .back-button {
            top: 20px;
            left: 20px;
        }

        .back-button a {
            padding: 8px 15px;
        }
    }
    .abcd{
        background-color: #4CAF50;
    }
  </style>
</head>
<body>
  <div class="back-button animate-slide-in">
    <a href="index.html">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>
  </div>

  <div class="logo animate-fade-in">
    <h1><i class="fas fa-futbol"></i> SOCCER-11</h1>
  </div>

  <div class="container" id="container">
    <div class="form-container sign-up-container">
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="signupForm">
        <h1>Create Account</h1>
        <input type="hidden" name="form_type" value="signup">
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="name" placeholder="Name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" />
        </div>
        <?php if (isset($signupErrors['name'])): ?>
            <span class="field-error"><?php echo $signupErrors['name']; ?></span>
        <?php endif; ?>
        
        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
        </div>
        <?php if (isset($signupErrors['email'])): ?>
            <span class="field-error"><?php echo $signupErrors['email']; ?></span>
        <?php endif; ?>
        
        <div class="input-group">
            <i class="fas fa-phone"></i>
            <input type="tel" name="phone" placeholder="Phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" />
        </div>
        <?php if (isset($signupErrors['phone'])): ?>
            <span class="field-error"><?php echo $signupErrors['phone']; ?></span>
        <?php endif; ?>
        
        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Password" />
        </div>
        <?php if (isset($signupErrors['password'])): ?>
            <span class="field-error"><?php echo $signupErrors['password']; ?></span>
        <?php endif; ?>
        
        <input type="submit" value="Sign Up" class="abcd"/>
      </form>
    </div>
    <div class="form-container sign-in-container">
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="signinForm">
        <h1>Sign in</h1>
        <input type="hidden" name="form_type" value="signin">
        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
        </div>
        <?php if (isset($signinErrors['email'])): ?>
            <span class="field-error"><?php echo $signinErrors['email']; ?></span>
        <?php endif; ?>
        
        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Password" />
        </div>
        <?php if (isset($signinErrors['password'])): ?>
            <span class="field-error"><?php echo $signinErrors['password']; ?></span>
        <?php endif; ?>
        
        <a href="forgot_password.php">Forgot your password?</a>
        <button type="submit">Sign In</button>
      </form>
    </div>
    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-left">
          <h1>Welcome Back!</h1>
          <p>To keep connected with us please login with your personal info</p>
          <button class="ghost" id="signIn">Sign In</button>
        </div>
        <div class="overlay-panel overlay-right">
          <h1>Hello, Friend!</h1>
          <p>Enter your personal details and start journey with us</p>
          <button class="ghost" id="signUp">Sign Up</button>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($successMessage)): ?>
      <div class="success-message">
          <?php echo $successMessage; ?>
      </div>
  <?php endif; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Get URL parameters to check if we should show signup
      const urlParams = new URLSearchParams(window.location.search);
      const action = urlParams.get('action');
      
      // If action is signup, show the signup panel
      if (action === 'signup') {
        document.getElementById('container').classList.add('right-panel-active');
      }

      // Existing panel switching logic
      const signUpButton = document.getElementById('signUp');
      const signInButton = document.getElementById('signIn');
      const container = document.getElementById('container');

      signUpButton.addEventListener('click', () => {
        container.classList.add("right-panel-active");
      });

      signInButton.addEventListener('click', () => {
        container.classList.remove("right-panel-active");
      });

      // Form validation
      const signupForm = document.getElementById('signupForm');
      const signinForm = document.getElementById('signinForm');

      // Validation rules
      const validationRules = {
        name: {
          pattern: /^[A-Za-z]+(?:[-\s][A-Za-z]+)*$/,
          minLength: 2,
          maxLength: 50,
          message: 'Name must be 2-50 characters and contain only letters, spaces, and hyphens'
        },
        email: {
          pattern: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
          message: 'Please enter a valid email address'
        },
        phone: {
          pattern: /^[6-9]\d{9}$/,
          message: 'Phone number must valid and be 10 digits long'
        },
        password: {
          pattern: /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/,
          message: 'Password must be at least 8 characters and include uppercase, lowercase, and numbers'
        }
      };

      // Function to validate a single field
      function validateField(input) {
        const field = input.name;
        const value = input.value.trim();
        const rules = validationRules[field];

        if (!rules) return true; // Skip validation if no rules exist for this field

        // Remove existing error message
        const existingError = input.nextElementSibling;
        if (existingError && existingError.classList.contains('field-error')) {
          existingError.remove();
        }

        // Reset classes
        input.classList.remove('valid', 'invalid');

        // Empty field validation
        if (!value) {
          input.classList.add('invalid');
          const error = document.createElement('span');
          error.className = 'field-error';
          error.textContent = `${field.charAt(0).toUpperCase() + field.slice(1)} is required`;
          input.after(error);
          return false;
        }

        // Pattern validation
        if (!rules.pattern.test(value)) {
          input.classList.add('invalid');
          const error = document.createElement('span');
          error.className = 'field-error';
          error.textContent = rules.message;
          input.after(error);
          return false;
        }

        // Length validation for name field
        if (field === 'name' && (value.length < rules.minLength || value.length > rules.maxLength)) {
          input.classList.add('invalid');
          const error = document.createElement('span');
          error.className = 'field-error';
          error.textContent = rules.message;
          input.after(error);
          return false;
        }

        // Phone number validation
        if (field === 'phone') {
          // Remove all non-digits
          let digits = value.replace(/\D/g, '');
          
          // Check if number starts with valid digit and is 10 digits long
          if (!(/^[6-9]\d{9}$/).test(digits)) {
            input.classList.add('invalid');
            const error = document.createElement('span');
            error.className = 'field-error';
            error.textContent = 'Phone number must start with 6, 7, 8, or 9 and be 10 digits long';
            input.after(error);
            return false;
          }

          // Set plain 10 digits
          input.value = digits;
          input.classList.add('valid');
          return true;
        }

        input.classList.add('valid');
        return true;
      }

      // Add validation to both forms
      [signupForm, signinForm].forEach(form => {
        if (!form) return; // Skip if form doesn't exist
        
        const inputs = form.querySelectorAll('input');

        // Add event listeners for real-time validation
        inputs.forEach(input => {
          ['input', 'blur'].forEach(eventType => {
            input.addEventListener(eventType, () => validateField(input));
          });
        });

       
        form.addEventListener('submit', (e) => {
          e.preventDefault();
          let isValid = true;

          inputs.forEach(input => {
            if (!validateField(input)) {
              isValid = false;
            }
          });

          if (isValid) {
            console.log('Form is valid, preparing to submit');
            const formData = new FormData(form);
            console.log('Form data:', Object.fromEntries(formData));
            
            form.submit();
          }
        });
      });
    });
  </script>
</body>
</html>