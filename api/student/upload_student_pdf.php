<?php
session_start();
require __DIR__ . '/../../db_connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student'){
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

if(!isset($_FILES['pdf'])){
    echo json_encode(["success"=>false,"message"=>"Δεν επιλέχθηκε αρχείο"]);
    exit;
}

$student_id = $_SESSION['user_id'];
$file = $_FILES['pdf'];

// Έλεγχος τύπου
if($file['type'] !== 'application/pdf'){
    echo json_encode(["success"=>false,"message"=>"Μόνο PDF αρχεία επιτρέπονται"]);
    exit;
}

// Φάκελος αποθήκευσης
$uploadDir = __DIR__ . '/../../uploads/student_docs/';
if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Δημιουργία μοναδικού ονόματος
$filename = 'student_'.$student_id.'_'.time().'.pdf';
$filepath = $uploadDir . $filename;

// Μετακίνηση του αρχείου
if(move_uploaded_file($file['tmp_name'], $filepath)){
    // Ενημέρωση στη βάση
    $sql = "UPDATE Theses SET review_doc_path=? WHERE student_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $filename, $student_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo json_encode(["success"=>true]);
} else {
    echo json_encode(["success"=>false,"message"=>"Σφάλμα κατά την αποθήκευση του αρχείου"]);
}
