<?php
session_start();
require '../config/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit;
}

$professor_id = $_SESSION['user_id'];
$message = '';

// Get theses ready for grading
$stmt = $pdo->prepare("SELECT t.id, t.topic_id, top.title, 
                      u.first_name, u.last_name, u.am
                      FROM Theses t
                      JOIN Topics top ON t.topic_id = top.id
                      JOIN Users u ON t.student_id = u.id
                      WHERE t.status = 'under_review' AND
                      (t.supervisor_id = ? OR 
                       EXISTS (SELECT 1 FROM CommitteeMembers 
                              WHERE thesis_id = t.id 
                              AND (professor_id1 = ? OR professor_id2 = ?)))");
$stmt->execute([$professor_id, $professor_id, $professor_id]);
$theses = $stmt->fetchAll();

// Check if already graded
$graded_theses = [];
if (!empty($theses)) {
    $stmt = $pdo->prepare("SELECT thesis_id FROM Grades WHERE professor_id = ?");
    $stmt->execute([$professor_id]);
    $graded_theses = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thesis_id'])) {
    $thesis_id = $_POST['thesis_id'];
    $grade = $_POST['grade'];
    $criteria = [
        'content' => $_POST['content_quality'],
        'presentation' => $_POST['presentation_quality'],
        'originality' => $_POST['originality'],
        'difficulty' => $_POST['difficulty']
    ];
    
    // Validate grade (1-10 with one decimal)
    if (!preg_match('/^(10|\d)(\.\d)?$/', $grade) || $grade < 1 || $grade > 10) {
        $message = "Ο βαθμός πρέπει να είναι μεταξύ 1 και 10.";
    } else {
        try {
            $pdo->beginTransaction();
            
            // Check if already graded
            $stmt = $pdo->prepare("SELECT id FROM Grades WHERE thesis_id = ? AND professor_id = ?");
            $stmt->execute([$thesis_id, $professor_id]);
            
            if ($stmt->fetch()) {
                // Update existing grade
                $stmt = $pdo->prepare("UPDATE Grades 
                                      SET grade = ?, criteria = ?
                                      WHERE thesis_id = ? AND professor_id = ?");
            } else {
                // Insert new grade
                $stmt = $pdo->prepare("INSERT INTO Grades 
                                      (thesis_id, professor_id, grade, criteria)
                                      VALUES (?, ?, ?, ?)");
            }
            
            $stmt->execute([
                $thesis_id,
                $professor_id,
                $grade,
                json_encode($criteria, JSON_UNESCAPED_UNICODE)
            ]);
            
            $pdo->commit();
            $message = "Η βαθμολόγηση καταχωρήθηκε επιτυχώς!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Σφάλμα: " . $e->getMessage();
        }
    }
}

// Get thesis details if specified
$current_thesis = null;
if (isset($_GET['thesis_id'])) {
    $stmt = $pdo->prepare("SELECT t.id, top.title, 
                          u.first_name, u.last_name, u.am,
                          g.grade, g.criteria
                          FROM Theses t
                          JOIN Topics top ON t.topic_id = top.id
                          JOIN Users u ON t.student_id = u.id
                          LEFT JOIN Grades g ON g.thesis_id = t.id AND g.professor_id = ?
                          WHERE t.id = ?");
    $stmt->execute([$professor_id, $_GET['thesis_id']]);
    $current_thesis = $stmt->fetch();
}
?>

<?php include '../includes/header.php'; ?>

<h2>Βαθμολόγηση Διπλωματικής Εργασίας</h2>

<?php if ($message): ?>
    <div class="alert alert-<?= strpos($message, 'επιτυχώς') !== false ? 'success' : 'danger' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Διπλωματικές προς Βαθμολόγηση</h5>
            </div>
            <div class="card-body">
                <?php if (empty($theses)): ?>
                    <p>Δεν υπάρχουν διπλωματικές προς βαθμολόγηση.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($theses as $thesis): ?>
                            <a href="grade_thesis.php?thesis_id=<?= $thesis['id'] ?>" 
                               class="list-group-item list-group-item-action <?= $current_thesis && $current_thesis['id'] == $thesis['id'] ? 'active' : '' ?>">
                                <?= htmlspecialchars($thesis['title']) ?>
                                <br>
                                <small><?= htmlspecialchars($thesis['first_name'] . ' ' . $thesis['last_name'] . ' (' . $thesis['am'] . ')') ?></small>
                                <?php if (in_array($thesis['id'], $graded_theses)): ?>
                                    <span class="badge bg-success float-end">Βαθμολογημένη</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if ($current_thesis): ?>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Βαθμολόγηση: <?= htmlspecialchars($current_thesis['title']) ?></h5>
            </div>
            <div class="card-body">
                <p>Φοιτητής: <?= htmlspecialchars($current_thesis['first_name'] . ' ' . $current_thesis['last_name'] . ' (' . $current_thesis['am'] . ')') ?></p>
                
                <form method="post">
                    <input type="hidden" name="thesis_id" value="<?= $current_thesis['id'] ?>">
                    
                    <div class="mb-3">
                        <label for="grade" class="form-label">Τελικός Βαθμός (1–10) *</label>
                        <input type="number" step="0.1" min="1" max="10" 
                               class="form-control" id="grade" name="grade" 
                               value="<?= htmlspecialchars($current_thesis['grade'] ?? '') ?>" required>
                    </div>
                    
                    <h5>Κριτήρια Βαθμολόγησης</h5>
                    
                    <?php 
                    $criteria = $current_thesis['criteria'] ? json_decode($current_thesis['criteria'], true) : [
                        'content' => 0,
                        'presentation' => 0,
                        'originality' => 0,
                        'difficulty' => 0
                    ];
                    ?>
                    
                    <div class="mb-3">
                        <label for="content_quality" class="form-label">Ποιότητα Περιεχομένου (0–10)</label>
                        <input type="number" step="0.1" min="0" max="10" 
                               class="form-control" id="content_quality" name="content_quality" 
                               value="<?= htmlspecialchars($criteria['content'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="presentation_quality" class="form-label">Παρουσίαση (0–10)</label>
                        <input type="number" step="0.1" min="0" max="10" 
                               class="form-control" id="presentation_quality" name="presentation_quality" 
                               value="<?= htmlspecialchars($criteria['presentation'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="originality" class="form-label">Πρωτοτυπία (0–10)</label>
                        <input type="number" step="0.1" min="0" max="10" 
                               class="form-control" id="originality" name="originality" 
                               value="<?= htmlspecialchars($criteria['originality'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="difficulty" class="form-label">Δυσκολία (0–10)</label>
                        <input type="number" step="0.1" min="0" max="10" 
                               class="form-control" id="difficulty" name="difficulty" 
                               value="<?= htmlspecialchars($criteria['difficulty'] ?? '') ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Υποβολή Βαθμολογίας</button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>