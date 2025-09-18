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


</html>
