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

// Ανάκτηση ΔΕ σε κατάσταση active ή under_review
$query = "SELECT t.id, t.status, tp.title 
          FROM Theses t
          JOIN Topics tp ON t.topic_id = tp.id
          WHERE t.status IN ('active','under_review')
          ORDER BY tp.title";

$result = $conn->query($query);
$theses = [];

while($row = $result->fetch_assoc()){
    $theses[] = [
        "id" => $row['id'],
        "status" => $row['status'],
        "title" => $row['title']
    ];
}

$conn->close();

echo json_encode(["success"=>true, "theses"=>$theses]);
?>
