<?php
session_start();
require_once("../../config/db.php");

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student'){
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $_SESSION['user_id'];

// Χτίζουμε δυναμικά το query ανάλογα με το αν άλλαξε password
if(!empty($data['password'])){
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE Users SET first_name=?, last_name=?, phone=?, password=? WHERE id=? AND role='student'");
    $stmt->bind_param("ssssi", $data['first_name'], $data['last_name'], $data['phone'], $hashed_password, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE Users SET first_name=?, last_name=?, phone=? WHERE id=? AND role='student'");
    $stmt->bind_param("sssi", $data['first_name'], $data['last_name'], $data['phone'], $user_id);
}

if($stmt->execute()){
    echo json_encode(["success" => true, "message" => "Το προφίλ ενημερώθηκε με επιτυχία"]);
} else {
    echo json_encode(["success" => false, "message" => "Σφάλμα κατά την ενημέρωση"]);
}
