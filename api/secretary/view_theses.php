<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary'){
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$sql = "
SELECT t.id, tp.title, tp.summary, t.status, t.assignment_date,
       CONCAT(u.first_name, ' ', u.last_name) AS supervisor
FROM Theses t
JOIN Topics tp ON t.topic_id = tp.id
JOIN Users u ON t.supervisor_id = u.id
WHERE t.status IN ('active','under_review')
ORDER BY t.assignment_date DESC
";

$result = $conn->query($sql);
$theses = [];

while($row = $result->fetch_assoc()){
    // Υπολογισμός ημερών από ανάθεση
    $days = $row['assignment_date'] ? floor((time() - strtotime($row['assignment_date']))/86400) : null;

    // Λίστα μελών τριμελούς
    $committee_sql = "
        SELECT CONCAT(u.first_name,' ',u.last_name) AS member
        FROM CommitteeMembers cm
        JOIN Users u ON cm.professor_id=u.id
        WHERE cm.thesis_id=?
    ";
    $stmt=$conn->prepare($committee_sql);
    $stmt->bind_param("i",$row['id']);
    $stmt->execute();
    $res=$stmt->get_result();
    $committee=[];
    while($cm=$res->fetch_assoc()){
        $committee[]=$cm['member'];
    }
    $stmt->close();

    $theses[] = [
        "id"=>$row['id'],
        "title"=>$row['title'],
        "summary"=>$row['summary'],
        "status"=>$row['status'],
        "supervisor"=>$row['supervisor'],
        "committee_members"=>$committee,
        "days_since_assignment"=>$days ?? "-"
    ];
}

echo json_encode(["success"=>true,"theses"=>$theses]);
$conn->close();
