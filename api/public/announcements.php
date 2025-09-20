<?php
require __DIR__ . '/../../db_connection.php';

// Παράμετροι
$start_date = $_GET['start'] ?? null;
$end_date = $_GET['end'] ?? null;
$format = strtolower($_GET['format'] ?? 'json');

// Έλεγχος μορφής ημερομηνίας
$date_regex = '/^\d{4}-\d{2}-\d{2}$/';
if (($start_date && !preg_match($date_regex, $start_date)) || ($end_date && !preg_match($date_regex, $end_date))) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Μη έγκυρη μορφή ημερομηνίας. Χρησιμοποιήστε YYYY-MM-DD.']);
    exit;
}

// Default ημερομηνίες
if (!$start_date) $start_date = '2000-01-01';
if (!$end_date) $end_date = date('Y-m-d');

// SQL για τις ανακοινώσεις
$sql = "SELECT a.id, a.thesis_id, a.title, a.text, a.start_date, a.end_date, tp.title AS thesis_title
        FROM Announcements a
        JOIN Theses t ON a.thesis_id = t.id
        JOIN Topics tp ON t.topic_id = tp.id
        WHERE a.start_date BETWEEN ? AND ?
        ORDER BY a.start_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$announcements = [];
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}

$stmt->close();
$conn->close();

// Επιστροφή JSON
if ($format === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'count' => count($announcements),
        'announcements' => $announcements
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Επιστροφή XML
header('Content-Type: text/xml; charset=utf-8');
$xml = new SimpleXMLElement('<announcements/>');
foreach ($announcements as $a) {
    $item = $xml->addChild('announcement');
    foreach ($a as $key => $value) {
        $item->addChild($key, htmlspecialchars($value));
    }
}
echo $xml->asXML();
