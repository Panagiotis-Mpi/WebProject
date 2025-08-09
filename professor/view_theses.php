<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit;
}

$prof_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT T.*, U.first_name, U.last_name, TP.title AS topic_title
    FROM Theses T
    JOIN Users U ON T.student_id = U.id
    JOIN Topics TP ON T.topic_id = TP.id
    WHERE T.supervisor_id = ?
");
$stmt->execute([$prof_id]);
$theses = $stmt->fetchAll();
?>

<h2>Οι Διπλωματικές μου</h2>

<?php if ($theses): ?>
    <table border="1" cellpadding="6">
        <tr>
            <th>Φοιτητής</th>
            <th>Θέμα</th>
            <th>Κατάσταση</th>
            <th>Ανάθεση</th>
        </tr>
        <?php foreach ($theses as $thesis): ?>
            <tr>
                <td><?= htmlspecialchars($thesis['first_name'] . ' ' . $thesis['last_name']) ?></td>
                <td><?= htmlspecialchars($thesis['topic_title']) ?></td>
                <td><?= htmlspecialchars($thesis['status']) ?></td>
                <td><?= htmlspecialchars($thesis['assignment_date']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>Δεν έχετε ακόμα ανατεθειμένες διπλωματικές.</p>
<?php endif; ?>
