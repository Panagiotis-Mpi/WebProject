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

$sql = "
SELECT t.id, t.topic_id, top.title AS topic_title, u.first_name, u.last_name
FROM Theses t
JOIN Users u ON u.id = t.student_id
JOIN Topics top ON top.id = t.topic_id
WHERE t.status='under_review' 
AND (t.supervisor_id=? OR t.id IN (
    SELECT thesis_id FROM CommitteeMembers WHERE professor_id=? AND role='member'
))
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii",$professor_id,$professor_id);
$stmt->execute();
$result = $stmt->get_result();
$theses=[];
while($row=$result->fetch_assoc()){
    $theses[]=$row;
}
$stmt->close();
$conn->close();

echo json_encode(["success"=>true,"theses"=>$theses]);
?>
