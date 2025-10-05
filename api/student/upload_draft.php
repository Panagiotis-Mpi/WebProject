<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student'){
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

if(!isset($_FILES['draft'])){
    echo json_encode(["success"=>false,"message"=>"Δεν επιλέχθηκε αρχείο."]);
    exit;
}

$student_id = $_SESSION['user_id'];

// Η διπλωματική του φοιτητή
$sql = "SELECT id FROM Theses WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$student_id);
$stmt->execute();
$res = $stmt->get_result();
$thesis = $res->fetch_assoc();
if(!$thesis){
    echo json_encode(["success"=>false,"message"=>"Δεν βρέθηκε διπλωματική."]);
    exit;
}

$thesis_id = $thesis['id'];

// Ανέβασμα αρχείου
$upload_dir = __DIR__ . '/../../uploads/drafts/';
if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$filename = time() . '_' . basename($_FILES['draft']['name']);
$target_path = $upload_dir . $filename;

if(move_uploaded_file($_FILES['draft']['tmp_name'], $target_path)){
    $stmt2 = $conn->prepare("UPDATE Theses SET review_doc_path=? WHERE id=?");
    $stmt2->bind_param("si", $filename, $thesis_id);
    $stmt2->execute();
    echo json_encode(["success"=>true,"message"=>"Το draft ανέβηκε επιτυχώς."]);
}else{
    echo json_encode(["success"=>false,"message"=>"Σφάλμα κατά το ανέβασμα."]);
}
