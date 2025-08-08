<?php
// professor_invitations.php - Προβολή προσκλήσεων σε τριμελείς

$conn = new PDO('mysql:host=localhost;dbname=diplomatiki;charset=utf8mb4', 'root', '');
$professor_id = 1;

$stmt = $conn->prepare("
    SELECT c.status, t.title, 
           u1.first_name as student_fname, u1.last_name as student_lname,
           u2.first_name as supervisor_fname, u2.last_name as supervisor_lname
    FROM CommitteeMembers c
    JOIN Theses th ON c.thesis_id = th.id
    JOIN Topics t ON th.topic_id = t.id
    JOIN Users u1 ON th.student_id = u1.id
    JOIN Users u2 ON th.supervisor_id = u2.id
    WHERE c.professor_id1 = ? OR c.professor_id2 = ?
");
$stmt->execute([$professor_id, $professor_id]);
$invitations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Προσκλήσεις Τριμελούς</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .invitation { border: 1px solid #ddd; padding: 15px; margin: 10px 0; }
        .accepted { background-color: #e6ffe6; }
        .pending { background-color: #fffae6; }
    </style>
</head>
<body>
    <h1>Προσκλήσεις Συμμετοχής σε Τριμελείς</h1>
    <?php foreach ($invitations as $inv): ?>
        <div class="invitation <?= $inv['status'] ?>">
            <h3>Θέμα: <?= htmlspecialchars($inv['title']) ?></h3>
            <p>Φοιτητής: <?= htmlspecialchars($inv['student_fname'] . ' ' . $inv['student_lname']) ?></p>
            <p>Επιβλέπων: <?= htmlspecialchars($inv['supervisor_fname'] . ' ' . $inv['supervisor_lname']) ?></p>
            <p>Κατάσταση: <?= $inv['status'] ?></p>
        </div>
    <?php endforeach; ?>
    <button onclick="window.history.back()">Πίσω</button>
</body>
</html>