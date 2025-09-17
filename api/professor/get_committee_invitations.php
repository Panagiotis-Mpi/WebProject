<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

// Έλεγχος role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$professor_id = $_SESSION['user_id'];

// Επιλογή ενεργών προσκλήσεων
$sql = "SELECT cm.id AS invitation_id, t.id AS thesis_id, t.student_id, u.first_name, u.last_name, 
               th.title AS topic_title, cm.status
        FROM CommitteeMembers cm
        JOIN Theses t ON cm.thesis_id = t.id
        JOIN Topics th ON t.topic_id = th.id
        JOIN Users u ON t.student_id = u.id
        WHERE cm.professor_id = ? AND cm.status = 'invited'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$result = $stmt->get_result();

$invitations = [];
while ($row = $result->fetch_assoc()) {
    $invitations[] = $row;
}

echo json_encode(["success" => true, "invitations" => $invitations]);

$stmt->close();
$conn->close();
?>
