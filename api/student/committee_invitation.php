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
