<?php
session_start();
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="el">
<head><meta charset="UTF-8"><title>Ανάθεση Θέματος</title></head>
<body>
    <h2>Ανάθεση Θέματος</h2>
    <form method="POST" action="assign_topic_action.php">
        <label>Θέμα (ID):</label><br>
        <input type="number" name="topic_id" required><br>
        <label>ΑΜ Φοιτητή:</label><br>
        <input type="text" name="student_am" required><br>
        <button type="submit">Ανάθεση</button>
    </form>
</body></html>
