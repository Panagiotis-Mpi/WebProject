<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit;
}

$professor_id = $_SESSION['user_id'];
$message = '';

if (isset($_POST['title']) && isset($_POST['summary'])) {
    $title = $_POST['title'];
    $summary = $_POST['summary'];
    
    $stmt = $pdo->prepare("INSERT INTO Topics (title, summary, creator_id) VALUES (?, ?, ?)");
    $stmt->execute([$title, $summary, $professor_id]);
    $message = "Το θέμα δημιουργήθηκε επιτυχώς!";
}
?>

<h2>Δημιουργία Νέου Θέματος</h2>

<?php if ($message): ?>
    <p style="color: green"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="post">
    <div>
        <label for="title">Τίτλος Θέματος:</label><br>
        <input type="text" id="title" name="title" required>
    </div>
    <div>
        <label for="summary">Σύντομη Περιγραφή:</label><br>
        <textarea id="summary" name="summary" rows="4" required></textarea>
    </div>
    <div>
        <button type="submit">Δημιουργία Θέματος</button>
    </div>
</form>