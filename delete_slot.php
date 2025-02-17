<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $delete_query = "DELETE FROM fixed_time_slots WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: admin_fixed_slots.php?message=Slot deleted successfully");
    } else {
        header("Location: admin_fixed_slots.php?error=Error deleting slot");
    }
} else {
    header("Location: admin_fixed_slots.php");
}
?>
