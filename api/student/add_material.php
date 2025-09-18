<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student'){
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$material_link = trim($data['link'] ?? '');

if(!$material_link){
    echo json_encode(["success"=>false,"message"=>"Ο σύνδεσμος δεν μπορεί να είναι κενός."]);
    exit;
}

$student_id = $_SESSION['user_id'];

$sql = "SELECT id FROM Theses WHERE student_id=?";
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

$stmt2 = $conn->prepare("INSERT INTO Notes (thesis_id, creator_id, content) VALUES (?, ?, ?)");
$stmt2->bind_param("iis", $thesis_id, $student_id, $material_link);
$stmt2->execute();

echo json_encode(["success"=>true,"message"=>"Ο σύνδεσμος προστέθηκε επιτυχώς."]);
