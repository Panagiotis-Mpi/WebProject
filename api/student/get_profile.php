<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος και φοιτητής
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student'){
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Φέρνουμε τα στοιχεία του φοιτητή
$stmt = $conn->prepare("SELECT email, first_name, last_name, am, phone FROM Users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_assoc()){
    echo json_encode([
        "success" => true,
        "user" => $row
    ]);
} else {
    echo json_encode(["success"=>false,"message"=>"Δεν βρέθηκαν στοιχεία χρήστη"]);
}

$stmt->close();
$conn->close();
?>
