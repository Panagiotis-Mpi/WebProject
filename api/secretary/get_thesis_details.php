<?php
// api/get_thesis_details.php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Unauthorized access.']);
    exit();
}

$student_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT t.id as thesis_id, t.status, tp.title, u.first_name as sup_first, u.last_name as sup_last
        FROM Theses t
        JOIN Topics tp ON t.topic_id = tp.id
        JOIN Users u ON t.supervisor_id = u.id
        WHERE t.student_id = ?
    ");
    $stmt->execute([$student_id]);
    $thesis = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$thesis) {
        header("Content-Type: application/json");
        echo json_encode(['error' => 'No thesis assigned.']);
        exit();
    }

    $thesis_id = $thesis['thesis_id'];

    $stmt_notes = $pdo->prepare("
        SELECT n.content, n.created_at, u.first_name, u.last_name
        FROM Notes n
        JOIN Users u ON n.creator_id = u.id
        WHERE n.thesis_id = ?
        ORDER BY n.created_at DESC
    ");
    $stmt_notes->execute([$thesis_id]);
    $notes = $stmt_notes->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'thesis' => $thesis,
        'notes' => $notes
    ];

    header("Content-Type: application/json");
    echo json_encode($response);

} catch (PDOException $e) {
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
