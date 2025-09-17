<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

// Μόνο για καθηγητές
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$sql = "SELECT id, first_name, last_name, am FROM Users WHERE role = 'student'";
$result = $conn->query($sql);

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode(["success" => true, "students" => $students]);
$conn->close();
