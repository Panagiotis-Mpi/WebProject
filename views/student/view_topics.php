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

// Ο κώδικας για την υποβολή αίτησης παραμένει ίδιος
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['topic_id'])) {
    $topic_id = $_POST['topic_id'];
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Theses WHERE student_id = ? AND status IN ('pending','active','under_review')");
        $stmt->execute([$student_id]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Έχετε ήδη ενεργή ή υπό ανάθεση διπλωματική.";
        } else {
            $stmt = $pdo->prepare("SELECT creator_id FROM Topics WHERE id = ?");
            $stmt->execute([$topic_id]);
            $topic = $stmt->fetch();
            if ($topic) {
                $supervisor_id = $topic['creator_id'];
                $stmt = $pdo->prepare("INSERT INTO Theses (topic_id, student_id, supervisor_id, status, assignment_date) VALUES (?, ?, ?, 'pending', NOW())");
                $stmt->execute([$topic_id, $student_id, $supervisor_id]);
                $success = "Η αίτησή σας καταχωρήθηκε! Αναμένεται έγκριση από τον επιβλέποντα.";
            } else {
                $error = "Το θέμα δεν βρέθηκε.";
            }
        }
    } catch (PDOException $e) {
        $error = "Σφάλμα: " . $e->getMessage();
    }
}
?>
        });
    </script>
</body>

</html>
