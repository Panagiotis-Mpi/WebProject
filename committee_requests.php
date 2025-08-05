<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit;
}

$professor_id = $_SESSION['user_id'];
$message = '';

// Handle accept/reject actions
if (isset($_GET['action']) && isset($_GET['committee_id'])) {
    $committee_id = $_GET['committee_id'];
    $action = $_GET['action'];
    
    $stmt = $pdo->prepare("UPDATE CommitteeMembers 
                          SET status = ? 
                          WHERE id = ? AND (professor_id1 = ? OR professor_id2 = ?)");
    $stmt->execute([$action, $committee_id, $professor_id, $professor_id]);
    
    $message = "Η απάντησή σας καταχωρήθηκε.";
}

// Get pending invitations
$stmt = $pdo->prepare("SELECT cm.id, cm.thesis_id, t.topic_id, top.title, 
                      u1.first_name as sup_first, u1.last_name as sup_last,
                      u2.first_name as prof1_first, u2.last_name as prof1_last,
                      u3.first_name as prof2_first, u3.last_name as prof2_last
                      FROM CommitteeMembers cm
                      JOIN Theses t ON cm.thesis_id = t.id
                      JOIN Topics top ON t.topic_id = top.id
                      JOIN Users u1 ON cm.supervisor_id = u1.id
                      JOIN Users u2 ON cm.professor_id1 = u2.id
                      JOIN Users u3 ON cm.professor_id2 = u3.id
                      WHERE (cm.professor_id1 = ? OR cm.professor_id2 = ?)
                      AND cm.status = 'invited'");
$stmt->execute([$professor_id, $professor_id]);
$invitations = $stmt->fetchAll();
?>

<h2>Προσκλήσεις Επιτροπής</h2>

<?php if ($message): ?>
    <p style="color: green"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<?php if (empty($invitations)): ?>
    <p>Δεν έχετε εκκρεμείς προσκλήσεις.</p>
<?php else: ?>
    <ul>
        <?php foreach ($invitations as $inv): ?>
            <li>
                <strong>Θέμα:</strong> <?= htmlspecialchars($inv['title']) ?><br>
                <strong>Επιβλέπων:</strong> <?= htmlspecialchars($inv['sup_first'] . ' ' . $inv['sup_last']) ?><br>
                <strong>Μέλη Επιτροπής:</strong> 
                <?= htmlspecialchars($inv['prof1_first'] . ' ' . $inv['prof1_last']) ?>, 
                <?= htmlspecialchars($inv['prof2_first'] . ' ' . $inv['prof2_last']) ?>
                
                <div style="margin-top: 10px;">
                    <a href="committee_requests.php?action=accepted&committee_id=<?= $inv['id'] ?>" 
                       style="color: green; margin-right: 10px;">Αποδοχή</a>
                    <a href="committee_requests.php?action=rejected&committee_id=<?= $inv['id'] ?>" 
                       style="color: red;">Απόρριψη</a>
                </div>
            </li>
            <hr>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>