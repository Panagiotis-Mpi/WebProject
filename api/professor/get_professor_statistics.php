<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

$professor_id = $_SESSION['user_id'];

// Στατιστικά για διπλωματικές που επιβλέπει ή είναι μέλος τριμελούς
$sql = "SELECT t.id, t.status, t.assignment_date, t.library_link, 
               AVG(g.final_grade) AS avg_grade,
               DATEDIFF(CURDATE(), t.assignment_date) AS duration_days
        FROM Theses t
        LEFT JOIN CommitteeMembers cm ON cm.thesis_id = t.id AND cm.professor_id = ?
        LEFT JOIN Grades g ON g.thesis_id = t.id
        WHERE t.supervisor_id = ? OR cm.professor_id = ?
        GROUP BY t.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $professor_id, $professor_id, $professor_id);
$stmt->execute();
$result = $stmt->get_result();

$theses = [];
while ($row = $result->fetch_assoc()) {
    $theses[] = [
        'id' => $row['id'],
        'status' => $row['status'],
        'duration_days' => (int)$row['duration_days'],
        'avg_grade' => $row['avg_grade'] ? (float)$row['avg_grade'] : null
    ];
}

$completedTheses = array_filter($theses, fn($t) => $t['status'] === 'completed');
$durations = array_map(fn($t) => $t['duration_days'], $completedTheses);
$grades = array_filter(array_map(fn($t) => $t['avg_grade'], $completedTheses), fn($g) => $g !== null);
$total_count = count($theses);

$avg_duration = count($durations) ? array_sum($durations)/count($durations) : 0;
$avg_grade = count($grades) ? array_sum($grades)/count($grades) : 0;

echo json_encode([
    "success" => true,
    "avg_duration" => round($avg_duration,2),
    "avg_grade" => round($avg_grade,2),
    "total_count" => $total_count
]);

$stmt->close();
$conn->close();
?>
