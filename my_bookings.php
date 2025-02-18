<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';

// Database connection
$conn = new mysqli("localhost", "root", "", "registration");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user's bookings
$sql = "SELECT 
            tts.id,
            t.name as turf_name,
            t.location,
            t.hourly_rate,
            fts.start_time,
            fts.end_time,
            tts.date,
            tts.booking_status,
            tts.created_at
        FROM turf_time_slots tts
        JOIN turf t ON tts.turf_id = t.turf_id
        JOIN fixed_time_slots fts ON tts.slot_id = fts.id
        WHERE tts.booked_by = ? AND tts.booking_status IS NOT NULL
        ORDER BY tts.date DESC, fts.start_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-4">
    <h2>My Bookings</h2>
    
    <!-- Booking Filters -->
    <div class="row mb-4">
        <div class="col-md-3">
            <select class="form-control" id="statusFilter">
                <option value="">All Bookings</option>
                <option value="confirmed">Confirmed</option>
                <option value="pending">Pending</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" id="dateFilter" placeholder="Select Date">
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Turf</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Booked On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($booking = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($booking['turf_name']); ?>
                            <small class="d-block text-muted"><?php echo htmlspecialchars($booking['location']); ?></small>
                        </td>
                        <td data-date="<?php echo $booking['date']; ?>">
                            <?php echo date('d M Y', strtotime($booking['date'])); ?>
                        </td>
                        <td>
                            <?php 
                            echo date('h:i A', strtotime($booking['start_time'])) . ' - ' . 
                                 date('h:i A', strtotime($booking['end_time'])); 
                            ?>
                        </td>
                        <td>
                            <span class="badge badge-<?php 
                                echo $booking['booking_status'] === 'confirmed' ? 'success' : 
                                    ($booking['booking_status'] === 'pending' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo ucfirst($booking['booking_status']); ?>
                            </span>
                        </td>
                        <td>₹<?php echo number_format($booking['hourly_rate'], 2); ?></td>
                        <td><?php echo date('d M Y, h:i A', strtotime($booking['created_at'])); ?></td>
                        <td>
                            <?php if ($booking['booking_status'] !== 'cancelled' && strtotime($booking['date']) > time()): ?>
                                <button class="btn btn-sm btn-danger cancel-booking" 
                                        data-booking-id="<?php echo $booking['id']; ?>">
                                    Cancel
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($result->num_rows === 0): ?>
                    <tr>
                        <td colspan="7" class="text-center">No bookings found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Cancel Booking Confirmation Modal -->
<div class="modal fade" id="cancelBookingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Booking</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this booking?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Keep it</button>
                <button type="button" class="btn btn-danger" id="confirmCancel">Yes, Cancel Booking</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date picker
    if (typeof flatpickr !== 'undefined') {
        flatpickr("#dateFilter", {
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr) {
                filterBookings();
            }
        });
    }

    // Status filter change handler
    document.getElementById('statusFilter').addEventListener('change', filterBookings);

    // Filter bookings function
    function filterBookings() {
        const status = document.getElementById('statusFilter').value;
        const date = document.getElementById('dateFilter').value;
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            let showRow = true;
            
            // Status filter
            if (status) {
                const rowStatus = row.querySelector('td:nth-child(4) .badge').textContent.trim().toLowerCase();
                if (rowStatus !== status.toLowerCase()) showRow = false;
            }

            // Date filter
            if (date) {
                const rowDate = row.querySelector('td:nth-child(2)').getAttribute('data-date');
                if (rowDate !== date) showRow = false;
            }

            row.style.display = showRow ? '' : 'none';
        });
    }

    // Cancel booking functionality
    let bookingToCancel = null;
    
    document.querySelectorAll('.cancel-booking').forEach(button => {
        button.addEventListener('click', function() {
            bookingToCancel = this.dataset.bookingId;
            $('#cancelBookingModal').modal('show');
        });
    });

    document.getElementById('confirmCancel').addEventListener('click', function() {
        if (!bookingToCancel) return;

        fetch('cancel_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                booking_id: bookingToCancel
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to cancel booking');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to cancel booking');
        })
        .finally(() => {
            $('#cancelBookingModal').modal('hide');
        });
    });
});
</script>

<?php
$conn->close();
include 'includes/footer.php';
?> 