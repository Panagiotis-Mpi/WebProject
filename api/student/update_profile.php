<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

// Έλεγχος αν είναι φοιτητής
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student'){
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

if(!$data){
    echo json_encode(["success"=>false,"message"=>"Δεν εστάλησαν δεδομένα"]);
    exit;
}

// Φτιάχνουμε δυναμικά το query μόνο με τα πεδία που υπάρχουν
$fields = [];
$params = [];
$types = "";

// Email
if(isset($data['email']) && $data['email'] !== ""){
    $fields[] = "email = ?";
    $params[] = $data['email'];
    $types .= "s";
}

// First name
if(isset($data['first_name']) && $data['first_name'] !== ""){
    $fields[] = "first_name = ?";
    $params[] = $data['first_name'];
    $types .= "s";
}

// Last name
if(isset($data['last_name']) && $data['last_name'] !== ""){
    $fields[] = "last_name = ?";
    $params[] = $data['last_name'];
    $types .= "s";
}

// AM 
if(isset($data['am'])){
    $fields[] = "am = ?";
    $params[] = $data['am'];
    $types .= "s";
}

// Phone
if(isset($data['phone'])){
    $fields[] = "phone = ?";
    $params[] = $data['phone'];
    $types .= "s";
}

if(empty($fields)){
    echo json_encode(["success"=>false,"message"=>"Δεν επιλέχθηκαν πεδία για ενημέρωση"]);
    exit;
}

$sql = "UPDATE Users SET " . implode(", ", $fields) . " WHERE id = ? AND role = 'student'";
$params[] = $user_id;
$types .= "i";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if($stmt->execute()){
    echo json_encode(["success"=>true,"message"=>"Τα στοιχεία ενημερώθηκαν επιτυχώς"]);
} else {
    echo json_encode(["success"=>false,"message"=>"Σφάλμα κατά την ενημέρωση"]);
}

$stmt->close();
$conn->close();
?>
