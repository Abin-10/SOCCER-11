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
    <style>
        .admin-sidebar { background: #f8f9fa; min-height: 100vh; padding: 20px; border-right: 1px solid #dee2e6; }
        .admin-content { padding: 20px; }
        .admin-nav-link { color: #333; padding: 10px 15px; display: block; border-radius: 4px; margin-bottom: 5px; }
        .admin-nav-link:hover { background: #e9ecef; text-decoration: none; }
        .admin-nav-link.active { background: rgb(14, 97, 23); color: #fff; }
        .table th, .table td { text-align: center; }
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
                <a href="users.php" class="admin-nav-link"><i class="fas fa-users mr-2"></i>Users</a>
                <a href="turfs.php" class="admin-nav-link"><i class="fas fa-football-ball mr-2"></i>Turfs</a>
                <a href="admin_fixed_slots.php" class="admin-nav-link"><i class="fas fa-clock mr-2"></i>Manage Slots</a> <!-- Added link for managing slots -->
                <a href="settings.php" class="admin-nav-link"><i class="fas fa-cog mr-2"></i>Settings</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 admin-content">
                <h2>Users List</h2>
                <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addUserModal">
                    <i class="fas fa-user-plus"></i> Add New User
                </button>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
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
                            <input type="password" name="password" class="form-control" required minlength="6">
                            <small class="text-danger" id="passwordError"></small>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" class="form-control" required>
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

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('addUserForm');
            const submitBtn = document.getElementById('submitBtn');
            const inputs = form.querySelectorAll('input, select');
            
            const validationRules = {
                name: {
                    pattern: /^[A-Za-z\s]+$/,
                    minLength: 3,
                    message: 'Name should contain only letters and spaces, minimum 3 characters'
                },
                email: {
                    pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                    message: 'Please enter a valid email address'
                },
                phone: {
                    pattern: /^[6-9]\d{9}$/,
                    message: 'Phone number should start with 6-9 and have 10 digits'
                },
                password: {
                    pattern: /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/,
                    message: 'Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number and one special character'
                },
                role: {
                    message: 'Please select a role'
                }
            };

            async function checkEmailUnique(email) {
                try {
                    const response = await fetch('check_email.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `email=${encodeURIComponent(email)}`
                    });
                    const data = await response.json();
                    return data.unique;
                } catch (error) {
                    console.error('Error checking email:', error);
                    return false;
                }
            }

            async function validateField(input) {
                const field = input.name;
                const value = input.value.trim();
                const errorElement = document.getElementById(`${field}Error`);
                
                if (!value) {
                    errorElement.textContent = 'This field is required';
                    return false;
                }

                const rules = validationRules[field];
                
                if (rules.pattern && !rules.pattern.test(value)) {
                    errorElement.textContent = rules.message;
                    return false;
                }

                if (rules.minLength && value.length < rules.minLength) {
                    errorElement.textContent = rules.message;
                    return false;
                }

                // Check for unique email
                if (field === 'email') {
                    const isUnique = await checkEmailUnique(value);
                    if (!isUnique) {
                        errorElement.textContent = 'This email is already registered';
                        return false;
                    }
                }

                errorElement.textContent = '';
                return true;
            }

            async function validateForm() {
                let isValid = true;
                for (const input of inputs) {
                    if (!await validateField(input)) {
                        isValid = false;
                    }
                }
                submitBtn.disabled = !isValid;
            }

            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    validateForm();
                });
                input.addEventListener('blur', () => validateField(input));
            });

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                let isValid = true;
                for (const input of inputs) {
                    if (!await validateField(input)) {
                        isValid = false;
                    }
                }
                if (isValid) {
                    this.submit();
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
        });
    </script>
</body>
</html>
