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
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Λίστα Θεμάτων</title>
    <style>
        .taken { color: red; font-weight: bold; }
        .free { color: green; font-weight: bold; }
        form { display:inline; }
    </style>
</head>
<body>
    <h1>Λίστα Θεμάτων</h1>

    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <div id="topics-container">
        </div>

    <p><a href="dashboard.php">← Επιστροφή στο Dashboard</a></p>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const topicsContainer = document.getElementById('topics-container');

            fetch('../api/get_topics.php')
                .then(response => response.json())
                .then(topics => {
                    if (topics.error) {
                        topicsContainer.innerHTML = `<p style="color:red;">Σφάλμα φόρτωσης θεμάτων: ${topics.error}</p>`;
                        return;
                    }
                    if (topics.length === 0) {
                        topicsContainer.innerHTML = "<p>Δεν υπάρχουν διαθέσιμα θέματα αυτή τη στιγμή.</p>";
                        return;
                    }
                    
                    let html = '<ul>';
                    topics.forEach(topic => {
                        const statusClass = topic.is_taken > 0 ? 'taken' : 'free';
                        const statusText = topic.is_taken > 0 ? 'Δεσμευμένο' : 'Ελεύθερο';
                        const actionButton = topic.is_taken > 0 ? '' : `
                            <form method="post">
                                <input type="hidden" name="topic_id" value="${topic.id}">
                                <button type="submit">Αίτηση Ανάθεσης</button>
                            </form>
                        `;

                        const pdfLink = topic.pdf_path ? `<a href="${topic.pdf_path}" target="_blank">📄 Αναλυτική Περιγραφή</a>` : '';

                        html += `
                            <li>
                                <strong>${topic.title}</strong><br>
                                ${topic.summary.replace(/\n/g, '<br>')}<br>
                                Επιβλέπων: ${topic.first_name} ${topic.last_name}<br>
                                Κατάσταση: <span class="${statusClass}">${statusText}</span>
                                ${actionButton}
                                <br>
                                ${pdfLink}
                            </li>
                            <hr>
                        `;
                    });
                    html += '</ul>';
                    topicsContainer.innerHTML = html;
                })
                .catch(error => {
                    topicsContainer.innerHTML = `<p style="color:red;">Σφάλμα κατά την ανάκτηση των θεμάτων. Παρακαλώ δοκιμάστε ξανά.</p>`;
                    console.error('Fetch error:', error);
                });
        });
    </script>
</body>
</html>