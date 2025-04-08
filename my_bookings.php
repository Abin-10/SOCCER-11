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
            CASE 
                WHEN HOUR(fts.start_time) BETWEEN 6 AND 10 THEN t.morning_rate
                WHEN HOUR(fts.start_time) BETWEEN 11 AND 16 THEN t.afternoon_rate
                ELSE t.evening_rate
            END as hourly_rate,
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

<style>
.fade-out {
    transition: opacity 0.5s ease;
    opacity: 0;
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

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
            <!-- Removed the date filter input -->
            <!-- <input type="text" class="form-control" id="dateFilter" placeholder="Select Date"> -->
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
                    <th>Invoice</th>
                    <th>Action</th>
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
                            <?php if ($booking['booking_status'] === 'confirmed'): ?>
                                <button class="btn btn-sm btn-primary download-invoice" 
                                        data-booking-id="<?php echo $booking['id']; ?>"
                                        data-turf-name="<?php echo htmlspecialchars($booking['turf_name']); ?>"
                                        data-location="<?php echo htmlspecialchars($booking['location']); ?>"
                                        data-date="<?php echo $booking['date']; ?>"
                                        data-time="<?php echo date('h:i A', strtotime($booking['start_time'])) . ' - ' . 
                                                       date('h:i A', strtotime($booking['end_time'])); ?>"
                                        data-amount="<?php echo $booking['hourly_rate']; ?>"
                                        data-user-name="<?php echo htmlspecialchars($_SESSION['user_name']); ?>">
                                    <i class="fas fa-download"></i> Invoice
                                </button>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($booking['booking_status'] === 'pending'): ?>
                                <button class="btn btn-sm btn-danger cancel-booking" 
                                        data-booking-id="<?php echo $booking['id']; ?>">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($result->num_rows === 0): ?>
                    <tr>
                        <td colspan="6" class="text-center">No bookings found</td>
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
    // Store all bookings data
    const bookingsData = [
        <?php while ($booking = $result->fetch_assoc()): ?>
            {
                id: <?php echo json_encode($booking['id']); ?>,
                turfName: <?php echo json_encode($booking['turf_name']); ?>,
                location: <?php echo json_encode($booking['location']); ?>,
                date: <?php echo json_encode($booking['date']); ?>,
                startTime: <?php echo json_encode($booking['start_time']); ?>,
                endTime: <?php echo json_encode($booking['end_time']); ?>,
                status: <?php echo json_encode($booking['booking_status']); ?>,
                hourlyRate: <?php echo json_encode($booking['hourly_rate']); ?>,
                createdAt: <?php echo json_encode($booking['created_at']); ?>
            },
        <?php endwhile; ?>
    ];

    // Reset the result pointer for the table rendering
    <?php $result->data_seek(0); ?>

    // Status filter change handler
    document.getElementById('statusFilter').addEventListener('change', filterBookings);

    // Filter bookings function
    function filterBookings() {
        const status = document.getElementById('statusFilter').value;
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            let showRow = true;
            
            // Status filter
            if (status) {
                const rowStatus = row.querySelector('td:nth-child(4) .badge').textContent.trim().toLowerCase();
                if (rowStatus !== status.toLowerCase()) showRow = false;
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

    document.getElementById('confirmCancel').addEventListener('click', async function() {
        if (!bookingToCancel) return;

        try {
            const response = await fetch('cancel_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    booking_id: bookingToCancel
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // Update the row status without fading out
                const row = document.querySelector(`button[data-booking-id="${bookingToCancel}"]`).closest('tr');
                const statusCell = row.querySelector('td:nth-child(4)');
                statusCell.innerHTML = '<span class="badge badge-danger">Cancelled</span>';
                
                // Remove the cancel button
                row.querySelector('.cancel-booking').remove();
                
                // Show success message
                alert('Booking cancelled successfully');
            } else {
                alert(data.message || 'Failed to cancel booking');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to cancel booking');
        } finally {
            $('#cancelBookingModal').modal('hide');
            bookingToCancel = null;
        }
    });

    // Invoice download functionality
    document.querySelectorAll('.download-invoice').forEach(button => {
        button.addEventListener('click', function() {
            const bookingData = {
                id: this.dataset.bookingId,
                turfName: this.dataset.turfName,
                location: this.dataset.location,
                date: this.dataset.date,
                time: this.dataset.time,
                amount: this.dataset.amount,
                userName: this.dataset.userName
            };

            // Generate invoice using HTML2PDF
            generateInvoice(bookingData);
        });
    });

    function generateInvoice(bookingData) {
        // Create invoice HTML content
        const invoiceContent = `
            <div style="padding: 40px; font-family: 'Segoe UI', Arial, sans-serif; max-width: 800px; margin: 0 auto; background: #fff; color: #333;">
                <div style="text-align: center; margin-bottom: 30px;">
                    <h1 style="color: #2c3e50; font-size: 28px; margin-bottom: 5px;">TURF BOOKING INVOICE</h1>
                    <div style="width: 100px; height: 3px; background: #3498db; margin: 10px auto;"></div>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
                    <div>
                        <p style="color: #7f8c8d; margin: 5px 0;">Invoice #: ${bookingData.id}</p>
                        <p style="color: #7f8c8d; margin: 5px 0;">Date: ${new Date().toLocaleDateString('en-US', { 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                        })}</p>
                    </div>
                    <div style="text-align: right;">
                        <h3 style="color: #2c3e50; margin: 0;">Customer Details</h3>
                        <p style="color: #7f8c8d; margin: 5px 0;">${bookingData.userName || 'Valued Customer'}</p>
                    </div>
                </div>

                <div style="background: #f8f9fa; padding: 25px; border-radius: 8px; margin-bottom: 30px;">
                    <h2 style="color: #2c3e50; font-size: 24px; margin-bottom: 20px;">
                        ${bookingData.turfName}
                        <span style="display: block; font-size: 16px; color: #7f8c8d; margin-top: 5px;">
                            ${bookingData.location}
                        </span>
                    </h2>
                    <p style="color: #666; line-height: 1.6;">
                        Experience premium sports facilities at our well-maintained turf. 
                        Perfect for football, cricket, and other sports activities.
                    </p>
                </div>

                <div style="background: #f8f9fa; padding: 25px; border-radius: 8px; margin-bottom: 30px;">
                    <h3 style="color: #2c3e50; margin-bottom: 15px;">Booking Details</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <p style="color: #7f8c8d; margin: 5px 0;">Date</p>
                            <p style="color: #2c3e50; font-weight: bold;">${bookingData.date}</p>
                        </div>
                        <div>
                            <p style="color: #7f8c8d; margin: 5px 0;">Time Slot</p>
                            <p style="color: #2c3e50; font-weight: bold;">${bookingData.time}</p>
                        </div>
                    </div>
                </div>

                <div style="background: #2c3e50; color: white; padding: 15px; border-radius: 8px; margin-bottom: 30px;">
                    <h3 style="margin: 0 0 15px 0;">Payment Details</h3>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <p style="margin: 5px 0; color: #bdc3c7;">Amount Paid</p>
                            <p style="font-size: 24px; font-weight: bold; margin: 5px 0;">₹${parseFloat(bookingData.amount).toFixed(2)}</p>
                        </div>
                        <div style="background: #27ae60; padding: 8px 15px; border-radius: 5px;">
                            <p style="margin: 0;">PAID</p>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; color: #7f8c8d; font-size: 14px; margin-top: 40px;">
                    <p>Thank you for choosing our facility!</p>
                    <p style="margin: 5px 0;">For any queries, please contact us at soocer09771@gmail.com</p>
                </div>
            </div>
        `;

        // Create a hidden iframe
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
        
        // Write invoice content to iframe and print
        iframe.contentWindow.document.open();
        iframe.contentWindow.document.write(`
            <html>
                <head>
                    <title>Booking Invoice - ${bookingData.turfName}</title>
                </head>
                <body style="margin: 0; background: #f0f2f5;">
                    ${invoiceContent}
                </body>
            </html>
        `);
        iframe.contentWindow.document.close();
        
        // Print the iframe content
        setTimeout(() => {
            iframe.contentWindow.print();
            document.body.removeChild(iframe);
        }, 500);
    }
});
</script>

<?php
$conn->close();
include 'includes/footer.php';
?> 