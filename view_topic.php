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
<head><meta charset="UTF-8"><title>Προβολή Θέματος</title></head>
<body>
    <h2>Το Θέμα Μου</h2>
    <p>Εδώ θα εμφανίζονται οι λεπτομέρειες του θέματος διπλωματικής εργασίας.</p>
</body></html>
