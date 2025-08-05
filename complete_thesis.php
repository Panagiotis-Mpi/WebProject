<?php
session_start();
require '../config/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary') {
    header("Location: ../login.php");
    exit;
}

$message = '';

// Get theses ready for completion
$stmt = $pdo->query("SELECT t.id, t.library_link, top.title, 
                    u.first_name, u.last_name, u.am,
                    AVG(g.grade) AS final_grade
                    FROM Theses t
                    JOIN Topics top ON t.topic_id = top.id
                    JOIN Users u ON t.student_id = u.id
                    LEFT JOIN Grades g ON g.thesis_id = t.id
                    WHERE t.status = 'under_review' AND t.library_link IS NOT NULL
                    GROUP BY t.id");
$theses = $stmt->fetchAll();

// Handle thesis completion
if (isset($_GET['complete']) && $_GET['complete']) {
    $thesis_id = $_GET['thesis_id'];
    
    try {
        $pdo->beginTransaction();
        
        // Update thesis status
        $stmt = $pdo->prepare("UPDATE Theses 
                              SET status = 'completed',
                                  completion_date = NOW()
                              WHERE id = ?");
        $stmt->execute([$thesis_id]);
        
        // Record status change
        $stmt = $pdo->prepare("INSERT INTO StatusHistory 
                              (thesis_id, status, changed_by) 
                              VALUES (?, 'completed', ?)");
        $stmt->execute([$thesis_id, $_SESSION['user_id']]);
        
        $pdo->commit();
        $message = "Η διπλωματική ολοκληρώθηκε επιτυχώς!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Σφάλμα: " . $e->getMessage();
    }
}
?>

<?php include '../includes/header.php'; ?>

<h2>Ολοκλήρωση Διπλωματικών Εργασιών</h2>

<?php if ($message): ?>
    <div class="alert alert-<?= strpos($message, 'επιτυχώς') !== false ? 'success' : 'danger' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<?php if (empty($theses)): ?>
    <p>Δεν υπάρχουν διπλωματικές έτοιμες για ολοκλήρωση.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Φοιτητής</th>
                    <th>Θέμα</th>
                    <th>Τελικός Βαθμός</th>
                    <th>Σύνδεσμος</th>
                    <th>Ενέργεια</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($theses as $thesis): ?>
                    <tr>
                        <td><?= htmlspecialchars($thesis['first_name'] . ' ' . $thesis['last_name'] . ' (' . $thesis['am'] . ')') ?></td>
                        <td><?= htmlspecialchars($thesis['title']) ?></td>
                        <td><?= round($thesis['final_grade'], 2) ?></td>
                        <td>
                            <a href="<?= htmlspecialchars($thesis['library_link']) ?>" target="_blank">Προβολή</a>
                        </td>
                        <td>
                            <a href="complete_thesis.php?complete=1&thesis_id=<?= $thesis['id'] ?>" 
                               class="btn btn-sm btn-success"
                               onclick="return confirm('Είστε σίγουρος ότι θέλετε να ολοκληρώσετε αυτή τη διπλωματική;')">
                                Ολοκλήρωση
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>