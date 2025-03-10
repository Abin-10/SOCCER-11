<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "registration");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if email already exists
if (isset($_POST['check_email'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $result = $conn->query("SELECT id FROM users WHERE email = '$email'");
    echo $result->num_rows > 0 ? 'exists' : 'available';
    exit();
}

// Fetch all customers
$result = $conn->query("SELECT id, name, email, phone, status FROM users WHERE role = 'user'");
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Customers - SOCCER-11</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f1f8e9 0%, #e8f5e9 100%);
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(180deg, #4CAF50 0%, #388E3C 100%);
            color: white;
            padding: 25px;
            position: fixed;
            height: 100vh;
            box-shadow: 4px 0 25px rgba(76,175,80,0.2);
        }

        .logo {
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 35px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo i {
            font-size: 28px;
            color: #A5D6A7;
            text-shadow: 0 0 15px rgba(165,214,167,0.4);
        }

        .logo h2 {
            font-size: 20px;
            color: white;
            margin: 0;
        }

        .menu-items {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .menu-items a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px 20px;
            border-radius: 12px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 8px;
            background: linear-gradient(to right, transparent 0%, #ffffff 100%);
            background-size: 200% 100%;
            background-position: left bottom;
            border: 1px solid rgba(255,255,255,0.1);
            position: relative;
        }

        .menu-items a:hover {
            background-position: right bottom;
            transform: translateX(12px);
            box-shadow: 0 6px 20px rgba(76,175,80,0.25);
            color: #2E7D32;
        }

        .menu-items a.active {
            background: white;
            color: #2E7D32;
            box-shadow: 0 6px 20px rgba(76,175,80,0.25);
        }

        .menu-items i {
            width: 20px;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .menu-items a:hover i {
            transform: scale(1.2) rotate(5deg);
            color: #2E7D32;
        }

        /* Responsive Design for Sidebar */
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 80px;
                padding: 15px;
            }

            .logo h2,
            .menu-items span {
                display: none;
            }

            .menu-items a {
                padding: 15px;
                justify-content: center;
            }

            .menu-items i {
                margin: 0;
            }

            .main-content {
                margin-left: 80px;
            }
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="logo">
                <i class="fas fa-futbol"></i>
                <h2>SOCCER-11</h2>
            </div>
            <ul class="menu-items">
                <li>
                    <a href="owner.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="owner_bookings.php">
                        <i class="fas fa-calendar"></i>
                        <span>Bookings</span>
                    </a>
                </li>
                <li>
                    <a href="owner_customer.php" class="active">
                        <i class="fas fa-users"></i>
                        <span>Customers</span>
                    </a>
                </li>
                <li>
                    <a href="owner_time_slots.php">
                        <i class="fas fa-clock"></i>
                        <span>Time Slots</span>
                    </a>
                </li>
                <li>
                    <a href="owner_reviews.php">
                        <i class="fas fa-star"></i>
                        <span>Reviews</span>
                    </a>
                </li>
                <li>
                    <a href="owner_settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container mt-5">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                <h2>Customers List</h2>
                <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addCustomerModal">
                    <i class="fas fa-user-plus"></i> Add Customer
                </button>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['phone']) ?></td>
                                <td>
                                    <?php 
                                        echo htmlspecialchars($row['status']) === 'active' ? 'Active' : 'Inactive';
                                    ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'active'): ?>
                                        <a href="toggle_customer_status.php?id=<?= $row['id'] ?>&action=deactivate" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to deactivate this user?')">
                                            <i class="fas fa-user-times"></i> Deactivate
                                        </a>
                                    <?php else: ?>
                                        <a href="toggle_customer_status.php?id=<?= $row['id'] ?>&action=activate" 
                                           class="btn btn-success btn-sm"
                                           onclick="return confirm('Are you sure you want to activate this user?')">
                                            <i class="fas fa-user-check"></i> Activate
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Customer</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="process_add_customer.php" method="POST" id="addCustomerForm" onsubmit="return validateForm()">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" pattern="[A-Za-z\s]{3,50}" title="Name should only contain letters and spaces, between 3-50 characters" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" id="email" pattern="[a-zA-Z0-9._%+-]+@gmail\.com$" title="Please enter a valid Gmail address" required>
                            <small id="emailFeedback" class="form-text"></small>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" class="form-control" pattern="^[6-9][0-9]{9}$" title="Phone number should start with 6-9 and have 10 digits" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" id="password" class="form-control" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$" title="Password must be at least 8 characters and include uppercase, lowercase, and numbers" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                            <small id="passwordMatch" class="form-text"></small>
                        </div>
                        <button type="submit" class="btn btn-success" id="submitBtn">Add Customer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        let emailTimeout;
        
        $('#email').on('input', function() {
            clearTimeout(emailTimeout);
            const email = $(this).val();
            const feedback = $('#emailFeedback');
            
            // Check if it's a valid Gmail address
            if (email && /^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(email)) {
                emailTimeout = setTimeout(function() {
                    $.post('owner_customer.php', {check_email: true, email: email}, function(response) {
                        if (response === 'exists') {
                            feedback.html('Email already exists').addClass('text-danger').removeClass('text-success');
                            $('#submitBtn').prop('disabled', true);
                        } else {
                            feedback.html('Email is available').addClass('text-success').removeClass('text-danger');
                            checkFormValidity(); // Check form validity
                        }
                    });
                }, 500);
            } else if (email) {
                feedback.html('Please enter a valid Gmail address').addClass('text-danger').removeClass('text-success');
                $('#submitBtn').prop('disabled', true);
            } else {
                feedback.html('');
                $('#submitBtn').prop('disabled', true);
            }
        });

        // Add live password validation
        $('#password').on('input', function() {
            const password = $(this).val();
            const feedback = $(this).next('.password-feedback');
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/;
            
            // Create feedback element if it doesn't exist
            if (!feedback.length) {
                $(this).after('<small class="form-text password-feedback"></small>');
            }
            
            if (password) {
                if (passwordRegex.test(password)) {
                    $(this).next('.password-feedback').html('Password meets requirements')
                        .addClass('text-success').removeClass('text-danger');
                    checkFormValidity(); // Check form validity
                } else {
                    $(this).next('.password-feedback').html('Password must be at least 8 characters and include uppercase, lowercase, and numbers')
                        .addClass('text-danger').removeClass('text-success');
                    $('#submitBtn').prop('disabled', true);
                }
            } else {
                $(this).next('.password-feedback').html('');
            }
            
            // Trigger confirm password validation if it has a value
            if ($('#confirm_password').val()) {
                $('#confirm_password').trigger('input');
            }
        });

        // Update confirm password validation
        $('#confirm_password').on('input', function() {
            const password = $('#password').val();
            const confirmPassword = $(this).val();
            const feedback = $('#passwordMatch');
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/;
            
            if (confirmPassword) {
                if (password === confirmPassword && passwordRegex.test(password)) {
                    feedback.html('Passwords match').addClass('text-success').removeClass('text-danger');
                    checkFormValidity(); // Check form validity
                } else {
                    feedback.html('Passwords do not match or do not meet requirements')
                        .addClass('text-danger').removeClass('text-success');
                    $('#submitBtn').prop('disabled', true);
                }
            } else {
                feedback.html('');
            }
        });

        // Function to check overall form validity
        function checkFormValidity() {
            const emailValid = $('#emailFeedback').hasClass('text-success');
            const passwordValid = $('#password').next('.password-feedback').hasClass('text-success');
            const confirmPasswordValid = $('#passwordMatch').hasClass('text-success');
            $('#submitBtn').prop('disabled', !(emailValid && passwordValid && confirmPasswordValid));
        }
    });

    function validateForm() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            alert('Passwords do not match!');
            return false;
        }
        
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/;
        if (!passwordRegex.test(password)) {
            alert('Password must be at least 8 characters and include uppercase, lowercase, and numbers');
            return false;
        }
        
        return true;
    }
    </script>
</body>
</html>
