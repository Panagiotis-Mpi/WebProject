<?php
session_start();
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="el">
<head><meta charset="UTF-8"><title>Πίνακας Φοιτητή</title></head>
<body>
    <h2>Πίνακας Φοιτητή</h2>
    <ul>
        <li><a href="view_topic.php">📘 Προβολή Θέματος</a></li>
        <li><a href="edit_profile.php">👤 Επεξεργασία Προφίλ</a></li>
        <li><a href="manage_thesis.php">📂 Διαχείριση Διπλωματικής</a></li>
    </ul>
    <a href="../logout.php">Αποσύνδεση</a>
</body></html>
