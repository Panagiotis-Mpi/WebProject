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

// Παίρνουμε τη διπλωματική του φοιτητή
$sql = "SELECT t.id AS thesis_id, t.status, t.assignment_date, t.library_link, t.review_doc_path,
               tp.title AS topic_title, tp.summary AS topic_summary, tp.pdf_path AS topic_pdf_path
        FROM Theses t
        JOIN Topics tp ON t.topic_id = tp.id
        WHERE t.student_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$thesis = $result->fetch_assoc();

if(!$thesis){
    echo json_encode(["success"=>false,"message"=>"Δεν βρέθηκε διπλωματική."]);
    exit;
}

// Παίρνουμε τα μέλη της επιτροπής
$sql2 = "SELECT u.first_name, u.last_name, cm.role, cm.status
         FROM CommitteeMembers cm
         JOIN Users u ON cm.professor_id = u.id
         WHERE cm.thesis_id = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $thesis['thesis_id']);
$stmt2->execute();
$res2 = $stmt2->get_result();

$committee_members = [];
while($row = $res2->fetch_assoc()){
    $committee_members[] = [
        "first_name"=>$row['first_name'],
        "last_name"=>$row['last_name'],
        "role"=>$row['role'],
        "status"=>$row['status']
    ];
}

$thesis['committee_members'] = $committee_members;

// Υπολογισμός χρόνου από ανάθεση
if($thesis['assignment_date']){
    $assigned = new DateTime($thesis['assignment_date']);
    $now = new DateTime();
    $thesis['days_since_assignment'] = $assigned->diff($now)->days;
} else {
    $thesis['days_since_assignment'] = null;
}

echo json_encode([
    "success"=>true,
    "thesis"=>$thesis
]);
