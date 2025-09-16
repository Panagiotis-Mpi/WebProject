<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php'; // Προσαρμογή σύμφωνα με το δέντρο

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση."]);
    exit;
}

$professor_id = $_SESSION['user_id'];
$topic_id = $_POST['topic-id'] ?? '';
$title = $_POST['topic-title'] ?? '';
$summary = $_POST['topic-summary'] ?? '';
$pdf = $_POST['topic-pdf'] ?? '';

if(!$topic_id || !$title || !$summary) {
    echo json_encode(["success" => false, "message" => "Πρέπει να συμπληρωθούν όλα τα πεδία."]);
    exit;
}

// Έλεγχος αν ο καθηγητής είναι δημιουργός του θέματος
$stmt = $conn->prepare("SELECT id FROM Topics WHERE id = ? AND creator_id = ?");
$stmt->bind_param("ii", $topic_id, $professor_id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Δεν έχετε δικαίωμα να τροποποιήσετε αυτό το θέμα."]);
    exit;
}

// Ενημέρωση θέματος
$stmt = $conn->prepare("UPDATE Topics SET title = ?, summary = ?, pdf_path = ? WHERE id = ?");
$stmt->bind_param("sssi", $title, $summary, $pdf, $topic_id);
if($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Το θέμα ενημερώθηκε επιτυχώς."]);
} else {
    echo json_encode(["success" => false, "message" => "Σφάλμα κατά την ενημέρωση."]);
}

$conn->close();
?>
