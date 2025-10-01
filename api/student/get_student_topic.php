<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

// Έλεγχος login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student'){
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$student_id = $_SESSION['user_id'];

// Βρίσκουμε τη διπλωματική του φοιτητή
$sql = "SELECT t.id as thesis_id, t.status, t.assignment_date,
               tp.title, tp.summary, tp.pdf_path, t.review_doc_path
        FROM Theses t
        JOIN Topics tp ON t.topic_id = tp.id
        WHERE t.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    echo json_encode(["success"=>false,"message"=>"Δεν έχετε ανατεθεί σε καμία διπλωματική."]);
    exit;
}

$thesis = $result->fetch_assoc();
$thesis_id = $thesis['thesis_id'];

// Υπολογισμός ημερών από ανάθεση
$days_since = null;
if(!empty($thesis['assignment_date'])){
    $days_since = (new DateTime())->diff(new DateTime($thesis['assignment_date']))->days;
}

// Παίρνουμε τα μέλη της τριμελούς
$sql2 = "SELECT u.first_name, u.last_name, c.role, c.status
         FROM CommitteeMembers c
         JOIN Users u ON c.professor_id = u.id
         WHERE c.thesis_id = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $thesis_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
$committee = $res2->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$stmt2->close();
$conn->close();

echo json_encode([
    "success"=>true,
    "thesis_id"=>$thesis_id,
    "topic"=>[
        "title"=>$thesis['title'],
        "summary"=>$thesis['summary'],
        "pdf_path"=>$thesis['pdf_path']
    ],
    "thesis"=>[
        "status"=>$thesis['status'],
        "assignment_date"=>$thesis['assignment_date'],
        "review_doc_path"=>$thesis['review_doc_path']
    ],
    "days_since_assignment"=>$days_since,
    "committee"=>$committee
]);
