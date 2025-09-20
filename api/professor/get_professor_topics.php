<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση."]);
    exit;
}

$professor_id = $_SESSION['user_id'];

// Επιλέγουμε όλα τα θέματα του καθηγητή
$sql = "SELECT t.id, t.title, t.summary, t.pdf_path,
               th.id AS thesis_id, th.status AS thesis_status,
               u.first_name AS student_first, u.last_name AS student_last
        FROM Topics t
        LEFT JOIN Theses th ON t.id = th.topic_id
        LEFT JOIN Users u ON th.student_id = u.id
        WHERE t.creator_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$result = $stmt->get_result();

$topics = [];

while ($topic = $result->fetch_assoc()) {

    // Παίρνουμε τριμελή επιτροπή μόνο αν υπάρχει διπλωματική
    $committee = [];
    if($topic['thesis_id']) {
        $stmt2 = $conn->prepare("SELECT u.first_name, u.last_name, c.role, c.status
                                 FROM CommitteeMembers c
                                 JOIN Users u ON c.professor_id = u.id
                                 WHERE c.thesis_id = ?");
        $stmt2->bind_param("i", $topic['thesis_id']);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $committee = $res2->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();
    }

    $topics[] = [
        "id" => $topic['id'],
        "title" => $topic['title'],
        "summary" => $topic['summary'],
        "pdf_path" => $topic['pdf_path'],
        "thesis_id" => $topic['thesis_id'],
        "thesis_status" => $topic['thesis_status'],
        "student_first" => $topic['student_first'],
        "student_last" => $topic['student_last'],
        "committee" => $committee
    ];
}

$stmt->close();
$conn->close();

echo json_encode(["success" => true, "topics" => $topics]);
?>
