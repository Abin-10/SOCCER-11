<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "registration");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch admin name
$user_id = $_SESSION['user_id'];
$admin_query = $conn->query("SELECT name FROM users WHERE id = '$user_id'");
$admin_data = $admin_query->fetch_assoc();
$admin_name = $admin_data['name'] ?? 'Admin';

// Fetch all users
$result = $conn->query("SELECT id, name, email, phone, role, status FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SOCCER-11 Admin - Users</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fd;
        }
        
        .navbar {
            background: rgb(24, 137, 32) !important;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
        }
        
        .navbar-brand, .nav-link {
            color: white !important;
        }
        
        .admin-sidebar {
            background: white;
            min-height: 100vh;
            padding: 30px;
            border-right: 1px solid rgba(0,0,0,0.05);
            box-shadow: 2px 0 15px rgba(0,0,0,0.05);
        }
        
        .admin-content {
            padding: 40px;
        }
        
        .admin-nav-link {
            color: #333;
            padding: 12px 20px;
            display: block;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .admin-nav-link:hover {
            background: rgba(24, 137, 32, 0.1);
            color: rgb(24, 137, 32);
            text-decoration: none;
            transform: translateX(5px);
        }
        
        .admin-nav-link.active {
            background: rgb(24, 137, 32);
            color: #fff;
            box-shadow: 0 4px 15px rgba(24, 137, 32, 0.2);
        }
        
        h2, h4 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            color: rgb(24, 137, 32);
        }
        
        .table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .table thead {
            background: rgb(24, 137, 32);
            color: white;
        }
        
        .table th {
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border: none;
        }
        
        .btn {
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-success {
            background: rgb(24, 137, 32);
            border: none;
            box-shadow: 0 4px 15px rgba(24, 137, 32, 0.2);
        }
        
        .btn-success:hover {
            background: rgb(24, 137, 32);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(24, 137, 32, 0.3);
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: rgb(24, 137, 32);
            box-shadow: 0 0 0 0.2rem rgba(24, 137, 32, 0.1);
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .modal-header {
            background: rgb(24, 137, 32);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .modal-title {
            font-family: 'Playfair Display', serif;
        }
        
        .close {
            color: white;
        }
        
        .dropdown-menu {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        
        .dropdown-item {
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .dropdown-item:hover {
            background: rgba(24, 137, 32, 0.1);
            color: rgb(24, 137, 32);
        }
        
        /* Add this CSS to make the role select box bigger */
        .form-control-large {
            width: 100%; /* Full width */
            height: 45px; /* Increase height */
            font-size: 16px; /* Increase font size */
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-md bg-light navbar-light">
        <a href="admin.php" class="navbar-brand">SOCCER-11 ADMIN</a>
        <div class="collapse navbar-collapse justify-content-between">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                        <i class="fas fa-user-circle mr-2"></i><?php echo htmlspecialchars($admin_name); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="admin-profile.php"><i class="fas fa-user mr-2"></i>Profile</a>
                        <a class="dropdown-item" href="settings.php"><i class="fas fa-cog mr-2"></i>Settings</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 admin-sidebar">
                <h4 class="mb-4">Dashboard</h4>
                <a href="admin.php" class="admin-nav-link"><i class="fas fa-home mr-2"></i>Overview</a>
                <a href="booking.php" class="admin-nav-link"><i class="fas fa-calendar-alt mr-2"></i>Bookings</a>
                <a href="users.php" class="admin-nav-link active"><i class="fas fa-users mr-2"></i>Users</a>
                <a href="turfs.php" class="admin-nav-link"><i class="fas fa-football-ball mr-2"></i>Turfs</a>
                <a href="admin_fixed_slots.php" class="admin-nav-link"><i class="fas fa-clock mr-2"></i>Manage Slots</a>
                <a href="settings.php" class="admin-nav-link"><i class="fas fa-cog mr-2"></i>Settings</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 admin-content">
                <h2>Users List</h2>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <button class="btn btn-success" data-toggle="modal" data-target="#addUserModal">
                            <i class="fas fa-user-plus"></i> Add New User
                        </button>
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="searchUser" class="form-control" placeholder="Search users...">
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['phone']) ?></td>
                                <td><?= ucfirst($row['role']) ?></td>
                                <td>
                                    <button class="btn btn-sm status-toggle <?= $row['status'] === 'active' ? 'btn-success' : 'btn-danger' ?>" 
                                            data-user-id="<?= $row['id'] ?>" 
                                            data-status="<?= $row['status'] ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </button>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-user" 
                                            data-user-id="<?= $row['id'] ?>"
                                            data-name="<?= htmlspecialchars($row['name']) ?>"
                                            data-email="<?= htmlspecialchars($row['email']) ?>"
                                            data-phone="<?= htmlspecialchars($row['phone']) ?>"
                                            data-role="<?= $row['role'] ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="process_add_user.php" method="POST" id="addUserForm" novalidate>
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" class="form-control" required pattern="[A-Za-z\s]+" minlength="3">
                            <small class="text-danger" id="nameError"></small>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                            <small class="text-danger" id="emailError"></small>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" class="form-control" required pattern="^[6-9]\d{9}$">
                            <small class="text-danger" id="phoneError"></small>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                            <small class="text-danger" id="passwordError"></small>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" class="form-control form-control-large" required>
                                <option value="">Select Role</option>
                                <option value="user">User</option>
                                <option value="owner">Owner</option>
                                <option value="admin">Admin</option>
                            </select>
                            <small class="text-danger" id="roleError"></small>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Add User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Edit User Modal -->
    <div class="modal fade" id="editUserModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="process_edit_user.php" method="POST" id="editUserForm" novalidate>
                        <input type="hidden" name="user_id" id="edit_user_id">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required pattern="[A-Za-z\s]+" minlength="3">
                            <small class="text-danger" id="editNameError"></small>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                            <small class="text-danger" id="editEmailError"></small>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" id="edit_phone" class="form-control" required pattern="^[6-9]\d{9}$">
                            <small class="text-danger" id="editPhoneError"></small>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" id="edit_role" class="form-control form-control-large" required>
                                <option value="">Select Role</option>
                                <option value="user">User</option>
                                <option value="owner">Owner</option>
                            </select>
                            <small class="text-danger" id="editRoleError"></small>
                        </div>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="success-animation">
                        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                            <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                    </div>
                    <h4 class="mt-3">Success!</h4>
                    <p class="mb-4">User has been updated successfully.</p>
                    <button type="button" class="btn btn-success" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Success Modal Styles */
    .success-animation {
        margin: 20px auto;
    }

    .checkmark {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: block;
        stroke-width: 2;
        stroke: #4bb71b;
        stroke-miterlimit: 10;
        box-shadow: inset 0px 0px 0px #4bb71b;
        animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
    }

    .checkmark__circle {
        stroke-dasharray: 166;
        stroke-dashoffset: 166;
        stroke-width: 2;
        stroke-miterlimit: 10;
        stroke: #4bb71b;
        fill: none;
        animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }

    .checkmark__check {
        transform-origin: 50% 50%;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }

    @keyframes stroke {
        100% {
            stroke-dashoffset: 0;
        }
    }

    @keyframes scale {
        0%, 100% {
            transform: none;
        }
        50% {
            transform: scale3d(1.1, 1.1, 1);
        }
    }

    @keyframes fill {
        100% {
            box-shadow: inset 0px 0px 0px 30px #4bb71b;
        }
    }

    #successModal .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 3px 20px rgba(0,0,0,0.1);
    }

    #successModal .modal-body {
        padding: 2rem;
    }

    #successModal h4 {
        color: #4bb71b;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    #successModal p {
        color: #666;
        font-size: 1.1rem;
    }

    #successModal .btn-success {
        padding: 10px 30px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
    }

    #successModal .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(75, 183, 27, 0.3);
    }

    .form-control.is-valid {
        border-color: #28a745;
        padding-right: calc(1.5em + .75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(.375em + .1875rem) center;
        background-size: calc(.75em + .375rem) calc(.75em + .375rem);
    }

    .form-control.is-invalid {
        border-color: #dc3545;
        padding-right: calc(1.5em + .75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%23dc3545' viewBox='-2 -2 7 7'%3e%3cpath stroke='%23dc3545' d='M0 0l3 3m0-3L0 3'/%3e%3ccircle r='.5'/%3e%3ccircle cx='3' r='.5'/%3e%3ccircle cy='3' r='.5'/%3e%3ccircle cx='3' cy='3' r='.5'/%3e%3c/svg%3E");
        background-repeat: no-repeat;
        background-position: right calc(.375em + .1875rem) center;
        background-size: calc(.75em + .375rem) calc(.75em + .375rem);
    }

    .text-danger {
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    /* Style for disabled submit button */
    .btn:disabled {
        cursor: not-allowed;
        opacity: 0.65;
    }
    </style>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('addUserForm');
            const submitBtn = document.getElementById('submitBtn');
            const inputs = form.querySelectorAll('input, select');
            
            // Initially disable the submit button
            submitBtn.disabled = true;

            // Live validation function for each field
            async function validateField(input) {
                const field = input.name;
                const value = input.value.trim();
                const errorElement = document.getElementById(`${field}Error`);
                let isValid = true;

                // Clear previous error message
                errorElement.textContent = '';

                // Required field validation
                if (!value) {
                    errorElement.textContent = 'This field is required';
                    return false;
                }

                switch (field) {
                    case 'name':
                        if (!/^[A-Za-z\s]+$/.test(value) || value.length < 3) {
                            errorElement.textContent = 'Name should contain only letters and spaces, minimum 3 characters';
                            isValid = false;
                        }
                        break;

                    case 'email':
                        if (!/^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(value)) {
                            errorElement.textContent = 'Please enter a valid Gmail address';
                            isValid = false;
                        } else {
                            // Check if email is unique
                            try {
                                const response = await fetch('check_email.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: `email=${encodeURIComponent(value)}`
                                });
                                const data = await response.json();
                                if (!data.unique) {
                                    errorElement.textContent = 'This email is already registered';
                                    isValid = false;
                                }
                            } catch (error) {
                                console.error('Error checking email:', error);
                                isValid = false;
                            }
                        }
                        break;

                    case 'phone':
                        if (!/^[6-9]\d{9}$/.test(value)) {
                            errorElement.textContent = 'Phone number must be 10 digits and start with 6-9';
                            isValid = false;
                        }
                        break;

                    case 'password':
                        if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/.test(value)) {
                            errorElement.textContent = 'Password must be 8+ characters with uppercase, lowercase & number';
                            isValid = false;
                        }
                        break;

                    case 'role':
                        if (!value) {
                            errorElement.textContent = 'Please select a role';
                            isValid = false;
                        }
                        break;
                }

                // Visual feedback
                if (isValid) {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                } else {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                }

                return isValid;
            }

            // Check all fields and update submit button
            async function validateForm() {
                let isValid = true;
                for (const input of inputs) {
                    const fieldValid = await validateField(input);
                    if (!fieldValid) {
                        isValid = false;
                    }
                }
                submitBtn.disabled = !isValid;
                return isValid;
            }

            // Add live validation listeners
            inputs.forEach(input => {
                input.addEventListener('input', async () => {
                    await validateField(input);
                    await validateForm();
                });

                input.addEventListener('blur', async () => {
                    await validateField(input);
                    await validateForm();
                });
            });

            // Form submission handler
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const isValid = await validateForm();
                if (!isValid) {
                    return;
                }

                try {
                    const formData = new FormData(this);
                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: formData
                    });

                    if (response.ok) {
                        // Show success modal
                        $('#addUserModal').modal('hide');
                        $('#successModal').modal('show');
                        
                        // Reset form
                        form.reset();
                        inputs.forEach(input => {
                            input.classList.remove('is-valid', 'is-invalid');
                        });
                        submitBtn.disabled = true;

                        // Reload page after delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        throw new Error('Failed to add user');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Failed to add user. Please try again.');
                }
            });

            // Add status toggle functionality
            document.querySelectorAll('.status-toggle').forEach(button => {
                button.addEventListener('click', async function() {
                    const userId = this.dataset.userId;
                    const currentStatus = this.dataset.status;
                    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

                    try {
                        const response = await fetch('toggle_user_status.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `user_id=${userId}&status=${newStatus}`
                        });

                        const result = await response.json();
                        if (result.success) {
                            // Update button appearance
                            this.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                            this.dataset.status = newStatus;
                            this.classList.toggle('btn-success');
                            this.classList.toggle('btn-danger');
                        } else {
                            alert('Failed to update status');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred while updating status');
                    }
                });
            });

            // Search functionality
            const searchUser = document.getElementById('searchUser');
            const tableRows = document.querySelectorAll('tbody tr');

            searchUser.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                tableRows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });

            // Edit user functionality
            document.querySelectorAll('.edit-user').forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    const name = this.dataset.name;
                    const email = this.dataset.email;
                    const phone = this.dataset.phone;
                    const role = this.dataset.role;

                    document.getElementById('edit_user_id').value = userId;
                    document.getElementById('edit_name').value = name;
                    document.getElementById('edit_email').value = email;
                    document.getElementById('edit_phone').value = phone;
                    document.getElementById('edit_role').value = role;

                    $('#editUserModal').modal('show');
                });
            });

            // Update the role select elements to show proper options
            const roleSelects = document.querySelectorAll('select[name="role"]');
            roleSelects.forEach(select => {
                // Clear existing options
                select.innerHTML = `
                    <option value="">Select Role</option>
                    <option value="user">User</option>
                    <option value="owner">Owner</option>
                `;

                // Add change event listener
                select.addEventListener('change', function() {
                    validateField(this);
                });
            });
        });
    </script>
</body>
</html>
