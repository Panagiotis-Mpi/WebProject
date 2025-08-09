<?php
// my_topics.php - Λίστα θεμάτων καθηγητή με δυνατότητα επεξεργασίας/διαγραφής

session_start();
require '../db.php';

// Έλεγχος αν είναι συνδεδεμένος καθηγητής
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit;
}

$professor_id = $_SESSION['user_id'];

try {
    // Φέρνουμε όλα τα θέματα που έχει δημιουργήσει ο καθηγητής
    $stmt = $conn->prepare("
        SELECT id, title, summary, pdf_path
        FROM Topics
        WHERE creator_id = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$professor_id]);
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Σφάλμα βάσης: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<title>Τα Θέματά Μου</title>
<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .topic-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 5px; }
    h2 { margin-top: 0; }
    .btn {
        padding: 5px 10px;
        text-decoration: none;
        border-radius: 3px;
        margin-right: 5px;
    }
    .edit-btn { background: #ffc107; color: #000; }
    .delete-btn { background: #dc3545; color: #fff; }
    .pdf-link { color: #007bff; text-decoration: underline; }
</style>
</head>
<body>

<h1>📄 Τα Θέματά Μου</h1>
<a href="create_topic.php" class="btn edit-btn">➕ Νέο Θέμα</a>
<hr>

<?php if (count($topics) > 0): ?>
    <?php foreach ($topics as $topic): ?>
        <div class="topic-card">
            <h2><?= htmlspecialchars($topic['title']) ?></h2>
            <p><strong>Περιγραφή:</strong> <?= nl2br(htmlspecialchars($topic['summary'])) ?></p>
            <?php if (!empty($topic['pdf_path'])): ?>
                <p><a class="pdf-link" href="<?= htmlspecialchars($topic['pdf_path']) ?>" target="_blank">📄 Προβολή PDF</a></p>
            <?php endif; ?>
            <a href="edit_topic.php?id=<?= $topic['id'] ?>" class="btn edit-btn">✏ Επεξεργασία</a>
            <a href="delete_topic.php?id=<?= $topic['id'] ?>" class="btn delete-btn" onclick="return confirm('Σίγουρα θέλετε να διαγράψετε το θέμα;')">🗑 Διαγραφή</a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Δεν έχετε δημιουργήσει θέματα ακόμη.</p>
<?php endif; ?>

</body>
</html>
