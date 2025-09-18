<?php
session_start();
require_once("../../config/db.php");

// Έλεγχος αν είναι φοιτητής
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student'){
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT email, first_name, last_name, am, phone FROM Users WHERE id=? AND role='student'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if($user){
    echo json_encode(["success" => true, "user" => $user]);
} else {
    echo json_encode(["success" => false, "message" => "Δεν βρέθηκαν στοιχεία"]);
}
