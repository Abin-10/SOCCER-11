<?php
include 'includes/header.php';

// Database connection
$conn = new mysqli("localhost", "root", "", "registration");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch turf details
$turf_sql = "SELECT t.turf_id, t.name, t.location, t.hourly_rate, t.owner_id 
             FROM turf t 
             WHERE t.turf_id = 1 LIMIT 1";  // You might want to make this dynamic based on the selected turf
$turf_result = $conn->query($turf_sql);
$turf = $turf_result->fetch_assoc();
?>

<h3 class="mb-4">Book Your Turf</h3>
<div class="row">
    <div class="col-md-6">
        <div class="turf-card">
            <div class="turf-card-body">
                <h5><?php echo htmlspecialchars($turf['name']); ?></h5>
                <p><?php echo htmlspecialchars($turf['location']); ?></p>
                <p class="price">₹<?php echo htmlspecialchars($turf['hourly_rate']); ?> per hour</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="booking-form">
            <form id="booking-form" action="process_booking.php" method="POST">
                <input type="hidden" name="turf_id" value="<?php echo $turf['turf_id']; ?>">
                
                <div class="form-group">
                    <label for="date">Select Date</label>
                    <input type="date" id="date" name="date" class="form-control" 
                           min="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label for="time-slot">Select Time Slot</label>
                    <select id="time-slot" name="time_slot" class="form-control" required>
                        <option value="">Choose a time slot</option>
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

                <button type="submit" class="btn btn-primary">Book Now</button>
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
                Your turf booking has been confirmed successfully.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="window.location.href='my_bookings.php'">View My Bookings</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('date').addEventListener('change', function() {
    const date = this.value;
    const turfId = <?php echo $turf['turf_id']; ?>;
    fetchAvailableSlots(date, turfId);
});

function fetchAvailableSlots(date, turfId) {
    const timeSlotSelect = document.getElementById('time-slot');
    timeSlotSelect.innerHTML = '<option value="">Loading slots...</option>';

    // Add timestamp to prevent caching
    const timestamp = new Date().getTime();
    const url = `check_availability.php?date=${date}&turf_id=${turfId}&_=${timestamp}`;

    fetch(url)
        .then(async response => {
            const text = await response.text();
            try {
                const data = JSON.parse(text);
                return data;
            } catch (e) {
                console.error('Raw response:', text);
                throw new Error('Server returned invalid JSON. Check console for details.');
            }
        })
        .then(data => {
            timeSlotSelect.innerHTML = '<option value="">Choose a time slot</option>';
            
            if (data.error) {
                throw new Error(data.error);
            }

            if (!data.slots || !Array.isArray(data.slots)) {
                throw new Error('Invalid response format');
            }

            const currentDate = new Date();
            const selectedDate = new Date(date);
            const isToday = selectedDate.toDateString() === currentDate.toDateString();
            const currentTime = currentDate.getHours() * 60 + currentDate.getMinutes(); // Current time in minutes

            data.slots.forEach(slot => {
                // Convert slot time to minutes for comparison
                const [hours, minutes] = slot.start_time.split(':');
                const slotTime = parseInt(hours) * 60 + parseInt(minutes);

                // Show slot if:
                // 1. It's a future date, or
                // 2. It's today and the slot time is in the future
                // 3. And the slot is not already booked
                if ((!isToday || (isToday && slotTime > currentTime)) && !slot.booked) {
                    const option = document.createElement('option');
                    option.value = slot.id;
                    option.textContent = `${slot.start_time} - ${slot.end_time}`;
                    timeSlotSelect.appendChild(option);
                }
            });

            // Show message if no slots are available
            if (timeSlotSelect.options.length === 1) { // Only the default option exists
                timeSlotSelect.innerHTML = '<option value="">No available slots for this date</option>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            timeSlotSelect.innerHTML = '<option value="">Error loading slots: ' + error.message + '</option>';
        });
}

// Add this new code after the existing functions
document.getElementById('booking-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('process_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Raw response:', text);
            throw new Error('Server returned invalid JSON. Check console for details.');
        }
    })
    .then(data => {
        if (data.success) {
            // Show the confirmation modal
            $('#bookingConfirmationModal').modal('show');
        } else {
            throw new Error(data.message || 'Booking failed. Please try again.');
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
});
</script>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>
