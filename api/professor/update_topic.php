<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση."]);
    exit;
}

$professor_id = $_SESSION['user_id'];

// Διαβάζουμε τα δεδομένα JSON από το request body
$data = json_decode(file_get_contents("php://input"), true);

$topic_id = $data['id'] ?? '';
$title = $data['title'] ?? null;
$summary = $data['summary'] ?? null;
$pdf = $data['pdf'] ?? null;

if(!$topic_id) {
    echo json_encode(["success" => false, "message" => "Απαιτείται το ID του θέματος."]);
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

// Δημιουργούμε το SQL δυναμικά για να ενημερώσουμε μόνο τα πεδία που υπάρχουν
$fields = [];
$params = [];
$types = '';

if ($title !== null) {
    $fields[] = "title = ?";
    $params[] = $title;
    $types .= 's';
}
if ($summary !== null) {
    $fields[] = "summary = ?";
    $params[] = $summary;
    $types .= 's';
}
if ($pdf !== null) {
    $fields[] = "pdf_path = ?";
    $params[] = $pdf;
    $types .= 's';
}

if (count($fields) === 0) {
    echo json_encode(["success" => false, "message" => "Δεν υπάρχει πεδίο για ενημέρωση."]);
    exit;
}

$sql = "UPDATE Topics SET " . implode(", ", $fields) . " WHERE id = ?";
$params[] = $topic_id;
$types .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Το θέμα ενημερώθηκε επιτυχώς."]);
} else {
    echo json_encode(["success" => false, "message" => "Σφάλμα κατά την ενημέρωση."]);
}

$conn->close();
?>
