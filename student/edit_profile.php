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
<head><meta charset="UTF-8"><title>Επεξεργασία Προφίλ</title></head>
<body>
    <h2>Επεξεργασία Στοιχείων</h2>
    <form method="POST" action="edit_profile_action.php">
        <label>Διεύθυνση:</label><br>
        <input type="text" name="address"><br>
        <label>Email Επικοινωνίας:</label><br>
        <input type="email" name="email"><br>
        <label>Κινητό:</label><br>
        <input type="text" name="mobile"><br>
        <label>Σταθερό:</label><br>
        <input type="text" name="phone"><br>
        <button type="submit">Αποθήκευση</button>
    </form>
</body></html>
