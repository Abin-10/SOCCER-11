<?php
require_once 'booking_utils.php';

function generateFormattedBookingId($original_id, $created_at) {
    $date = new DateTime($created_at);
    $year = $date->format('Y');
    $month = $date->format('m');
    
    // Pad the ID with zeros to ensure at least 4 digits
    $padded_id = str_pad($original_id, 4, '0', STR_PAD_LEFT);
    
    // Format: BK-YYYYMM-####
    return "BK-{$year}{$month}-{$padded_id}";
}
?> 