<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>SOCCER-11 Admin - Bookings</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport">

        <!-- Favicon -->
        <link href="img/favicon.ico" rel="icon">

        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Oswald:wght@400;700&display=swap" rel="stylesheet"> 

        <!-- CSS Libraries -->
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

        <!-- Template Stylesheet -->
        <link href="css/style.css" rel="stylesheet">
        
        <style>
            /* Reusing admin styles */
            .admin-sidebar {
                background: #f8f9fa;
                min-height: 100vh;
                padding: 20px;
                border-right: 1px solid #dee2e6;
            }
            
            .admin-content {
                padding: 20px;
            }
            
            .booking-table {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .admin-nav-link {
                color: #333;
                padding: 10px 15px;
                display: block;
                border-radius: 4px;
                margin-bottom: 5px;
            }
            
            .admin-nav-link:hover {
                background: #e9ecef;
                text-decoration: none;
            }
            
            .admin-nav-link.active {
                background:rgb(23, 125, 31);
                color: #fff;
            }

            /* New booking-specific styles */
            .time-slot {
                padding: 10px;
                margin: 5px;
                border: 1px solid #dee2e6;
                border-radius: 4px;
                cursor: pointer;
            }

            .time-slot.selected {
                background-color:rgb(16, 113, 19);
                color: white;
                border-color:rgb(32, 115, 14);
            }

            .time-slot.unavailable {
                background-color: #f8f9fa;
                color: #6c757d;
                cursor: not-allowed;
            }
        </style>
    </head>

    <body>
        <!-- Nav Start -->
        <nav class="navbar navbar-expand-md bg-light navbar-light">
            <a href="index.html" class="navbar-brand">SOCCER-11 ADMIN</a>
            <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-toggle="dropdown">
                            <i class="fas fa-user-circle mr-2"></i>Admin Name
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="admin-profile.php"><i class="fas fa-user mr-2"></i>Profile</a>
                            <a class="dropdown-item" href="settings.php"><i class="fas fa-cog mr-2"></i>Settings</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="login.php"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
        <!-- Nav End -->
        
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-md-2 admin-sidebar">
                    <h4 class="mb-4">Dashboard</h4>
                    <a href="admin.php" class="admin-nav-link"><i class="fas fa-home mr-2"></i>Overview</a>
                    <a href="booking.php" class="admin-nav-link active"><i class="fas fa-calendar-alt mr-2"></i>Bookings</a>
                    <a href="users.php" class="admin-nav-link"><i class="fas fa-users mr-2"></i>Users</a>
                    <a href="turfs.php" class="admin-nav-link"><i class="fas fa-football-ball mr-2"></i>Turfs</a>
                    <a href="admin_fixed_slots.php" class="admin-nav-link"><i class="fas fa-clock mr-2"></i>Manage Slots</a> <!-- New link for managing slots -->
                    <a href="settings.php" class="admin-nav-link"><i class="fas fa-cog mr-2"></i>Settings</a>
                </div>

                <!-- Main Content -->
                <div class="col-md-10 admin-content">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Manage Bookings</h2>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#newBookingModal">
                            <i class="fas fa-plus mr-2"></i>New Booking
                        </button>
                    </div>

                    <!-- Booking Filters -->
                    <div class="booking-table mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date Range</label>
                                    <input type="text" class="form-control" id="dateRange">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Turf</label>
                                    <select class="form-control">
                                        <option value="">All Turfs</option>
                                        <option>Turf A - Main Ground</option>
                                        <option>Turf B - Practice Area</option>
                                        <option>Turf C - Mini Ground</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control">
                                        <option value="">All Status</option>
                                        <option>Confirmed</option>
                                        <option>Pending</option>
                                        <option>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Search</label>
                                    <input type="text" class="form-control" placeholder="Search bookings...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bookings Table -->
                    <div class="booking-table">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Turf</th>
                                    <th>Date</th>
                                    <th>Time Slot</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#BK001</td>
                                    <td>John Doe</td>
                                    <td>Turf A</td>
                                    <td>2025-01-13</td>
                                    <td>7 PM - 8 PM</td>
                                    <td>₹1000</td>
                                    <td><span class="badge badge-success">Confirmed</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#viewBookingModal">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editBookingModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <!-- Add more booking rows as needed -->
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-end">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add New Booking Modal -->
        <div class="modal fade" id="newBookingModal" tabindex="-1" role="dialog" aria-labelledby="newBookingModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newBookingModalLabel">New Booking</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-group">
                                <label for="customerName">Customer Name</label>
                                <input type="text" class="form-control" id="customerName" required>
                            </div>
                            <div class="form-group">
                                <label for="turf">Select Turf</label>
                                <select class="form-control" id="turf">
                                    <option>Turf A</option>
                                    <option>Turf B</option>
                                    <option>Turf C</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="date">Booking Date</label>
                                <input type="date" class="form-control" id="date" required>
                            </div>
                            <div class="form-group">
                                <label for="time">Time Slot</label>
                                <select class="form-control" id="time">
                                    <option>7 PM - 8 PM</option>
                                    <option>8 PM - 9 PM</option>
                                    <option>9 PM - 10 PM</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="amount">Amount</label>
                                <input type="number" class="form-control" id="amount" value="1000" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Booking</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        
        <script>
            $(document).ready(function(){
                $('#dateRange').flatpickr({
                    mode: "range",
                    dateFormat: "Y-m-d"
                });
            });
        </script>
    </body>
</html>
