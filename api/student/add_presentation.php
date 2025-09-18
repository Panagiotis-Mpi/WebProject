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
$date = $data['date'] ?? '';
$time = $data['time'] ?? '';
$mode = $data['mode'] ?? '';
$location = trim($data['location'] ?? '');

if(!$date || !$time || !in_array($mode, ['in_person','online']) || !$location){
    echo json_encode(["success"=>false,"message"=>"Παρακαλώ συμπληρώστε όλα τα πεδία σωστά."]);
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

$stmt2 = $conn->prepare("INSERT INTO Presentations (thesis_id, date, time, mode, location) VALUES (?, ?, ?, ?, ?)");
$stmt2->bind_param("issss", $thesis_id, $date, $time, $mode, $location);
$stmt2->execute();

echo json_encode(["success"=>true,"message"=>"Η παρουσίαση καταχωρήθηκε επιτυχώς."]);
