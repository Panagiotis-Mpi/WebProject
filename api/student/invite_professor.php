<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='student'){
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$thesis_id = $data['thesis_id'] ?? null;
$professor_id = $data['professor_id'] ?? null;

if(!$thesis_id || !$professor_id){
    echo json_encode(["success"=>false,"message"=>"Απαιτούνται όλα τα πεδία"]);
    exit;
}

// Έλεγχος αν υπάρχει ήδη
$stmt = $conn->prepare("SELECT id FROM CommitteeMembers WHERE thesis_id=? AND professor_id=?");
$stmt->bind_param("ii", $thesis_id, $professor_id);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows>0){
    echo json_encode(["success"=>false,"message"=>"Ο καθηγητής έχει ήδη προσκληθεί."]);
    exit;
}

// Προσθήκη μέλους με status 'invited' και role 'member'
$stmt = $conn->prepare("INSERT INTO CommitteeMembers (thesis_id, professor_id, role, status) VALUES (?, ?, 'member','invited')");
$stmt->bind_param("ii", $thesis_id, $professor_id);
$stmt->execute();

// Έλεγχος αν δύο μέλη έχουν αποδεχθεί -> αλλαγή κατάστασης ΔΕ
$sql = "SELECT COUNT(*) as cnt FROM CommitteeMembers WHERE thesis_id=? AND status='accepted'";
$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("i", $thesis_id);
$stmt2->execute();
$result = $stmt2->get_result()->fetch_assoc();

if($result['cnt'] >= 2){
    $stmt3 = $conn->prepare("UPDATE Theses SET status='active' WHERE id=?");
    $stmt3->bind_param("i", $thesis_id);
    $stmt3->execute();

    // Ακύρωση υπόλοιπων προσκλήσεων
    $stmt4 = $conn->prepare("UPDATE CommitteeMembers SET status='rejected' WHERE thesis_id=? AND status='invited'");
    $stmt4->bind_param("i", $thesis_id);
    $stmt4->execute();
}

echo json_encode(["success"=>true,"message"=>"Πρόσκληση αποθηκεύτηκε."]);
?>
