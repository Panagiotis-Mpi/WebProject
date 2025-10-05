<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

// Έλεγχος ρόλου
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary'){
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

// Λήψη δεδομένων
$data = json_decode(file_get_contents("php://input"), true);
$thesis_id = $data['thesis_id'] ?? null;
$new_status = $data['status'] ?? null;
$gs_protocol = $data['gs_protocol'] ?? null;
$cancel_reason = $data['cancel_reason'] ?? null;

if(!$thesis_id || !$new_status){
    echo json_encode(["success"=>false,"message"=>"Λείπουν δεδομένα."]);
    exit;
}

// Ανάκτηση τρέχουσας κατάστασης
$stmt = $conn->prepare("SELECT status, library_link FROM Theses WHERE id=?");
$stmt->bind_param("i", $thesis_id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows === 0){
    echo json_encode(["success"=>false,"message"=>"Η ΔΕ δεν βρέθηκε."]);
    exit;
}
$thesis = $result->fetch_assoc();
$current_status = $thesis['status'];
$library_link = $thesis['library_link'];

// Έλεγχος επιτρεπόμενων αλλαγών
$allowed = false;

if($current_status === 'active'){
    if($new_status === 'cancelled' && $gs_protocol && $cancel_reason){
        $allowed = true;
    } elseif($new_status === 'under_review' && $gs_protocol){
        $allowed = true;
    }
} elseif($current_status === 'under_review'){
    if($new_status === 'completed' && !empty($library_link)){
        $allowed = true;
    }
}

if(!$allowed){
    echo json_encode(["success"=>false,"message"=>"Η αλλαγή κατάστασης δεν επιτρέπεται."]);
    exit;
}

// Ενημέρωση Theses
$update_stmt = $conn->prepare("
    UPDATE Theses SET status=?, gs_protocol=?, gs_cancel_reason=? WHERE id=?
");
$update_stmt->bind_param("sssi", $new_status, $gs_protocol, $cancel_reason, $thesis_id);
$update_stmt->execute();

// Καταχώρηση στο StatusHistory
$history_stmt = $conn->prepare("
    INSERT INTO StatusHistory (thesis_id, status, changed_by) VALUES (?, ?, ?)
");
$history_stmt->bind_param("isi", $thesis_id, $new_status, $_SESSION['user_id']);
$history_stmt->execute();

$update_stmt->close();
$history_stmt->close();
$conn->close();

echo json_encode(["success"=>true,"message"=>"Η κατάσταση ενημερώθηκε επιτυχώς."]);
?>
