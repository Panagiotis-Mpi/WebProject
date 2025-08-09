<?php
// Τοποθεσία: /diplomatiki/public/presentations_feed.php
require '../db.php';  // Φόρτωση του db.php από root φάκελο

// Λήψη παραμέτρων URL
$format = isset($_GET['format']) && in_array($_GET['format'], ['json', 'xml']) 
          ? $_GET['format'] : 'json';

$from_date = isset($_GET['from']) ? $_GET['from'] : date('Y-m-d');
$to_date = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d', strtotime('+1 month'));

// Ερώτημα για παρουσιάσεις
$query = "SELECT 
         p.date, p.time, p.mode, p.location,
         t.topic_id, top.title,
         u.first_name, u.last_name, u.am
         FROM Presentations p
         JOIN Theses t ON p.thesis_id = t.id
         JOIN Topics top ON t.topic_id = top.id
         JOIN Users u ON t.student_id = u.id
         WHERE p.date BETWEEN :from_date AND :to_date
         ORDER BY p.date, p.time";

$stmt = $pdo->prepare($query);
$stmt->execute([':from_date' => $from_date, ':to_date' => $to_date]);
$presentations = $stmt->fetchAll();

// Δημιουργία εξόδου
if ($format === 'xml') {
    header('Content-Type: application/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<presentations>';
    foreach ($presentations as $p) {
        echo '<presentation>';
        echo '<date>' . htmlspecialchars($p['date']) . '</date>';
        echo '<time>' . htmlspecialchars($p['time']) . '</time>';
        echo '<mode>' . htmlspecialchars($p['mode']) . '</mode>';
        echo '<location>' . htmlspecialchars($p['location']) . '</location>';
        echo '<title>' . htmlspecialchars($p['title']) . '</title>';
        echo '<student>' . htmlspecialchars($p['first_name'] . ' ' . $p['last_name'] . ' (' . $p['am'] . ')') . '</student>';
        echo '</presentation>';
    }
    echo '</presentations>';
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'metadata' => [
            'generated_at' => date('c'),
            'date_range' => ['from' => $from_date, 'to' => $to_date],
            'count' => count($presentations)
        ],
        'presentations' => $presentations
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}