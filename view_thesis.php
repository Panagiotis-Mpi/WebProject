<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT T.*, TP.title AS topic_title
    FROM Theses T
    JOIN Topics TP ON T.topic_id = TP.id
    WHERE T.student_id = ?
");
$stmt->execute([$student_id]);
$thesis = $stmt->fetch();

?>

<h2>Η Διπλωματική μου Εργασία</h2>

<?php if ($thesis): ?>
    <p><strong>Θέμα:</strong> <?= htmlspecialchars($thesis['topic_title']) ?></p>
    <p><strong>Κατάσταση:</strong> <?= htmlspecialchars($thesis['status']) ?></p>
    <p><strong>Ημερομηνία Ανάθεσης:</strong> <?= htmlspecialchars($thesis['assignment_date']) ?></p>
    <?php if ($thesis['library_link']): ?>
        <p><a href="<?= htmlspecialchars($thesis['library_link']) ?>" target="_blank">📥 Τελικό Κείμενο (Νημερτής)</a></p>
    <?php endif; ?>
<?php else: ?>
    <p>Δεν έχει ανατεθεί ακόμα διπλωματική εργασία.</p>
<?php endif; ?>
