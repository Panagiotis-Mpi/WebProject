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
<head><meta charset="UTF-8"><title>Δημιουργία Θέματος</title></head>
<body>
    <h2>Νέο Θέμα</h2>
    <form method="POST" action="create_topic_action.php" enctype="multipart/form-data">
        <label>Τίτλος:</label><br>
        <input type="text" name="title" required><br>
        <label>Περιγραφή:</label><br>
        <textarea name="summary" rows="4" cols="50"></textarea><br>
        <label>PDF αρχείο:</label><br>
        <input type="file" name="pdf"><br>
        <button type="submit">Υποβολή</button>
    </form>
</body></html>
