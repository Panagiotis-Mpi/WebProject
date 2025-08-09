<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['professor', 'student'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$message = '';

// Get user's theses based on role
if ($role === 'professor') {
    $stmt = $pdo->prepare("SELECT t.id, t.status, top.title 
                          FROM Theses t
                          JOIN Topics top ON t.topic_id = top.id
                          WHERE t.supervisor_id = ? OR 
                                EXISTS (SELECT 1 FROM CommitteeMembers 
                                       WHERE thesis_id = t.id 
                                       AND (professor_id1 = ? OR professor_id2 = ?))");
    $stmt->execute([$user_id, $user_id, $user_id]);
} else { // student
    $stmt = $pdo->prepare("SELECT t.id, t.status, top.title 
                          FROM Theses t
                          JOIN Topics top ON t.topic_id = top.id
                          WHERE t.student_id = ?");
    $stmt->execute([$user_id]);
}
$theses = $stmt->fetchAll();

// Handle status update
if (isset($_POST['thesis_id']) && isset($_POST['new_status'])) {
    $thesis_id = $_POST['thesis_id'];
    $new_status = $_POST['new_status'];
    
    // Verify allowed transitions
    $allowed_transitions = [
        'pending' => ['active'],
        'active' => ['under_review'],
        'under_review' => ['completed']
    ];
    
    // Get current status
    $stmt = $pdo->prepare("SELECT status FROM Theses WHERE id = ?");
    $stmt->execute([$thesis_id]);
    $current_status = $stmt->fetchColumn();
    
    if (isset($allowed_transitions[$current_status]) && 
        in_array($new_status, $allowed_transitions[$current_status])) {
        
        try {
            $pdo->beginTransaction();
            
            // Update thesis status
            $stmt = $pdo->prepare("UPDATE Theses SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $thesis_id]);
            
            // Record in history
            $stmt = $pdo->prepare("INSERT INTO StatusHistory 
                                  (thesis_id, status, changed_by) 
                                  VALUES (?, ?, ?)");
            $stmt->execute([$thesis_id, $new_status, $user_id]);
            
            $pdo->commit();
            $message = "Η κατάσταση ενημερώθηκε επιτυχώς!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Σφάλμα: " . $e->getMessage();
        }
    } else {
        $message = "Μη έγκυρη μετάβαση κατάστασης.";
    }
}
?>

<h2>Διαχείριση Κατάστασης Διπλωματικής</h2>

<?php if ($message): ?>
    <p style="color: <?= strpos($message, 'επιτυχώς') !== false ? 'green' : 'red' ?>">
        <?= htmlspecialchars($message) ?>
    </p>
<?php endif; ?>

<?php if (empty($theses)): ?>
    <p>Δεν έχετε διπλωματικές εργασίες.</p>
<?php else: ?>
    <ul>
        <?php foreach ($theses as $thesis): ?>
            <li>
                <strong><?= htmlspecialchars($thesis['title']) ?></strong><br>
                Τρέχουσα κατάσταση: <?= htmlspecialchars($thesis['status']) ?>
                
                <form method="post" style="margin-top: 5px;">
                    <input type="hidden" name="thesis_id" value="<?= $thesis['id'] ?>">
                    
                    <?php if ($thesis['status'] === 'pending' && $role === 'professor'): ?>
                        <input type="hidden" name="new_status" value="active">
                        <button type="submit">Ενεργοποίηση (pending → active)</button>
                    
                    <?php elseif ($thesis['status'] === 'active' && $role === 'student'): ?>
                        <input type="hidden" name="new_status" value="under_review">
                        <button type="submit">Υποβολή για Αξιολόγηση (active → under_review)</button>
                    
                    <?php elseif ($thesis['status'] === 'under_review' && $role === 'professor'): ?>
                        <input type="hidden" name="new_status" value="completed">
                        <button type="submit">Ολοκλήρωση (under_review → completed)</button>
                    <?php endif; ?>
                </form>
                
                <div style="margin-top: 5px;">
                    <a href="status_history.php?thesis_id=<?= $thesis['id'] ?>">Ιστορικό Καταστάσεων</a>
                </div>
            </li>
            <hr>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>