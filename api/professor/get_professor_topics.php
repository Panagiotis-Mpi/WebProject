<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php'; // ανεβαίνουμε 2 φακέλους

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση."]);
    exit;
}

$professor_id = $_SESSION['user_id'];

$sql = "SELECT id, title, summary, pdf_path
        FROM Topics
        WHERE creator_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$result = $stmt->get_result();

$topics = [];
while ($row = $result->fetch_assoc()) {
    $topics[] = $row;
}

echo json_encode(["success" => true, "topics" => $topics]);

$stmt->close();
$conn->close();
