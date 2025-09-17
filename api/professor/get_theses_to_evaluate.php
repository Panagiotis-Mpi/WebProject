<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor'){
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$professor_id = $_SESSION['user_id'];

// Διπλωματικές υπό εξέταση (ως επιβλέπων ή μέλος τριμελούς)
$sql = "
SELECT t.id AS thesis_id, t.topic_id, top.title AS topic_title, u.first_name AS student_first, u.last_name AS student_last
FROM Theses t
JOIN Users u ON u.id = t.student_id
JOIN Topics top ON top.id = t.topic_id
LEFT JOIN CommitteeMembers cm ON cm.thesis_id = t.id AND cm.professor_id = ?
WHERE t.status='under_review' AND (t.supervisor_id=? OR cm.id IS NOT NULL)
ORDER BY t.id ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $professor_id, $professor_id);
$stmt->execute();
$result = $stmt->get_result();

$theses = [];
while($row = $result->fetch_assoc()){
    $theses[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode(["success"=>true,"theses"=>$theses]);
