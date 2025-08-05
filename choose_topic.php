<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$message = '';

// Έλεγχος αν ο φοιτητής έχει ήδη αναλάβει διπλωματική
$stmt = $pdo->prepare("SELECT * FROM Theses WHERE student_id = ?");
$stmt->execute([$student_id]);
$existing = $stmt->fetch();

if ($existing) {
    $message = "Έχετε ήδη αναλάβει διπλωματική εργασία.";
} elseif (isset($_POST['topic_id'])) {
    $topic_id = $_POST['topic_id'];

    // Βρίσκουμε το θέμα και τον επιβλέποντα
    $stmt = $pdo->prepare("SELECT * FROM Topics WHERE id = ?");
    $stmt->execute([$topic_id]);
    $topic = $stmt->fetch();

    if ($topic) {
        $stmt = $pdo->prepare("INSERT INTO Theses (topic_id, student_id, supervisor_id, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$topic_id, $student_id, $topic['creator_id']]);
        $message = "Η ανάληψη θέματος έγινε με επιτυχία! Περιμένετε αποδοχή από την τριμελή επιτροπή.";
    } else {
        $message = "Σφάλμα: το θέμα δεν βρέθηκε.";
    }
}

// Φέρνουμε όλα τα διαθέσιμα θέματα
$topics = $pdo->query("SELECT Topics.*, Users.first_name, Users.last_name 
                       FROM Topics 
                       JOIN Users ON Topics.creator_id = Users.id")->fetchAll();
?>

<h2>Επιλογή Θέματος</h2>

<?php if ($message): ?>
    <p style="color: green"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<ul>
<?php foreach ($topics as $topic): ?>
    <li>
        <strong><?= htmlspecialchars($topic['title']) ?></strong><br>
        <?= htmlspecialchars($topic['summary']) ?><br>
        Επιβλέπων: <?= htmlspecialchars($topic['first_name'] . ' ' . $topic['last_name']) ?><br>
        <form method="post" style="margin-top: 5px;">
            <input type="hidden" name="topic_id" value="<?= $topic['id'] ?>">
            <button type="submit">Ανάληψη Θέματος</button>
        </form>
    </li>
    <hr>
<?php endforeach; ?>
</ul>
