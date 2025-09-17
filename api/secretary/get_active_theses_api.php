<?php
// api/get_active_theses_api.php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access.']);
    exit();
}

try {
    $stmt = $pdo->query("
        SELECT t.id, tp.title, u.first_name, u.last_name, t.status
        FROM Theses t
        JOIN Topics tp ON t.topic_id = tp.id
        JOIN Users u ON t.student_id = u.id
        WHERE t.status IN ('active','under_review')
        ORDER BY t.id DESC
    ");
    $theses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($theses);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
