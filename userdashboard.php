<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Turf Booking System</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <meta content="Product Landing Page Template" name="keywords">
        <meta content="Product Landing Page Template" name="description">

        <!-- Favicon -->
        <link href="img/favicon.ico" rel="icon">

        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Oswald:wght@400;700&display=swap" rel="stylesheet"> 

        <!-- CSS Libraries -->
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
        <link href="lib/slick/slick.css" rel="stylesheet">
        <link href="lib/slick/slick-theme.css" rel="stylesheet">

        <!-- Template Stylesheet -->
        <link href="css/style.css" rel="stylesheet">
        <style>
            .welcome-card {
                background: #f8f9fa;
                border-radius: 10px;
                padding: 20px;
                text-align: center;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                margin-top: 20px;
            }
            .turf-menu {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 15px;
            }
            .turf-card {
                flex: 1 1 calc(30% - 10px);
                background: #fff;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                transition: transform 0.2s ease-in-out;
            }
            .turf-card:hover {
                transform: translateY(-5px);
            }
            .turf-card img {
                width: 100%;
                height: 150px;
                object-fit: cover;
            }
            .turf-card h5 {
                padding: 10px;
                margin: 0;
                font-size: 1.2rem;
                background: #f8f9fa;
                text-align: center;
            }
            .btn-book {
                margin-top: 20px;
                background-color: #007bff;
                color: #fff;
            }
            .highlight {
                background: #007bff;
                color: #fff;
                padding: 10px;
                border-radius: 5px;
                text-align: center;
                margin-bottom: 20px;
            }
            .announcement {
                background: #ffc107;
                color: #000;
                padding: 10px;
                border-radius: 5px;
                text-align: center;
                margin-bottom: 20px;
            }
            body {
                background-image: url('img/2bddaca37a232e039554eaba34919d3beecf3c37.jpg.webp');
                background-size: cover;
                background-attachment: fixed;
                background-position: center;
                background-repeat: no-repeat;
                position: relative;
            }
            body::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(255, 255, 255, 0.9);
                z-index: -1;
            }
            .welcome-card, .turf-card {
                background: rgba(255, 255, 255, 0.95);
            }
            .logbtn {
                background-color: #007bff;
                color: white;
                border: none;
                border-radius: 25px;
                padding: 8px 20px;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }
            .logbtn:hover {
                background-color: #0056b3;
            }
        </style>
    </head>

    <body data-spy="scroll" data-target=".navbar" data-offset="51">
        <!-- Nav Start -->
        <div id="nav">
            <div class="navbar navbar-expand-md bg-light navbar-light">
                <a href="index.html" class="navbar-brand">SOCCER-11</a>
                <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item"><a href="#dashboard" class="nav-link">Dashboard</a></li>
                        <li class="nav-item"><a href="#book" class="nav-link">Book Turf</a></li>
                        <li class="nav-item"><a href="#cancel" class="nav-link">Cancel Booking</a></li>
                        <li class="nav-item"><a href="#contact" class="nav-link">Contact</a></li>
                        <a href="index.html"><button class="logbtn">Log Out</button></a>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Nav End -->

        <!-- Welcome Section Start -->
        <div id="dashboard" class="mt-5 pt-5">
            <div class="container">
                <div class="welcome-card mb-4" style="margin-top: 50px;">
                    <h1>Welcome, <span id="customer-name">[Customer Name]</span>!</h1>
                    <p>Your current bookings and activity will appear here. Manage your schedule effectively.</p>
                    <button class="btn btn-book" onclick="document.getElementById('book').scrollIntoView({behavior: 'smooth'})">Book Now</button>
                </div>
                <div class="highlight mb-4">
                    <h4>Today's Highlight</h4>
                    <p>Get 10% off on bookings for Full Field!</p>
                </div>
                <div class="announcement mb-4">
                    <h4>Special Announcement</h4>
                    <p>New evening slots are now available. Book your preferred turf today!</p>
                </div>
            </div>
        </div>
        <!-- Welcome Section End -->

        <!-- Book Turf Start -->
        <div id="book" class="mt-5">
            <div class="container">
                <div class="section-header text-center">
                    <h1>Book Your Turf</h1>
                </div>
                <div class="turf-menu">
                    <div class="turf-card">
                        <img src="img/pexels-rogerio-rodrigues-1643207864-27669822.jpg" alt="Full Field">
                        <h5>Full Field</h5>
                    </div>
                    <div class="turf-card">
                        <img src="img/pexels-gonzalo-acuna-166058093-10908537.jpg" alt="Half Field">
                        <h5>Half Field</h5>
                    </div>
                    <div class="turf-card">
                        <img src="img/pexels-jean-daniel-7970589.jpg" alt="Quarter Field">
                        <h5>Quarter Field</h5>
                    </div>
                </div>
                <form id="booking-form" class="mt-4" action="book_turf.php" method="POST">
                    <div class="form-group col-md-6 mx-auto">
                        <label for="date">Select Date</label>
                        <input type="date" id="date" name="date" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6 mx-auto">
                        <label for="time-slot">Select Time Slot</label>
                        <select id="time-slot" name="time_slot" class="form-control" onchange="checkAvailability(this.value)">
                            <option value="6-7">6 AM - 7 AM</option>
                            <option value="7-8">7 AM - 8 AM</option>
                            <option value="8-9">8 AM - 9 AM</option>
                            <option value="9-10">9 AM - 10 AM</option>
                            <option value="10-11">10 AM - 11 AM</option>
                            <option value="11-12">11 AM - 12 PM</option>
                            <option value="12-1">12 PM - 1 PM</option>
                            <option value="1-2">1 PM - 2 PM</option>
                            <option value="2-3">2 PM - 3 PM</option>
                            <option value="3-4">3 PM - 4 PM</option>
                            <option value="4-5">4 PM - 5 PM</option>
                            <option value="5-6">5 PM - 6 PM</option>
                            <option value="6-7">6 PM - 7 PM</option>
                            <option value="7-8">7 PM - 8 PM</option>
                            <option value="8-9">8 PM - 9 PM</option>
                            <option value="9-10">9 PM - 10 PM</option>
                            <option value="10-11">10 PM - 11 PM</option>
                            <option value="11-12">11 PM - 12 AM</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6 mx-auto">
                        <label for="payment">Payment Method</label>
                        <select id="payment" name="payment" class="form-control">
                            <option value="credit">Credit Card</option>
                            <option value="debit">Debit Card</option>
                            <option value="upi">UPI</option>
                            <option value="netbanking">Net Banking</option>
                        </select>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Confirm Booking</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Book Turf End -->

        <!-- Cancel Booking Start -->
        <div id="cancel" class="mt-5">
            <div class="container">
                <div class="section-header">
                    <h1>Cancel Booking</h1>
                </div>
                <form id="cancel-form" action="cancel_booking.php" method="POST">
                    <div class="form-group">
                        <label for="booking-id">Booking ID</label>
                        <input type="text" id="booking-id" name="booking_id" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-danger">Cancel Booking</button>
                </form>
            </div>
        </div>
        <!-- Cancel Booking End -->

        <!-- Contact Start -->
        <div id="contact" class="mt-5">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                    </div>
                    <div class="col-md-6">
                        <div class="contact-info">
                            <h2 style="font-size: 1.8rem; margin-bottom: 15px;">Get in Touch</h2>
                            <p style="font-size: 1rem; margin-bottom: 12px;">
                                SOCCER-11 Kanjirapally
                            </p>
                            <h3 style="font-size: 1.1rem; margin-bottom: 10px;"><i class="fa fa-envelope"></i> kickoff@gmail.com</h3>
                            <h3 style="font-size: 1.1rem; margin-bottom: 10px;"><i class="fa fa-phone"></i> +91 9856754356</h3>
                            <h3 style="font-size: 1.1rem; margin-bottom: 15px;"><i class="fa fa-phone"></i> +91 9678554321</h3>
                            <a class="btn" href="login.php" style="font-size: 1rem; padding: 8px 20px;">Contact Us</a>
                            <div class="social" style="margin-top: 20px;">
                                <a href="#" style="font-size: 1.1rem; margin: 0 8px;"><i class="fab fa-twitter"></i></a>
                                <a href="https://www.facebook.com/" style="font-size: 1.1rem; margin: 0 8px;"><i class="fab fa-facebook-f"></i></a>
                                <a href="https://in.linkedin.com/" style="font-size: 1.1rem; margin: 0 8px;"><i class="fab fa-linkedin-in"></i></a>
                                <a href="https://www.instagram.com/" style="font-size: 1.1rem; margin: 0 8px;"><i class="fab fa-instagram"></i></a>
                                <a href="https://www.youtube.com/" style="font-size: 1.1rem; margin: 0 8px;"><i class="fab fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Contact End -->

        <!-- Footer Start -->
        <div id="footer">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                    </div>
                    <div class="col-md-6">
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer End -->

        <!-- Back to Top -->
        <a href="#" class="back-to-top"><i class="fa fa-chevron-up"></i></a>

        <!-- JavaScript Libraries -->
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
        <script src="lib/easing/easing.min.js"></script>
        <script src="lib/slick/slick.min.js"></script>

        <!-- Availability Check Script -->
        <script>
            function checkAvailability(selectedTimeSlot) {
                // Example logic to check availability (replace with actual backend integration)
                const bookedSlots = ["6-10", "3-6"];
                if (bookedSlots.includes(selectedTimeSlot)) {
                    alert("Selected time slot is unavailable. Please choose another.");
                    document.getElementById("time-slot").value = ""; // Reset the selection
                }
            }
        </script>

        <!-- Template Javascript -->
        <script src="js/main.js"></script>
    </body>
</html>
