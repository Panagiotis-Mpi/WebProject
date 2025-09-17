<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

// Έλεγχος αν είναι γραμματεία
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary'){
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

// Επιλογή μόνο των διπλωματικών σε "active" ή "under_review"
$sql = "SELECT t.id, tp.title, tp.summary, t.status, t.assignment_date
        FROM Theses t
        JOIN Topics tp ON t.topic_id = tp.id
        WHERE t.status IN ('active', 'under_review')";
$result = $conn->query($sql);

$theses = [];
while($row = $result->fetch_assoc()){
    // Υπολογισμός ημερών από την ανάθεση (αν υπάρχει assignment_date)
    $days_passed = null;
    if(!empty($row['assignment_date'])){
        $date1 = new DateTime($row['assignment_date']);
        $date2 = new DateTime();
        $interval = $date1->diff($date2);
        $days_passed = $interval->days;
    }

    // Φέρνουμε τα μέλη της τριμελούς
    $committee_sql = "SELECT u.first_name, u.last_name, cm.role
                      FROM CommitteeMembers cm
                      JOIN Users u ON cm.professor_id = u.id
                      WHERE cm.thesis_id = ?";
    $stmt = $conn->prepare($committee_sql);
    $stmt->bind_param("i", $row['id']);
    $stmt->execute();
    $committee_res = $stmt->get_result();
    $committee = [];
    while($c = $committee_res->fetch_assoc()){
        $committee[] = $c;
    }
    $stmt->close();

    $theses[] = [
        "id" => $row['id'],
        "title" => $row['title'],
        "summary" => $row['summary'],
        "status" => $row['status'],
        "days_passed" => $days_passed,
        "committee" => $committee
    ];
}

echo json_encode(["success"=>true,"data"=>$theses], JSON_UNESCAPED_UNICODE);
$conn->close();
?>
