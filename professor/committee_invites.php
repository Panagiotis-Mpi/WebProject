<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit;
}

$professor_id = $_SESSION['user_id'];
$message = '';

// Get professor's active theses
$stmt = $pdo->prepare("SELECT t.id, t.topic_id, top.title 
                      FROM Theses t
                      JOIN Topics top ON t.topic_id = top.id
                      WHERE t.supervisor_id = ? AND t.status = 'active'");
$stmt->execute([$professor_id]);
$theses = $stmt->fetchAll();

// Get all other professors
$stmt = $pdo->prepare("SELECT id, first_name, last_name FROM Users WHERE role = 'professor' AND id != ?");
$stmt->execute([$professor_id]);
$professors = $stmt->fetchAll();

if (isset($_POST['thesis_id']) && isset($_POST['professor1']) && isset($_POST['professor2'])) {
    $thesis_id = $_POST['thesis_id'];
    $professor1_id = $_POST['professor1'];
    $professor2_id = $_POST['professor2'];
    
    // Check if professors are different
    if ($professor1_id == $professor2_id) {
        $message = "Πρέπει να επιλέξετε 2 διαφορετικούς καθηγητές.";
    } else {
        // Create committee invitations
        $stmt = $pdo->prepare("INSERT INTO CommitteeMembers 
                              (thesis_id, supervisor_id, professor_id1, professor_id2) 
                              VALUES (?, ?, ?, ?)");
        $stmt->execute([$thesis_id, $professor_id, $professor1_id, $professor2_id]);
        
        $message = "Οι προσκλήσεις στάλθηκαν επιτυχώς!";
    }
}
?>

<h2>Πρόσκληση Επιτροπής Διπλωματικής</h2>

<?php if ($message): ?>
    <p style="color: <?= strpos($message, 'επιτυχώς') !== false ? 'green' : 'red' ?>">
        <?= htmlspecialchars($message) ?>
    </p>
<?php endif; ?>

<form method="post">
    <div>
        <label for="thesis_id">Διπλωματική Εργασία:</label>
        <select id="thesis_id" name="thesis_id" required>
            <option value="">-- Επιλέξτε --</option>
            <?php foreach ($theses as $thesis): ?>
                <option value="<?= $thesis['id'] ?>">
                    <?= htmlspecialchars($thesis['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div>
        <label for="professor1">1ο Μέλος Επιτροπής:</label>
        <select id="professor1" name="professor1" required>
            <option value="">-- Επιλέξτε --</option>
            <?php foreach ($professors as $prof): ?>
                <option value="<?= $prof['id'] ?>">
                    <?= htmlspecialchars($prof['first_name'] . ' ' . $prof['last_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div>
        <label for="professor2">2ο Μέλος Επιτροπής:</label>
        <select id="professor2" name="professor2" required>
            <option value="">-- Επιλέξτε --</option>
            <?php foreach ($professors as $prof): ?>
                <option value="<?= $prof['id'] ?>">
                    <?= htmlspecialchars($prof['first_name'] . ' ' . $prof['last_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <button type="submit">Αποστολή Προσκλήσεων</button>
</form>