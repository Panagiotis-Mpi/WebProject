<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor'){
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$thesis_id = $data['thesis_id'] ?? null;
$content_score = $data['content_score'] ?? null;
$organization_score = $data['organization_score'] ?? null;
$presentation_score = $data['presentation_score'] ?? null;
$professor_id = $_SESSION['user_id'];

if(!$thesis_id || $content_score===null || $organization_score===null || $presentation_score===null){
    echo json_encode(["success"=>false,"message"=>"Λείπουν δεδομένα"]);
    exit;
}

// Έλεγχος αν οι βαθμοί είναι μεταξύ 0 και 10
if($content_score < 0 || $content_score > 10 ||
   $organization_score < 0 || $organization_score > 10 ||
   $presentation_score < 0 || $presentation_score > 10){
    echo json_encode(["success"=>false,"message"=>"Οι βαθμοί πρέπει να είναι μεταξύ 0 και 10"]);
    exit;
}

// Έλεγχος αν ο καθηγητής συμμετέχει στη ΔΕ (ως επιβλέπων ή μέλος τριμελούς)
$sql_check="SELECT 1 FROM Theses t
LEFT JOIN CommitteeMembers cm ON cm.thesis_id=t.id AND cm.professor_id=? 
WHERE t.id=? AND (t.supervisor_id=? OR cm.id IS NOT NULL)";
$stmt=$conn->prepare($sql_check);
$stmt->bind_param("iii",$professor_id,$thesis_id,$professor_id);
$stmt->execute();
$res=$stmt->get_result();
if($res->num_rows==0){
    echo json_encode(["success"=>false,"message"=>"Δεν έχετε δικαίωμα αξιολόγησης αυτής της διπλωματικής"]);
    exit;
}
$stmt->close();

// Καταχώρηση ή ενημέρωση βαθμολογίας
$sql="INSERT INTO Grades (thesis_id, professor_id, content_score, organization_score, presentation_score)
      VALUES (?, ?, ?, ?, ?)
      ON DUPLICATE KEY UPDATE 
          content_score=VALUES(content_score),
          organization_score=VALUES(organization_score),
          presentation_score=VALUES(presentation_score)";
$stmt=$conn->prepare($sql);
$stmt->bind_param("iiidd",$thesis_id,$professor_id,$content_score,$organization_score,$presentation_score);

if($stmt->execute()){
    echo json_encode(["success"=>true,"message"=>"Ο βαθμός καταχωρήθηκε επιτυχώς"]);
}else{
    echo json_encode(["success"=>false,"message"=>"Σφάλμα κατά την καταχώρηση"]);
}

$stmt->close();
$conn->close();
