<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

// Έλεγχος session: μόνο καθηγητές
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$title = trim($_POST['title'] ?? '');
$summary = trim($_POST['summary'] ?? '');
$pdf_path = trim($_POST['pdf_path'] ?? '') ?: null;

if (empty($title) || empty($summary)) {
    echo json_encode(["success" => false, "message" => "Συμπληρώστε όλα τα υποχρεωτικά πεδία"]);
    exit;
}

$creator_id = $_SESSION['user_id'];

// Prepared statement για αποφυγή SQL injection
$sql = "INSERT INTO Topics (title, summary, pdf_path, creator_id) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $title, $summary, $pdf_path, $creator_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Το θέμα δημιουργήθηκε"]);
} else {
    echo json_encode(["success" => false, "message" => "Σφάλμα κατά την αποθήκευση"]);
}

$stmt->close();
$conn->close();
