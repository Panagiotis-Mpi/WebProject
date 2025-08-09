<?php
session_start();
require '../config/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$message = '';

// Get student's active thesis
$stmt = $pdo->prepare("SELECT t.id, t.status, top.title 
                      FROM Theses t
                      JOIN Topics top ON t.topic_id = top.id
                      WHERE t.student_id = ? AND t.status = 'active'");
$stmt->execute([$student_id]);
$thesis = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $thesis) {
    $presentation_date = $_POST['presentation_date'];
    $drive_link = $_POST['drive_link'] ?? '';
    
    try {
        $pdo->beginTransaction();
        
        // Handle file upload if present
        $file_path = '';
        if (isset($_FILES['thesis_file']) && $_FILES['thesis_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/theses/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $filename = uniqid() . '_' . preg_replace('/[^A-Za-z0-9\._\-]/', '', $_FILES['thesis_file']['name']);
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['thesis_file']['tmp_name'], $filepath)) {
                $file_path = $filepath;
            }
        }
        
        // Update thesis with submission details
        $stmt = $pdo->prepare("UPDATE Theses 
                              SET status = 'under_review',
                                  library_link = ?,
                                  submission_date = NOW(),
                                  presentation_date = ?
                              WHERE id = ?");
        $stmt->execute([$drive_link ?: $file_path, $presentation_date, $thesis['id']]);
        
        // Record status change
        $stmt = $pdo->prepare("INSERT INTO StatusHistory 
                              (thesis_id, status, changed_by) 
                              VALUES (?, 'under_review', ?)");
        $stmt->execute([$thesis['id'], $student_id]);
        
        $pdo->commit();
        $message = "Η διπλωματική υποβλήθηκε επιτυχώς!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Σφάλμα: " . $e->getMessage();
    }
}
?>

<?php include '../includes/header.php'; ?>

<h2>Υποβολή Διπλωματικής Εργασίας</h2>

<?php if ($message): ?>
    <p style="color: <?= strpos($message, 'επιτυχώς') !== false ? 'green' : 'red' ?>">
        <?= htmlspecialchars($message) ?>
    </p>
<?php endif; ?>

<?php if (!$thesis): ?>
    <p>Δεν έχετε ενεργή διπλωματική εργασία για υποβολή.</p>
<?php else: ?>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <h4>Θέμα: <?= htmlspecialchars($thesis['title']) ?></h4>
        </div>
        
        <div class="mb-3">
            <label for="presentation_date" class="form-label">Ημερομηνία Παρουσίασης *</label>
            <input type="datetime-local" class="form-control" id="presentation_date" name="presentation_date" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Επιλογή Υποβολής:</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="submission_type" id="submission_file" value="file" checked>
                <label class="form-check-label" for="submission_file">
                    Ανέβασμα Αρχείου
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="submission_type" id="submission_drive" value="drive">
                <label class="form-check-label" for="submission_drive">
                    Σύνδεσμος Google Drive
                </label>
            </div>
        </div>
        
        <div class="mb-3" id="file_upload_section">
            <label for="thesis_file" class="form-label">Αρχείο Διπλωματικής (PDF)</label>
            <input type="file" class="form-control" id="thesis_file" name="thesis_file" accept=".pdf">
        </div>
        
        <div class="mb-3" id="drive_link_section" style="display: none;">
            <label for="drive_link" class="form-label">Σύνδεσμος Google Drive</label>
            <input type="url" class="form-control" id="drive_link" name="drive_link" placeholder="https://drive.google.com/...">
        </div>
        
        <button type="submit" class="btn btn-primary">Υποβολή</button>
    </form>
    
    <script>
    // Toggle between file upload and drive link
    document.querySelectorAll('input[name="submission_type"]').forEach(el => {
        el.addEventListener('change', function() {
            document.getElementById('file_upload_section').style.display = 
                this.value === 'file' ? 'block' : 'none';
            document.getElementById('drive_link_section').style.display = 
                this.value === 'drive' ? 'block' : 'none';
        });
    });
    </script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>