<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Βρίσκουμε τη διπλωματική του φοιτητή
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
    echo "<p>Δεν σας έχει ανατεθεί διπλωματική εργασία ακόμα.</p>";
    echo '<p><a href="dashboard.php">← Επιστροφή στο Dashboard</a></p>';
    exit();
}

$thesis_id = $thesis['thesis_id'];

// Φόρτωση σημειώσεων
$stmt = $pdo->prepare("
    SELECT n.content, u.first_name, u.last_name
    FROM Notes n
    JOIN Users u ON n.creator_id = u.id
    WHERE n.thesis_id = ?
");
$stmt->execute([$thesis_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Η Διπλωματική Μου</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { margin-bottom: 15px; }
        .note { border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 4px; background: #f9f9f9; }
        .note small { color: #666; display: block; margin-top: 5px; }
    </style>
</head>
<body>
    <h1>Η Διπλωματική Μου</h1>

    <h3>Βασικές Πληροφορίες</h3>
    <p><strong>Θέμα:</strong> <?= htmlspecialchars($thesis['title']) ?></p>
    <p><strong>Επιβλέπων:</strong> <?= htmlspecialchars($thesis['sup_first'].' '.$thesis['sup_last']) ?></p>
    <p><strong>Κατάσταση:</strong> <?= htmlspecialchars($thesis['status']) ?></p>

    <h3>Σημειώσεις Καθηγητών</h3>
    <?php if (empty($notes)): ?>
        <p>Δεν υπάρχουν σημειώσεις για τη διπλωματική σας.</p>
    <?php else: ?>
        <?php foreach ($notes as $note): ?>
            <div class="note">
                <p><?= nl2br(htmlspecialchars($note['content'])) ?></p>
                <small>Από: <?= htmlspecialchars($note['first_name'].' '.$note['last_name']) ?></small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a href="dashboard.php">← Επιστροφή στο Dashboard</a></p>
</body>
</html>