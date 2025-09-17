<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

// Μόνο για καθηγητές
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$topic_id = trim($data['topic_id'] ?? '');
$student_am = trim($data['student_am'] ?? '');
$supervisor_id = $_SESSION['user_id'];

if (!$topic_id || !$student_am) {
    http_response_code(400);
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
    http_response_code(403);
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
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Δεν βρέθηκε φοιτητής με αυτό το ΑΜ"]);
    exit;
}
$student = $result->fetch_assoc();
$student_id = $student['id'];
$stmt->close();

// Έλεγχος αν ο φοιτητής έχει ήδη ενεργή διπλωματική
$sql = "SELECT id FROM Theses 
        WHERE student_id = ? 
        AND status IN ('pending','active','under_review') 
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "Ο φοιτητής έχει ήδη ενεργή διπλωματική"]);
    exit;
}
$stmt->close();

// Εισαγωγή στη Theses με αρχικό status 'pending'
$sql = "INSERT INTO Theses (topic_id, student_id, supervisor_id, status, assignment_date) 
        VALUES (?, ?, ?, 'pending', CURDATE())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $topic_id, $student_id, $supervisor_id);

if ($stmt->execute()) {
    // Πάρε το ID της νεοδημιουργηθείσας Διπλωματικής
    $thesis_id = $stmt->insert_id;

    // Προσθήκη επιβλέποντα στην CommitteeMembers
    $sql2 = "INSERT INTO CommitteeMembers (thesis_id, professor_id, role, status) 
             VALUES (?, ?, 'supervisor', 'accepted')";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("ii", $thesis_id, $supervisor_id);
    $stmt2->execute();
    $stmt2->close();

    echo json_encode(["success" => true, "message" => "Ανάθεση επιτυχής"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Σφάλμα κατά την ανάθεση"]);
}

$stmt->close();
$conn->close();
?>
