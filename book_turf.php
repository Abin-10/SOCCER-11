<?php
include 'includes/header.php';

// Database connection
$conn = new mysqli("localhost", "root", "", "registration");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch turf details
$turf_sql = "SELECT t.turf_id, t.name, t.location, t.morning_rate, t.afternoon_rate, t.evening_rate, t.owner_id 
             FROM turf t 
             WHERE t.turf_id = 1 LIMIT 1";
$turf_result = $conn->query($turf_sql);
$turf = $turf_result->fetch_assoc();

// Fetch all turfs for selection
$turf_sql = "SELECT turf_id, name FROM turf"; 
$turf_list_result = $conn->query($turf_sql);
$turf_list = $turf_list_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="third-party-cookies" content="allowed">
    <!-- Rest of your head content -->
</head>

<!-- Include Razorpay SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<h3 class="mb-4">Book Your Turf</h3>
<div class="row">
    <div class="col-md-6">
        <div class="turf-card">
            <div class="turf-card-body">
                <h5><?php echo htmlspecialchars($turf['name']); ?></h5>
                <p><?php echo htmlspecialchars($turf['location']); ?></p>
                
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="booking-form">
            <form id="booking-form" action="process_booking.php" method="POST">
                <input type="hidden" name="turf_id" value="<?php echo $turf['turf_id']; ?>">
                <input type="hidden" name="payment_id" id="payment_id">
                <input type="hidden" name="payment_status" id="payment_status">
                
                <!-- Add turf selection dropdown -->
                <div class="form-group">
                    <label for="turf-select">Select Turf</label>
                    <select id="turf-select" name="turf_id" class="form-control" required>
                        <option value="">Choose a turf</option>
                        <?php foreach ($turf_list as $turf_option): ?>
                            <option value="<?php echo $turf_option['turf_id']; ?>">
                                <?php echo htmlspecialchars($turf_option['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date">Select Date</label>
                    <input type="date" id="date" name="date" class="form-control" 
                           min="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label for="time-slot">Select Time Slot</label>
                    <select id="time-slot" name="time_slot" class="form-control" required disabled>
                        <option value="">Choose a turf first</option>
                    </select>
                </div>

                <?php if ($turf['owner_id'] == $_SESSION['user_id']): ?>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="is_owner_booking" name="is_owner_booking">
                        <label class="custom-control-label" for="is_owner_booking">Book as Owner</label>
                    </div>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <p class="mb-1">Total Amount: <span id="booking-amount">₹0</span></p>
                    <small class="text-muted">Payment will be processed securely via Razorpay</small>
                </div>

                <button type="button" id="pay-button" class="btn btn-primary" disabled>Pay & Book Now</button>
            </form>
        </div>
    </div>
</div>

<!-- Add this modal HTML before the script tags -->
<div class="modal fade" id="bookingConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="bookingConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookingConfirmationModalLabel">Booking Confirmed!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Your turf booking has been confirmed successfully.</p>
                <p>Payment ID: <span id="confirmation-payment-id"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="window.location.href='my_bookings.php'">View My Bookings</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('turf-select').addEventListener('change', function() {
    const turfId = this.value;
    const date = document.getElementById('date').value;
    const timeSlotSelect = document.getElementById('time-slot');
    
    if (turfId) {
        timeSlotSelect.disabled = false;
        timeSlotSelect.innerHTML = '<option value="">Choose a time slot</option>';
        
        fetch(`get_turf_details.php?turf_id=${turfId}`)
            .then(response => response.json())
            .then(turf => {
                // Update the turf card details
                document.querySelector('.turf-card-body h5').textContent = turf.name;
                document.querySelector('.turf-card-body p:nth-of-type(1)').textContent = turf.location;
                document.querySelector('.price').textContent = `₹${turf.morning_rate} per hour`;
                
                // Update hidden input
                document.querySelector('input[name="turf_id"]').value = turf.turf_id;
                
                // Fetch available slots if date is selected
                if (date) {
                    fetchAvailableSlots(date, turfId);
                }
            })
            .catch(error => {
                console.error('Error fetching turf details:', error);
            });
    } else {
        // If no turf is selected, disable and reset time slot select
        timeSlotSelect.disabled = true;
        timeSlotSelect.innerHTML = '<option value="">Choose a turf first</option>';
        document.getElementById('pay-button').disabled = true;
        document.getElementById('booking-amount').textContent = '₹0';
    }
});

document.getElementById('date').addEventListener('change', function() {
    const date = this.value;
    const turfSelect = document.getElementById('turf-select');
    const turfId = turfSelect.value;
    
    if (!turfId) {
        alert('Please select a turf first');
        this.value = ''; // Reset date selection
        return;
    }
    
    if (turfId) {
        fetchAvailableSlots(date, turfId);
    }
});

document.getElementById('time-slot').addEventListener('change', function() {
    updateBookingAmount();
});

function formatTo12Hour(time) {
    // Split the time into hours and minutes
    const [hours, minutes] = time.split(':');
    
    // Convert to 12-hour format
    let period = 'AM';
    let hour = parseInt(hours);
    
    if (hour >= 12) {
        period = 'PM';
        if (hour > 12) {
            hour -= 12;
        }
    }
    if (hour === 0) {
        hour = 12;
    }
    
    return `${hour}:${minutes} ${period}`;
}

function fetchAvailableSlots(date, turfId) {
    const timeSlotSelect = document.getElementById('time-slot');
    timeSlotSelect.innerHTML = '<option value="">Loading slots...</option>';
    document.getElementById('pay-button').disabled = true;

    // Get current time
    const now = new Date();
    const today = new Date().toISOString().split('T')[0];
    const currentHour = now.getHours();
    const currentMinutes = now.getMinutes();

    fetch(`get_turf_details.php?turf_id=${turfId}`)
        .then(response => response.json())
        .then(turfDetails => {
            return fetch(`check_availability.php?date=${date}&turf_id=${turfId}`)
                .then(async response => {
                    const text = await response.text();
                    try {
                        const data = JSON.parse(text);
                        return { 
                            slots: data.slots, 
                            rates: {
                                morning: parseFloat(turfDetails.morning_rate),
                                afternoon: parseFloat(turfDetails.afternoon_rate),
                                evening: parseFloat(turfDetails.evening_rate)
                            }
                        };
                    } catch (e) {
                        console.error('Raw response:', text);
                        throw new Error('Server returned invalid JSON');
                    }
                });
        })
        .then(data => {
            timeSlotSelect.innerHTML = '<option value="">Choose a time slot</option>';
            
            if (!data.slots || !Array.isArray(data.slots)) {
                throw new Error('Invalid response format');
            }

            data.slots.forEach(slot => {
                if (slot.is_available) {
                    // Check if slot is in the future
                    const [slotHour, slotMinutes] = slot.start_time.split(':').map(Number);
                    const isToday = date === today;
                    const isFutureSlot = !isToday || 
                        (slotHour > currentHour || 
                        (slotHour === currentHour && slotMinutes > currentMinutes));

                    if (isFutureSlot) {
                        const option = document.createElement('option');
                        let rate;
                        if (slotHour >= 6 && slotHour <= 10) {
                            rate = data.rates.morning;
                        } else if (slotHour > 10 && slotHour <= 16) {
                            rate = data.rates.afternoon;
                        } else if (slotHour > 16 && slotHour <= 23) {
                            rate = data.rates.evening;
                        } else {
                            rate = data.rates.morning;
                        }

                        const slotData = {
                            id: slot.id,
                            booking_id: slot.slot_booking_id,
                            duration: 1,
                            hourly_rate: rate
                        };
                        option.value = JSON.stringify(slotData);
                        
                        const startTime12 = formatTo12Hour(slot.start_time);
                        const endTime12 = formatTo12Hour(slot.end_time);
                        option.textContent = `${startTime12} - ${endTime12} (₹${rate})`;
                        timeSlotSelect.appendChild(option);
                    }
                }
            });
            
            updateBookingAmount();
        })
        .catch(error => {
            console.error('Error:', error);
            timeSlotSelect.innerHTML = '<option value="">Error loading slots</option>';
            document.getElementById('booking-amount').textContent = '₹0';
            document.getElementById('pay-button').disabled = true;
        });
}

function updateBookingAmount() {
    const timeSlotSelect = document.getElementById('time-slot');
    const bookingAmountElement = document.getElementById('booking-amount');
    const payButton = document.getElementById('pay-button');
    
    if (timeSlotSelect.value) {
        try {
            const slotData = JSON.parse(timeSlotSelect.value);
            const hourlyRate = parseFloat(slotData.hourly_rate) || 0;
            const duration = parseInt(slotData.duration) || 1;
            const amount = hourlyRate * duration;
            
            if (amount <= 0) {
                throw new Error('Invalid amount');
            }
            
            // Store the calculated amount for later use
            timeSlotSelect.dataset.calculatedAmount = amount;
            bookingAmountElement.textContent = `₹${amount.toFixed(2)}`;
            payButton.disabled = false;
        } catch (e) {
            console.error('Error calculating amount:', e);
            bookingAmountElement.textContent = '₹0';
            payButton.disabled = true;
        }
    } else {
        bookingAmountElement.textContent = '₹0';
        payButton.disabled = true;
    }
}

// Modify the pay button click handler
document.getElementById('pay-button').addEventListener('click', function() {
    const timeSlotSelect = document.getElementById('time-slot');
    
    if (!timeSlotSelect.value) {
        alert('Please select a time slot first');
        return;
    }
    
    // Use the stored calculated amount
    const amount = parseFloat(timeSlotSelect.dataset.calculatedAmount) || 0;
    const amountInPaise = Math.round(amount * 100); // Convert to paise

    if (!amountInPaise || amountInPaise <= 0) {
        alert('Invalid booking amount. Please try again.');
        return;
    }
    
    const options = {
        key: 'rzp_test_3ujiEJJapHR3Se',
        amount: amountInPaise,
        currency: 'INR',
        name: '<?php echo htmlspecialchars($turf['name']); ?>',
        description: 'Turf Booking Payment',
        handler: function (response) {
            // Handle the payment success
            document.getElementById('payment_id').value = response.razorpay_payment_id;
            document.getElementById('payment_status').value = 'success';
            document.getElementById('confirmation-payment-id').textContent = response.razorpay_payment_id;
            
            // Submit the form
            processBooking();
        },
        prefill: {
            name: '<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>',
            email: '<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>'
        },
        theme: {
            color: '#3399cc'
        },
        modal: {
            ondismiss: function() {
                // Handle when user closes the Razorpay payment window
                console.log('Payment window closed');
            }
        }
    };
    
    const rzp = new Razorpay(options);
    rzp.open();
});

function processBooking() {
    const formData = new FormData(document.getElementById('booking-form'));
    const slotData = JSON.parse(formData.get('time_slot'));
    
    // Calculate payment amount
    const hourlyRate = slotData.hourly_rate || 0;
    const duration = slotData.duration || 1;
    const amount = hourlyRate * duration;
    
    // Add payment details to formData
    formData.set('time_slot', slotData.id);
    formData.set('payment_amount', amount);
    formData.set('payment_status', 'completed');
    
    if (slotData.booking_id) {
        formData.set('booking_id', slotData.booking_id);
    }
    
    fetch('process_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        const text = await response.text();
        try {
            if (text.includes('Fatal error')) {
                throw new Error('Server error occurred. Please try again.');
            }
            return JSON.parse(text);
        } catch (e) {
            console.error('Raw response:', text);
            throw new Error('Server error occurred. Please try again.');
        }
    })
    .then(data => {
        if (data.success) {
            $('#bookingConfirmationModal').modal('show');
        } else {
            throw new Error(data.message || 'Booking failed. Please try again.');
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}
</script>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>