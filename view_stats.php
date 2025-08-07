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
<head><meta charset="UTF-8"><title>Στατιστικά</title></head>
<body>
    <h2>Προβολή Στατιστικών</h2>
    <p>Εδώ θα εμφανίζονται τα στατιστικά σε γραφήματα (π.χ. μέσος χρόνος, μέσος βαθμός).</p>
</body></html>
