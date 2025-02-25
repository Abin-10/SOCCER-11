<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "registration");

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$email = $_POST['email'] ?? '';
$email = $conn->real_escape_string($email);

// If editing user, exclude current user's email from check
$userId = $_POST['user_id'] ?? null;
$userClause = $userId ? " AND id != '$userId'" : "";

$query = "SELECT COUNT(*) as count FROM users WHERE email = '$email'" . $userClause;
$result = $conn->query($query);
$row = $result->fetch_assoc();

echo json_encode(['unique' => $row['count'] == 0]);

$conn->close(); 