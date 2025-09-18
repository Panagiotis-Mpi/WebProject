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
$stmt = $pdo->prepare("SELECT id, supervisor_id FROM Theses WHERE student_id = ? AND status IN ('pending','active','under_review') LIMIT 1");
$stmt->execute([$student_id]);
$thesis = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$thesis) {
    $error = "Δεν έχετε ενεργή διπλωματική.";
} else {
    $thesis_id = $thesis['id'];
    $supervisor_id = $thesis['supervisor_id'];

    // Λίστα καθηγητών εκτός του supervisor
    $stmt2 = $pdo->prepare("SELECT id, first_name, last_name FROM Users WHERE role='professor' AND id != ?");
    $stmt2->execute([$supervisor_id]);
    $professors = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $prof1 = $_POST['professor1'] ?? null;
        $prof2 = $_POST['professor2'] ?? null;

        if (!$prof1 || !$prof2 || $prof1 === $prof2) {
            $error = "Παρακαλώ επιλέξτε δύο διαφορετικούς καθηγητές.";
        } else {
            try {
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM CommitteeMembers WHERE thesis_id = ?");
                $stmtCheck->execute([$thesis_id]);
                if ($stmtCheck->fetchColumn() > 0) {
                    $error = "Έχει ήδη οριστεί τριμελής για αυτή τη διπλωματική.";
                } else {
                    // ΝΕΑ Εισαγωγή: Εισάγουμε κάθε μέλος ξεχωριστά
                    $stmtInsert = $pdo->prepare("INSERT INTO CommitteeMembers (thesis_id, professor_id, role, status) VALUES (?, ?, 'member', 'invited')");
                    $stmtInsert->execute([$thesis_id, $prof1]);
                    $stmtInsert->execute([$thesis_id, $prof2]);

                    $success = "Η πρόσκληση τριμελούς δημιουργήθηκε επιτυχώς!";
                }
            } catch (PDOException $e) {
                $error = "Σφάλμα βάσης δεδομένων: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Πρόσκληση Τριμελούς Επιτροπής</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <h1>Πρόσκληση Τριμελούς Επιτροπής</h1>

    <?php if ($error): ?><p style="color:red;"><?=htmlspecialchars($error)?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green;"><?=htmlspecialchars($success)?></p><?php endif; ?>

    <?php if ($thesis): ?>
    <form method="post">
        <label for="professor1">Επιλέξτε 1ο μέλος:</label><br>
        <select name="professor1" required>
            <option value="">-- Επιλογή --</option>
            <?php foreach ($professors as $p): ?>
                <option value="<?=$p['id']?>"><?=htmlspecialchars($p['first_name'].' '.$p['last_name'])?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="professor2">Επιλέξτε 2ο μέλος:</label><br>
        <select name="professor2" required>
            <option value="">-- Επιλογή --</option>
            <?php foreach ($professors as $p): ?>
                <option value="<?=$p['id']?>"><?=htmlspecialchars($p['first_name'].' '.$p['last_name'])?></option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">Αποστολή Πρόσκλησης</button>
    </form>
    <?php endif; ?>

    <p><a href="dashboard.php">← Επιστροφή στο Dashboard</a></p>
</body>
</html>