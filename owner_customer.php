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
</head>
<body>

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
                                echo "Raw status: " . $row['status'] . "<br>";
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
        <a href="owner.php" class="btn btn-success">Back to Dashboard</a>
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
                            <label>Full Name</label>
                            <input type="text" name="name" class="form-control" pattern="[A-Za-z\s]{3,50}" title="Name should only contain letters and spaces, between 3-50 characters" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" id="email" required>
                            <small id="emailFeedback" class="form-text"></small>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" class="form-control" pattern="^[6-9][0-9]{9}$" title="Phone number should start with 6-9 and have 10 digits" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" id="password" class="form-control" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$" title="Password must be at least 8 characters and include uppercase, lowercase, number and special character" required>
                            <small class="form-text text-muted">Password must contain at least 8 characters, including uppercase, lowercase, number and special character</small>
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
            
            if (email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                emailTimeout = setTimeout(function() {
                    $.post('owner_customer.php', {check_email: true, email: email}, function(response) {
                        if (response === 'exists') {
                            feedback.html('Email already exists').addClass('text-danger').removeClass('text-success');
                            $('#submitBtn').prop('disabled', true);
                        } else {
                            feedback.html('Email is available').addClass('text-success').removeClass('text-danger');
                            $('#submitBtn').prop('disabled', false);
                        }
                    });
                }, 500);
            } else {
                feedback.html('');
            }
        });

        $('#confirm_password').on('input', function() {
            const password = $('#password').val();
            const confirmPassword = $(this).val();
            const feedback = $('#passwordMatch');
            
            if (password === confirmPassword) {
                feedback.html('Passwords match').addClass('text-success').removeClass('text-danger');
                $('#submitBtn').prop('disabled', false);
            } else {
                feedback.html('Passwords do not match').addClass('text-danger').removeClass('text-success');
                $('#submitBtn').prop('disabled', true);
            }
        });
    });

    function validateForm() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            alert('Passwords do not match!');
            return false;
        }
        
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passwordRegex.test(password)) {
            alert('Password must be at least 8 characters and include uppercase, lowercase, number and special character');
            return false;
        }
        
        return true;
    }
    </script>
</body>
</html>
