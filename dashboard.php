<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM Users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Θέματα που έχει δημιουργήσει
$stmt = $pdo->prepare("SELECT * FROM Topics WHERE creator_id = ?");
$stmt->execute([$user['id']]);
$topics = $stmt->fetchAll();
?>

<h2>Καθηγητής <?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></h2>

<h3>Τα Θέματά σας:</h3>
<a href="create_topic.php">+ Προσθήκη νέου θέματος</a>
<ul>
<?php foreach ($topics as $topic): ?>
    <li><strong><?= htmlspecialchars($topic['title']) ?></strong> - <?= htmlspecialchars($topic['summary']) ?></li>
<?php endforeach; ?>
</ul>
