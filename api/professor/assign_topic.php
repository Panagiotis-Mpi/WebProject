<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

// Μόνο για καθηγητές
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$topic_id = $data['topic_id'] ?? null;
$student_am = $data['student_am'] ?? null;
$supervisor_id = $_SESSION['user_id'];

if (!$topic_id || !$student_am) {
    echo json_encode(["success" => false, "message" => "Λείπουν δεδομένα"]);
    exit;
}

// Έλεγχος αν το θέμα ανήκει στον καθηγητή
$sql = "SELECT id FROM Topics WHERE id = ? AND creator_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $topic_id, $supervisor_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Το θέμα δεν ανήκει σε εσάς"]);
    exit;
}
$stmt->close();

// Βρίσκουμε το student_id με βάση το AM
$sql = "SELECT id FROM Users WHERE am = ? AND role = 'student' LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_am);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Δεν βρέθηκε φοιτητής με αυτό το ΑΜ"]);
    exit;
}
$student = $result->fetch_assoc();
$student_id = $student['id'];
$stmt->close();

// Εισαγωγή στη Theses
$sql = "INSERT INTO Theses (topic_id, student_id, supervisor_id, status, assignment_date) 
        VALUES (?, ?, ?, 'active', CURDATE())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $topic_id, $student_id, $supervisor_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Ανάθεση επιτυχής"]);
} else {
    echo json_encode(["success" => false, "message" => "Σφάλμα κατά την ανάθεση"]);
}

$stmt->close();
$conn->close();
