<?php
// api/get_professors.php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Unauthorized access.']);
    exit();
}

$student_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT supervisor_id FROM Theses WHERE student_id = ? AND status IN ('pending','active','under_review') LIMIT 1");
    $stmt->execute([$student_id]);
    $thesis = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$thesis) {
        header("Content-Type: application/json");
        echo json_encode(['error' => 'No active thesis.']);
        exit();
    }
    
    $supervisor_id = $thesis['supervisor_id'];

    $stmt2 = $pdo->prepare("SELECT id, first_name, last_name FROM Users WHERE role='professor' AND id != ?");
    $stmt2->execute([$supervisor_id]);
    $professors = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    header("Content-Type: application/json");
    echo json_encode($professors);

} catch (PDOException $e) {
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>