<?php
session_start();
require '../config/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$message = '';

// Get student's thesis ready for finalization
$stmt = $pdo->prepare("SELECT t.id, t.status, top.title, 
                      GROUP_CONCAT(CONCAT(u.first_name, ' ', u.last_name) AS committee,
                      AVG(g.grade) AS final_grade
                      FROM Theses t
                      JOIN Topics top ON t.topic_id = top.id
                      LEFT JOIN CommitteeMembers cm ON cm.thesis_id = t.id
                      LEFT JOIN Users u ON (cm.professor_id1 = u.id OR cm.professor_id2 = u.id)
                      LEFT JOIN Grades g ON g.thesis_id = t.id
                      WHERE t.student_id = ? AND t.status = 'under_review'
                      GROUP BY t.id");
$stmt->execute([$student_id]);
$thesis = $stmt->fetch();

// Handle library link submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $thesis) {
    $library_link = $_POST['library_link'];
    
    if (empty($library_link)) {
        $message = "Πρέπει να εισάγετε σύνδεσμο προς το αποθετήριο.";
    } else {
        try {
            $pdo->beginTransaction();
            
            // Update thesis with library link
            $stmt = $pdo->prepare("UPDATE Theses 
                                  SET library_link = ?
                                  WHERE id = ?");
            $stmt->execute([$library_link, $thesis['id']]);
            
            $pdo->commit();
            $message = "Ο σύνδεσμος καταχωρήθηκε επιτυχώς!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Σφάλμα: " . $e->getMessage();
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<h2>Ολοκλήρωση Διπλωματικής Εργασίας</h2>

<?php if ($message): ?>
    <div class="alert alert-<?= strpos($message, 'επιτυχώς') !== false ? 'success' : 'danger' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<?php if (!$thesis): ?>
    <p>Δεν έχετε διπλωματική έτοιμη για ολοκλήρωση.</p>
<?php else: ?>
    <div class="card mb-4">
        <div class="card-header">
            <h4><?= htmlspecialchars($thesis['title']) ?></h4>
        </div>
        <div class="card-body">
            <h5>Πρακτικό Εξέτασης</h5>
            <div class="border p-3 mb-4">
                <h6>Στοιχεία Διπλωματικής</h6>
                <p><strong>Τίτλος:</strong> <?= htmlspecialchars($thesis['title']) ?></p>
                <p><strong>Επιτροπή:</strong> <?= htmlspecialchars($thesis['committee']) ?></p>
                <p><strong>Τελικός Βαθμός:</strong> <?= round($thesis['final_grade'], 2) ?></p>
                <p><strong>Ημερομηνία Ολοκλήρωσης:</strong> <?= date('d/m/Y') ?></p>
            </div>
            
            <form method="post">
                <div class="mb-3">
                    <label for="library_link" class="form-label">Σύνδεσμος προς Αποθετήριο (Νημερτής) *</label>
                    <input type="url" class="form-control" id="library_link" name="library_link" 
                           value="<?= htmlspecialchars($thesis['library_link'] ?? '') ?>" 
                           placeholder="https://nemertes.lis.upatras.gr/..." required>
                </div>
                
                <button type="submit" class="btn btn-primary">Υποβολή</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>