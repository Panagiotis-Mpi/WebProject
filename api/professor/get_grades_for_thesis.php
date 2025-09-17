<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor'){
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$thesis_id = $_GET['thesis_id'] ?? null;
$professor_id = $_SESSION['user_id'];

if(!$thesis_id){
    echo json_encode(["success"=>false,"message"=>"Λείπει το ID της διπλωματικής"]);
    exit;
}

// Έλεγχος αν ο καθηγητής συμμετέχει στη ΔΕ
$sql_check="SELECT 1 FROM Theses t
LEFT JOIN CommitteeMembers cm ON cm.thesis_id=t.id AND cm.professor_id=? 
WHERE t.id=? AND (t.supervisor_id=? OR cm.id IS NOT NULL)";
$stmt=$conn->prepare($sql_check);
$stmt->bind_param("iii",$professor_id,$thesis_id,$professor_id);
$stmt->execute();
$res=$stmt->get_result();
if($res->num_rows==0){
    echo json_encode(["success"=>false,"message"=>"Δεν έχετε πρόσβαση σε αυτή τη διπλωματική"]);
    exit;
}
$stmt->close();

// Παίρνουμε τα σκορ του τρέχοντος καθηγητή
$sql="SELECT content_score, organization_score, presentation_score
      FROM Grades
      WHERE thesis_id=? AND professor_id=?";
$stmt=$conn->prepare($sql);
$stmt->bind_param("ii",$thesis_id,$professor_id);
$stmt->execute();
$res=$stmt->get_result();
$grades = null;
if($row=$res->fetch_assoc()){
    $grades = $row;
} else {
    $grades = [
        "content_score" => null,
        "organization_score" => null,
        "presentation_score" => null
    ];
}
$stmt->close();
$conn->close();

echo json_encode(["success"=>true,"grades"=>$grades]);

$stmt->close();
$conn->close();

echo json_encode(["success"=>true,"grades"=>$grades]);
