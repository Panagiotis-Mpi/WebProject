<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='student'){
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$sql = "SELECT id, first_name, last_name FROM Users WHERE role='professor'";
$result = $conn->query($sql);

$professors = [];
while($row = $result->fetch_assoc()){
    $professors[] = $row;
}

echo json_encode(["success"=>true,"professors"=>$professors]);
?>
