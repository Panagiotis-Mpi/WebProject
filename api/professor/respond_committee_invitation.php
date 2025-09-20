<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

// Έλεγχος role
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

// Ενημέρωση της πρόσκλησης καθηγητή
$sql = "UPDATE CommitteeMembers SET status = ? WHERE id = ? AND professor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $status, $invitation_id, $_SESSION['user_id']);

if ($stmt->execute()) {

    // Έλεγχος πόσοι καθηγητές έχουν αποδεχτεί την ίδια διπλωματική
    $sql_check = "SELECT COUNT(*) AS accepted_count, thesis_id 
                  FROM CommitteeMembers 
                  WHERE thesis_id = (SELECT thesis_id FROM CommitteeMembers WHERE id = ?) 
                    AND status = 'accepted'";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $invitation_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();
    $accepted_count = $row_check['accepted_count'];
    $thesis_id = $row_check['thesis_id'];

    // Αν ακριβώς 3 καθηγητές έχουν αποδεχτεί
    if ($accepted_count === 3) {
        // Αλλάζουμε την κατάσταση της διπλωματικής σε ενεργή
        $sql_update_thesis = "UPDATE Theses SET status = 'active' WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update_thesis);
        $stmt_update->bind_param("i", $thesis_id);
        $stmt_update->execute();
        $stmt_update->close();

        // Αυτόματη απόρριψη όλων των άλλων προσκλήσεων που παραμένουν "invited"
        $sql_reject_others = "UPDATE CommitteeMembers 
                              SET status = 'rejected' 
                              WHERE thesis_id = ? AND status = 'invited'";
        $stmt_reject = $conn->prepare($sql_reject_others);
        $stmt_reject->bind_param("i", $thesis_id);
        $stmt_reject->execute();
        $stmt_reject->close();
    }

    $stmt_check->close();
    echo json_encode(["success" => true, "message" => "Η απάντηση καταχωρήθηκε"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Σφάλμα κατά την ενημέρωση"]);
}

$stmt->close();
$conn->close();
?>
