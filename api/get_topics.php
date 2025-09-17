<?php
// api/get_topics.php
session_start();
require '../db.php';

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος και είναι φοιτητής
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Unauthorized access.']);
    exit();
}

try {
    // Φόρτωση θεμάτων
    $stmt = $pdo->prepare("
        SELECT t.id, t.title, t.summary, t.pdf_path, u.first_name, u.last_name,
               (SELECT COUNT(*) FROM Theses th WHERE th.topic_id = t.id AND th.status IN ('pending','active','under_review')) AS is_taken
        FROM Topics t
        JOIN Users u ON t.creator_id = u.id
        ORDER BY t.id DESC
    ");
    $stmt->execute();
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header("Content-Type: application/json");
    echo json_encode($topics);

} catch (PDOException $e) {
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>