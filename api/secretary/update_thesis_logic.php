<?php
// secretary/update_thesis_logic.php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$thesis_id = $data['thesis_id'] ?? null;
$status = $data['status'] ?? null;
$gs_number_active = $data['gs_number_active'] ?? null;
$gs_number_cancelled = $data['gs_number_cancelled'] ?? null;
$cancellation_reason = $data['cancellation_reason'] ?? null;
$grade = $data['grade'] ?? null;

if (!$thesis_id || !$status) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input.']);
    exit();
}

$message = 'Η διπλωματική ενημερώθηκε.';
$update_sql = "UPDATE Theses SET status = ?";
$params = [$status];

switch ($status) {
    case 'active':
        if (!$gs_number_active) {
            http_response_code(400);
            echo json_encode(['error' => 'GS Number is required for Active status.']);
            exit();
        }
        $update_sql .= ", gs_active_number = ?";
        $params[] = $gs_number_active;
        break;
    case 'cancelled':
        if (!$gs_number_cancelled || !$cancellation_reason) {
            http_response_code(400);
            echo json_encode(['error' => 'GS Number and reason are required for Cancelled status.']);
            exit();
        }
        $update_sql .= ", gs_cancelled_number = ?, cancellation_reason = ?";
        $params[] = $gs_number_cancelled;
        $params[] = $cancellation_reason;
        break;
    case 'completed':
        if (!$grade) {
            http_response_code(400);
            echo json_encode(['error' => 'Grade is required for Completed status.']);
            exit();
        }
        // Προσθέτουμε τον βαθμό και ελέγχουμε για τον σύνδεσμο της βιβλιοθήκης (Νημερτής).
        $stmt_check_nemertes = $pdo->prepare("SELECT nemertes_link FROM Theses WHERE id = ?");
        $stmt_check_nemertes->execute([$thesis_id]);
        $thesis_to_complete = $stmt_check_nemertes->fetch(PDO::FETCH_ASSOC);

        if (!$thesis_to_complete || empty($thesis_to_complete['nemertes_link'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nemertes link must be submitted by student first.']);
            exit();
        }
        $update_sql .= ", grade = ?";
        $params[] = $grade;
        break;
}

$update_sql .= " WHERE id = ?";
$params[] = $thesis_id;

try {
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute($params);
    echo json_encode(['success' => true, 'message' => $message]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
