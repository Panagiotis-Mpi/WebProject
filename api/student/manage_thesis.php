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

