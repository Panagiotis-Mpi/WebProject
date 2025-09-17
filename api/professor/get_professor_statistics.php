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

// Διπλωματικές ως επιβλέπων
$sql_supervisor = "
SELECT t.id, t.status, t.assignment_date, AVG(g.final_grade) AS avg_grade,
       DATEDIFF(CURDATE(), t.assignment_date) AS duration_days
FROM Theses t
LEFT JOIN Grades g ON g.thesis_id = t.id
WHERE t.supervisor_id = ?
GROUP BY t.id";
$stmt = $conn->prepare($sql_supervisor);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$result = $stmt->get_result();
$supervisorTheses = [];
while ($row = $result->fetch_assoc()) {
    $supervisorTheses[] = [
        'status' => $row['status'],
        'duration_days' => (int)$row['duration_days'],
        'avg_grade' => $row['avg_grade'] ? (float)$row['avg_grade'] : null
    ];
}
$stmt->close();

// Διπλωματικές ως μέλος τριμελούς
$sql_member = "
SELECT t.id, t.status, t.assignment_date, AVG(g.final_grade) AS avg_grade,
       DATEDIFF(CURDATE(), t.assignment_date) AS duration_days
FROM Theses t
JOIN CommitteeMembers cm ON cm.thesis_id = t.id AND cm.professor_id = ?
LEFT JOIN Grades g ON g.thesis_id = t.id
WHERE cm.role='member'
GROUP BY t.id";
$stmt = $conn->prepare($sql_member);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$result = $stmt->get_result();
$memberTheses = [];
while ($row = $result->fetch_assoc()) {
    $memberTheses[] = [
        'status' => $row['status'],
        'duration_days' => (int)$row['duration_days'],
        'avg_grade' => $row['avg_grade'] ? (float)$row['avg_grade'] : null
    ];
}
$stmt->close();
$conn->close();

// Συνάρτηση υπολογισμού μέσου όρου
function calculateStats($theses) {
    $completed = array_filter($theses, fn($t) => $t['status'] === 'completed');
    $durations = array_map(fn($t) => $t['duration_days'], $completed);
    $grades = array_filter(array_map(fn($t) => $t['avg_grade'], $completed), fn($g) => $g !== null);
    return [
        'avg_duration' => count($durations) ? round(array_sum($durations)/count($durations),2) : 0,
        'avg_grade' => count($grades) ? round(array_sum($grades)/count($grades),2) : 0,
        'total_count' => count($theses)
    ];
}

echo json_encode([
    'success' => true,
    'supervisor' => calculateStats($supervisorTheses),
    'member' => calculateStats($memberTheses)
]);
?>
