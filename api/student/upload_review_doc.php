<?php
session_start();
require __DIR__ . '/../../db_connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student'){
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$thesis_id = $_POST['thesis_id'] ?? null;
if(!$thesis_id || !isset($_FILES['review_doc'])){
    echo json_encode(["success"=>false,"message"=>"Απαιτούνται όλα τα πεδία"]);
    exit;
}

$uploadDir = '../../uploads/review_docs/';
if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$filename = basename($_FILES['review_doc']['name']);
$targetFile = $uploadDir . time() . "_" . $filename;

if(move_uploaded_file($_FILES['review_doc']['tmp_name'], $targetFile)){
    $stmt = $conn->prepare("UPDATE Theses SET review_doc_path=? WHERE id=? AND student_id=?");
    $stmt->bind_param("sii", $targetFile, $thesis_id, $_SESSION['user_id']);
    $stmt->execute();
    echo json_encode(["success"=>true,"message"=>"Αρχείο ανέβηκε επιτυχώς"]);
}else{
    echo json_encode(["success"=>false,"message"=>"Σφάλμα κατά την αποθήκευση του αρχείου"]);
}
?>
