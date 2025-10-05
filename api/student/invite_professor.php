<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

// Μόνο για φοιτητές
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student'){
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

// Έλεγχος αν ο καθηγητής υπάρχει
$stmt = $conn->prepare("SELECT id FROM Users WHERE id=? AND role='professor'");
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows === 0){
    echo json_encode(["success"=>false,"message"=>"Μη έγκυρος καθηγητής."]);
    exit;
}

// Έλεγχος αν ήδη έχει προσκληθεί
$stmt = $conn->prepare("SELECT id FROM CommitteeMembers WHERE thesis_id=? AND professor_id=?");
$stmt->bind_param("ii", $thesis_id, $professor_id);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0){
    echo json_encode(["success"=>false,"message"=>"Ο καθηγητής έχει ήδη προσκληθεί."]);
    exit;
}

// Εισαγωγή πρόσκλησης
$stmt = $conn->prepare("INSERT INTO CommitteeMembers (thesis_id, professor_id, role, status) VALUES (?, ?, 'member', 'invited')");
$stmt->bind_param("ii", $thesis_id, $professor_id);
if(!$stmt->execute()){
    echo json_encode(["success"=>false,"message"=>"Σφάλμα κατά την αποθήκευση πρόσκλησης."]);
    exit;
}

// Έλεγχος αν υπάρχουν 3 αποδοχές
$sql = "SELECT COUNT(*) AS cnt FROM CommitteeMembers WHERE thesis_id=? AND status='accepted'";
$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("i", $thesis_id);
$stmt2->execute();
$result = $stmt2->get_result()->fetch_assoc();

if($result['cnt'] >= 3){
    // Ενεργοποίηση διπλωματικής
    $stmt3 = $conn->prepare("UPDATE Theses SET status='active' WHERE id=?");
    $stmt3->bind_param("i", $thesis_id);
    $stmt3->execute();

    // Απόρριψη όσων έμειναν σε εκκρεμότητα
    $stmt4 = $conn->prepare("UPDATE CommitteeMembers SET status='rejected' WHERE thesis_id=? AND status='invited'");
    $stmt4->bind_param("i", $thesis_id);
    $stmt4->execute();
}

$conn->close();
echo json_encode(["success"=>true,"message"=>"Η πρόσκληση αποθηκεύτηκε επιτυχώς."]);
?>
