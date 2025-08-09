<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary') {
    header("Location: ../login.php");
    exit;
}

$stmt = $pdo->query("
    SELECT T.*, U.first_name, U.last_name, TP.title AS topic_title
    FROM Theses T
    JOIN Users U ON T.student_id = U.id
    JOIN Topics TP ON T.topic_id = TP.id
    WHERE T.status IN ('active', 'under_review')
");
$theses = $stmt->fetchAll();
?>

<h2>Ενεργές και Υπό Εξέταση Διπλωματικές</h2>

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
    <p>Δεν υπάρχουν διπλωματικές σε εξέλιξη.</p>
<?php endif; ?>
