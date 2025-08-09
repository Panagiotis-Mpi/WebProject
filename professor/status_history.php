<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['thesis_id'])) {
    header("Location: status_update.php");
    exit;
}

$thesis_id = $_GET['thesis_id'];
$user_id = $_SESSION['user_id'];

// Verify user has access to this thesis
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Theses 
                      WHERE id = ? AND 
                      (student_id = ? OR 
                       supervisor_id = ? OR
                       EXISTS (SELECT 1 FROM CommitteeMembers 
                              WHERE thesis_id = ? 
                              AND (professor_id1 = ? OR professor_id2 = ?)))");
$stmt->execute([$thesis_id, $user_id, $user_id, $thesis_id, $user_id, $user_id]);
$has_access = $stmt->fetchColumn();

if (!$has_access) {
    header("Location: status_update.php");
    exit;
}

// Get status history
$stmt = $pdo->prepare("SELECT sh.*, u.first_name, u.last_name 
                      FROM StatusHistory sh
                      JOIN Users u ON sh.changed_by = u.id
                      WHERE sh.thesis_id = ?
                      ORDER BY sh.change_date DESC");
$stmt->execute([$thesis_id]);
$history = $stmt->fetchAll();

// Get thesis title
$stmt = $pdo->prepare("SELECT t.id, top.title 
                      FROM Theses t
                      JOIN Topics top ON t.topic_id = top.id
                      WHERE t.id = ?");
$stmt->execute([$thesis_id]);
$thesis = $stmt->fetch();
?>

<h2>Ιστορικό Κατάστασης: <?= htmlspecialchars($thesis['title']) ?></h2>

<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>Κατάσταση</th>
        <th>Αλλαγή από</th>
        <th>Ημερομηνία</th>
    </tr>
    <?php foreach ($history as $entry): ?>
        <tr>
            <td><?= htmlspecialchars($entry['status']) ?></td>
            <td><?= htmlspecialchars($entry['first_name'] . ' ' . $entry['last_name']) ?></td>
            <td><?= htmlspecialchars($entry['change_date']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<p><a href="status_update.php">Επιστροφή</a></p>