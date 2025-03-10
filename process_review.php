<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once 'db.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
    $user_id = $_SESSION['user_id'];
    $turf_id = mysqli_real_escape_string($conn, $_POST['turf_id']);
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $comments = mysqli_real_escape_string($conn, $_POST['review']);

    // Validate rating
    if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
        $_SESSION['error'] = "Invalid rating value";
        header("Location: contact.php");
        exit();
    }

    // Validate turf_id exists
    $turf_check = $conn->prepare("SELECT turf_id FROM turf WHERE turf_id = ?");
    $turf_check->bind_param("i", $turf_id);
    $turf_check->execute();
    if ($turf_check->get_result()->num_rows === 0) {
        $_SESSION['error'] = "Invalid turf selected";
        header("Location: contact.php");
        exit();
    }
    $turf_check->close();

    // Check if user has already reviewed this turf
    $check_existing = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND turf_id = ?");
    $check_existing->bind_param("ii", $user_id, $turf_id);
    $check_existing->execute();
    if ($check_existing->get_result()->num_rows > 0) {
        $_SESSION['error'] = "You have already reviewed this turf";
        header("Location: contact.php");
        exit();
    }
    $check_existing->close();

    // Insert review into database
    $sql = "INSERT INTO reviews (user_id, turf_id, rating, comments) VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $user_id, $turf_id, $rating, $comments);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Thank you! Your review has been submitted successfully.";
        header("Location: contact.php");
        exit();
    } else {
        $_SESSION['error'] = "Error submitting review. Please try again.";
        header("Location: contact.php");
        exit();
    }

    $stmt->close();
} else {
    // If someone tries to access this file directly
    header("Location: contact.php");
    exit();
}

$conn->close();
?> 