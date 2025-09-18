<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student'){
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$student_id = $_SESSION['user_id'];

$sql = "
SELECT t.id AS thesis_id, t.status, t.assignment_date, t.library_link, t.review_doc_path,
       tp.title, tp.summary, tp.pdf_path,
       s.first_name AS supervisor_first, s.last_name AS supervisor_last
FROM Theses t
JOIN Topics tp ON t.topic_id = tp.id
JOIN Users s ON t.supervisor_id = s.id
WHERE t.student_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$thesis = $result->fetch_assoc();
if(!$thesis){
    echo json_encode(["success"=>false,"message"=>"Δεν βρέθηκε διπλωματική."]);
    exit;
}

// Πάρε τα μέλη της τριμελούς
$members_sql = "SELECT u.first_name, u.last_name, cm.role, cm.status 
                FROM CommitteeMembers cm
                JOIN Users u ON cm.professor_id = u.id
                WHERE cm.thesis_id = ?";
$stmt2 = $conn->prepare($members_sql);
$stmt2->bind_param("i", $thesis['thesis_id']);
$stmt2->execute();
$members = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

$thesis['committee_members'] = $members;

echo json_encode(["success"=>true,"thesis"=>$thesis]);
?>
