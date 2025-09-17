<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$invitation_id = $data['invitation_id'] ?? null;
$action = $data['action'] ?? null; // accept ή reject

if (!$invitation_id || !in_array($action, ['accept', 'reject'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Λείπουν ή λανθασμένα δεδομένα"]);
    exit;
}

$status = $action === 'accept' ? 'accepted' : 'rejected';

$sql = "UPDATE CommitteeMembers SET status = ? WHERE id = ? AND professor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $status, $invitation_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Η απάντηση καταχωρήθηκε"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Σφάλμα κατά την ενημέρωση"]);
}

$stmt->close();
$conn->close();
?>
