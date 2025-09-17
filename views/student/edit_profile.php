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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $contact_info = $_POST['contact_info'] ?? '';

    if (!$first_name || !$last_name || !$email) {
        $error = "Όλα τα πεδία (εκτός από στοιχεία επικοινωνίας) είναι υποχρεωτικά.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE Users SET first_name=?, last_name=?, email=?, contact_info=? WHERE id=?");
            $stmt->execute([$first_name, $last_name, $email, $contact_info, $student_id]);
            $success = "Το προφίλ ενημερώθηκε με επιτυχία!";
        } catch (PDOException $e) {
            $error = "Σφάλμα: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Επεξεργασία Προφίλ</title>
</head>
<body>
    <h1>Επεξεργασία Προφίλ</h1>

    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form method="post">
        <label>Όνομα: <input type="text" name="first_name" id="first_name" required></label><br>
        <label>Επώνυμο: <input type="text" name="last_name" id="last_name" required></label><br>
        <label>Email: <input type="email" name="email" id="email" required></label><br>
        <label>Στοιχεία Επικοινωνίας:<br>
            <textarea name="contact_info" id="contact_info"></textarea>
        </label><br>
        <button type="submit">Αποθήκευση</button>
    </form>

    <p><a href="dashboard.php">← Επιστροφή</a></p>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('../api/get_profile.php')
                .then(response => response.json())
                .then(user => {
                    if (user.error) {
                        console.error('Failed to load profile:', user.error);
                        return;
                    }
                    document.getElementById('first_name').value = user.first_name || '';
                    document.getElementById('last_name').value = user.last_name || '';
                    document.getElementById('email').value = user.email || '';
                    document.getElementById('contact_info').value = user.contact_info || '';
                })
                .catch(error => console.error('Error fetching profile:', error));
        });
    </script>
</body>
</html>